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

class Hankeme_Chameleon_Adminhtml_Chameleon_SkinController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {

        $this->getStorage();
        return $this;
    }

    public function indexAction()
    {

        $storeId = (int) $this->getRequest()->getParam('store');

        $this->loadLayout();

        $block = $this->getLayout()->getBlock('skin_edit');
        if ($block) {
            $block->setStoreId($storeId);
        }
        $blocktree = $this->getLayout()->getBlock('skin_tree');
        if ($blocktree) {
            $blocktree->setStoreId($storeId);
        }

        $this->renderLayout();
    }

    public function treeJsonAction()
    {
        try {

            $storeId = (int) $this->getRequest()->getParam('store');
	    if(!$storeId) $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));

            $this->_initAction();
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('hankeme_chameleon/adminhtml_skin_tree')->setStoreId($storeId)
                    ->getTreeJson()
            );
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
        }
    }

    public function contentsAction()
    {
        try {
            $this->_initAction()->_saveSessionCurrentPath();
            $this->loadLayout('empty');
            $this->renderLayout();
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function getStorage()
    {
        if (!Mage::registry('skin_storage')) {
            $storage = Mage::getModel('hankeme_chameleon/skin_storage');
            Mage::register('skin_storage', $storage);
        }
        return Mage::registry('skin_storage');
    }

    protected function _saveSessionCurrentPath()
    {
        $this->getStorage()
            ->getSession()
            ->setCurrentPath(Mage::helper('hankeme_chameleon')->getCurrentPath());
        return $this;
    }

    /**
     * Fire when select files
     */
    public function onInsertAction()
    {
        $helper = Mage::helper('hankeme_chameleon');
        $storeId = $this->getRequest()->getParam('store');

        $filename = $this->getRequest()->getParam('filename');
        $filename = $helper->idDecode($filename);
        $asIs = $this->getRequest()->getParam('as_is');

        Mage::helper('catalog')->setStoreId($storeId);
        $helper->setStoreId($storeId);

        $data = $helper->getFileContents($filename, $asIs);
        $this->getResponse()->setBody($data);
    }

    public function onBackupAction()
    {
        $helper = Mage::helper('hankeme_chameleon');
        $storeId = $this->getRequest()->getParam('store');

        $filename = $this->getRequest()->getParam('filename');
        $filename = $helper->idDecode($filename);

        $helper->setStoreId($storeId);
        $data = $helper->createBackup($filename);
        $this->getResponse()->setBody($data);
    }

    public function onSaveAction()
    {
        $helper = Mage::helper('hankeme_chameleon');
        $storeId = $this->getRequest()->getParam('store');

        $filename = $this->getRequest()->getParam('filename');
        $filename = $helper->idDecode($filename);

        $content = $this->getRequest()->getParam('content');

        $helper->setStoreId($storeId);
        $data = $helper->saveFileContent($filename, $content);
        $this->getResponse()->setBody($data);
    }

   public function newFileAction()
   {
        $helper = Mage::helper('hankeme_chameleon');
        $filename = $this->getRequest()->getParam('name');
        $data = $helper->createFile($filename);
        $this->getResponse()->setBody($data);
   }

   public function deleteFilesAction()
   {
        $helper = Mage::helper('hankeme_chameleon');
        $storeId = $this->getRequest()->getParam('store');
        $filename = $this->getRequest()->getParam('files');
	$filename = $helper->idDecode($filename);
        $helper->setStoreId($storeId);
        $data = $helper->deleteFile($filename);
        $this->getResponse()->setBody($data);
   }

    public function newFolderAction()
    {
        try {
            $this->_initAction();
            $name = $this->getRequest()->getPost('name');
            $path = $this->getStorage()->getSession()->getCurrentPath();
       	    $helper = Mage::helper('hankeme_chameleon');
            $result = $helper->createDirectory($name, $path);
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deleteFolderAction()
    {
        try {
            $path = $this->getStorage()->getSession()->getCurrentPath();
       	    $helper = Mage::helper('hankeme_chameleon');
            $result = $helper->deleteDirectory($path);
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
 
}
