<?php

/**
* HANKEME
*
* NOTICE OF LICENSE
*
* This source file is exclusively published under the Open Software License (OSL 3.0)
* See http://opensource.org/licenses/osl-3.0.php for Details
* In case of questions regarding the use of this source file please
refer to the contact below.
*
* @project: [HANKEME Chameleon]
* @contact: info@hankeme.de
* @collaborators: [strohmeier]
*/

class Hankeme_Chameleon_Model_Skin_Storage extends Varien_Object
{

    /**
     * Config object
     *
     * @var Mage_Core_Model_Config_Element
     */
    protected $_config;

    /**
     * Config object as array
     *
     * @var array
     */
    protected $_configAsArray;

    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';
    const THUMBS_DIRECTORY_NAME = '.thumbs';
    const THUMB_PLACEHOLDER_PATH_SUFFIX = 'images/placeholder/thumbnail.jpg';

    public function getDirsCollection($path)
    {
        if (Mage::helper('core/file_storage_database')->checkDbUsage()) {
            $subDirectories = Mage::getModel('core/file_storage_directory_database')->getSubdirectories($path);
            foreach ($subDirectories as $directory) {
                $fullPath = rtrim($path, DS) . DS . $directory['name'];
                  if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
            }
        }

        $conditions = array('reg_exp' => array(), 'plain' => array());

        foreach ($this->getConfig()->dirs->exclude->children() as $dir) {
            $conditions[$dir->getAttribute('regexp') ? 'reg_exp' : 'plain'][(string) $dir] = true;
        }
        // "include" section takes precedence and can revoke directory exclusion
        foreach ($this->getConfig()->dirs->include->children() as $dir) {
            unset($conditions['regexp'][(string) $dir], $conditions['plain'][(string) $dir]);
        }

        $regExp = $conditions['reg_exp'] ? ('~' . implode('|', array_keys($conditions['reg_exp'])) . '~i') : null;

        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setCollectFiles(false)
            ->setCollectRecursively(false);
        $storageRootLength = strlen(Mage::helper('hankeme_chameleon')->getStorageRoot());

        foreach ($collection as $key => $value) {
            $rootChildParts = explode(DIRECTORY_SEPARATOR, substr($value->getFilename(), $storageRootLength));

            if (array_key_exists($rootChildParts[0], $conditions['plain'])
                || ($regExp && preg_match($regExp, $value->getFilename()))) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }


    /**
     * Return files
     *
     * @param string $path Parent directory path
     * @param string $type Type of storage, e.g. image, media etc.
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFilesCollection($path, $type = null)
    {
        if (Mage::helper('core/file_storage_database')->checkDbUsage()) {
            $files = Mage::getModel('core/file_storage_database')->getDirectoryFiles($path);

            $fileStorageModel = Mage::getModel('core/file_storage_file');
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }

        $collection = $this->getCollection($path)
            ->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(false)
            ->setOrder('mtime', Varien_Data_Collection::SORT_ORDER_ASC);

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed). ')$/i');
        }

        $helper = $this->getHelper();

        // prepare items
        foreach ($collection as $item) {
            $item->setId($helper->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($helper->getShortFilename($item->getBasename()));
            $item->setUrl($helper->getCurrentUrl() . $item->getBasename());

            if ($this->isImage($item->getBasename())) {
                $thumbUrl = $this->getThumbnailUrl($item->getFilename(), true);
                // generate thumbnail "on the fly" if it does not exists
                if(! $thumbUrl) {
                    $thumbUrl = Mage::getSingleton('adminhtml/url')->getUrl('*/*/thumbnail', array('file' => $item->getId()));
                }

                $size = @getimagesize($item->getFilename());

                if (is_array($size)) {
                    $item->setWidth($size[0]);
                    $item->setHeight($size[1]);
                }
            } else {
                //$thumbUrl = Mage::getDesign()->getSkinBaseUrl() . self::THUMB_PLACEHOLDER_PATH_SUFFIX;
		$thumbUrl = false;
            }

            $item->setThumbUrl($thumbUrl);
        }

        return $collection;
    }


    public function getCollection($path = null)
    {
        $collection = Mage::getModel('hankeme_chameleon/skin_storage_collection');

        if ($path !== null) {
            $collection->addTargetDir($path);
        }
        return $collection;
    }

    public function getConfig()
    {
        if (! $this->_config) {
            $this->_config = Mage::getConfig()->getNode('css/browser', 'adminhtml');
        }

        return $this->_config;
    }

     /**
     * Prepare allowed_extensions config settings
     *
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    public function getAllowedExtensions($type = null)
    {
        $extensions = $this->getConfigData('extensions');

        if (is_string($type) && array_key_exists("{$type}_allowed", $extensions)) {
            $allowed = $extensions["{$type}_allowed"];
        } else {
            $allowed = $extensions['allowed'];
        }

        return array_keys(array_filter($allowed));
    }

    public function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    public function getHelper()
    {
        return Mage::helper('hankeme_chameleon');
    }

    /**
     * Simple way to check whether file is image or not based on extension
     *
     * @param string $filename
     * @return bool
     */
    public function isImage($filename)
    {
        if (!$this->hasData('_image_extensions')) {
            $this->setData('_image_extensions', $this->getAllowedExtensions('image'));
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $this->_getData('_image_extensions'));
    }

    /**
     * Config object as array getter
     *
     * @return array
     */
    public function getConfigAsArray()
    {
        if (! $this->_configAsArray) {
            $this->_configAsArray = $this->getConfig()->asCanonicalArray();
        }

        return $this->_configAsArray;
    }

    /**
     * Wysiwyg Config reader
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfigData($key, $default=false)
    {
        $configArray = $this->getConfigAsArray();
        $key = (string) $key;

        return array_key_exists($key, $configArray) ? $configArray[$key] : $default;
    }
}
