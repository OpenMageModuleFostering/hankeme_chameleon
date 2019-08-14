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
class Hankeme_Chameleon_Block_Adminhtml_Design_Edit_Files extends Mage_Adminhtml_Block_Template
{
    /**
     * Files collection object
     *
     * @var Varien_Data_Collection_Filesystem
     */
    protected $_filesCollection;

    /**
     * Prepared Files collection for current directory
     *
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFiles()
    {
        if (! $this->_filesCollection) {
            $this->_filesCollection = Mage::getSingleton('hankeme_chameleon/design_storage')->getFilesCollection(Mage::helper('hankeme_chameleon/design')->getCurrentPath(), $this->_getMediaType());

        }

        return $this->_filesCollection;
    }

    /**
     * Files collection count getter
     *
     * @return int
     */
    public function getFilesCount()
    {
        return $this->getFiles()->count();
    }

    /**
     * File idetifier getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileId(Varien_Object $file)
    {
        return $file->getId();
    }

    /**
     * File type getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileType(Varien_Object $file)
    {
	$bname = $file->getBasename();
	$nparts = explode('.', $bname);
        return end($nparts);
    }

    /**
     * File thumb URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileThumbUrl(Varien_Object $file)
    {
        return $file->getThumbUrl();
    }

    /**
     * File name URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileName(Varien_Object $file)
    {
        return $file->getName();
    }

    /**
     * Image file width getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileWidth(Varien_Object $file)
    {
        return $file->getWidth();
    }

    /**
     * Image file height getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileHeight(Varien_Object $file)
    {
        return $file->getHeight();
    }

    /**
     * File short name getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileShortName(Varien_Object $file)
    {
        return $file->getShortName();
    }

    public function getImagesWidth()
    {
        return Mage::getSingleton('cms/wysiwyg_images_storage')->getConfigData('resize_width');
    }

    public function getImagesHeight()
    {
        return Mage::getSingleton('cms/wysiwyg_images_storage')->getConfigData('resize_height');
    }

    /**
     * Return current media type based on request or data
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
