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

class Hankeme_Chameleon_Block_Adminhtml_Skin_Tree extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
	$this->setUseAjax(true);
    }

     /**
     * Json tree builder
     *
     * @return string
     */
    public function getTreeJson()
    {

	//if(!$this->getStoreId()) return Zend_Json::encode($jsonArray); //force setting of store view level first

        $helper = Mage::helper('hankeme_chameleon');
        $storageRoot = $helper->getStorageRoot();
        $collection = Mage::registry('skin_storage')->getDirsCollection($helper->getCurrentPath($this->getStoreId()));
        $jsonArray = array();
 
        foreach ($collection as $item) {
	   if(is_dir($item->getFilename())) {
            $jsonArray[] = array(
                'text'  => $helper->getShortFilename($item->getBasename(), 20),
                'id'    => $helper->convertPathToId($item->getFilename()),
                'cls'   => 'folder'
            );
	   }
	   else {
            $jsonArray[] = array(
                'text'  => $helper->getShortFilename($item->getBasename(), 20),
                'id'    => $helper->convertPathToId($item->getFilename()),
                'cls'   => 'file'
            );
           }
        }
        return Zend_Json::encode($jsonArray);
    }

     /**
     * Json source URL
     *
     * @return string
     */
    public function getTreeLoaderUrl()
    {
        return $this->getUrl('*/*/treeJson', array('store' => $this->getStoreId()));
    }

    /**
     * Root node name of tree
     *
     * @return string
     */
    public function getRootNodeName()
    {

	if($this->getStoreId()) return Mage::getStoreConfig('design/theme/skin', $this->getStoreId());
        return $this->__('skin');
    }

    /**
     * Return tree node full path based on current path
     *
     * @return string
     */
    public function getTreeCurrentPath()
    {
        $treePath = '/root';

            $helper = Mage::helper('hankeme_chameleon');
            $path = str_replace($helper->getStorageRoot(), '', $path);
            $relative = '';
            foreach (explode(DS, $path) as $dirName) {
                if ($dirName) {
                    $relative .= DS . $dirName;
                    $treePath .= '/' . $helper->idEncode($relative);
                }
            }

        return $treePath;
    }

    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

}
