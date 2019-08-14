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

class Hankeme_Chameleon_Block_Adminhtml_Skin_Edit extends Mage_Adminhtml_Block_Widget_Container
{

    private $_target_element_id = 'chameleon_edit_stage';

    /**
     * Array of action buttons
     *
     *
     * @var array
     */
    protected $_actionbuttons = array(
        -1  => array(),
        0   => array(),
        1   => array(),
    );

    public function __construct()
    {
        parent::__construct();
        $this->_headerText = $this->__('Skin Storage');
        $this->_removeButton('back')->_removeButton('edit');
        $this->_addButton('newfolder', array(
            'class'   => 'save',
            'label'   => $this->helper('hankeme_chameleon')->__('Create Folder...'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.newFolder();'
        ));

        $this->_addButton('delete_folder', array(
            'class'   => 'delete no-display',
            'label'   => $this->helper('hankeme_chameleon')->__('Delete Folder'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.deleteFolder();',
            'id'      => 'button_delete_folder'
        ));

        $this->_addButton('new_file', array(
            'class'   => 'save',
            'label'   => $this->helper('hankeme_chameleon')->__('New File'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.createFile();',
            'id'      => 'button_create_file'
        ));

        $this->_addButton('delete_files', array(
            'class'   => 'delete no-display',
            'label'   => $this->helper('hankeme_chameleon')->__('Delete File'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.deleteFiles();',
            'id'      => 'button_delete_files'
        ));

        $this->_addButton('backup_files', array(
            'class'   => 'save no-display',
            'label'   => $this->helper('hankeme_chameleon')->__('Backup File'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.backup();',
            'id'      => 'button_backup_files'
        ));

        $this->_addButton('insert_files', array(
            'class'   => 'save no-display',
            'label'   => $this->helper('hankeme_chameleon')->__('Edit File'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.insert();',
            'id'      => 'button_insert_files'
        ));

        $this->_addActionButton('save_changes', array(
            'class'   => 'save no-display f-right',
            'label'   => $this->helper('hankeme_chameleon')->__('Save Changes'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.save();',
            'id'      => 'button_save_changes'
        ));
    }

    public function getContentsUrl()
    {
        return $this->getUrl('*/*/contents', array('type' => $this->getRequest()->getParam('type')));
    }

    /**
     * Javascript setup object for filebrowser instance
     *
     * @return string
     */
    public function getFilebrowserSetupObject()
    {
        $setupObject = new Varien_Object();

        $setupObject->setData(array(
            'newFolderPrompt'                 => $this->helper('hankeme_chameleon')->__('New Folder Name:'),
            'newFilePrompt'                 => $this->helper('hankeme_chameleon')->__('New File Name:'),
            'deleteFolderConfirmationMessage' => $this->helper('hankeme_chameleon')->__('Are you sure you want to delete the current folder and all of its contents?'),
            'deleteFileConfirmationMessage'   => $this->helper('hankeme_chameleon')->__('Are you sure you want to delete the selected file?'),
            'targetElementId' => $this->getTargetElementId(),
            'contentsUrl'     => $this->getContentsUrl(),
            'onInsertUrl'     => $this->getOnInsertUrl(),
            'onSaveUrl'     => $this->getOnSaveUrl(),
            'newFolderUrl'    => $this->getNewfolderUrl(),
            'newFileUrl'    => $this->getNewfileUrl(),
            'deleteFolderUrl' => $this->getDeletefolderUrl(),
            'deleteFilesUrl'  => $this->getDeleteFilesUrl(),
            'headerText'      => $this->getHeaderText(),
	    'onBackupUrl'     => $this->getOnBackupUrl(),
        ));

        return Mage::helper('core')->jsonEncode($setupObject);
    }

    /**
     * New directory action target URL
     *
     * @return string
     */
    public function getNewfolderUrl()
    {
        return $this->getUrl('*/*/newFolder');
    }

    /**
     * New file action target URL
     *
     * @return string
     */
    public function getNewfileUrl()
    {
        return $this->getUrl('*/*/newFile');
    }

    /**
     * Delete directory action target URL
     *
     * @return string
     */
    protected function getDeletefolderUrl()
    {
        return $this->getUrl('*/*/deleteFolder');
    }

    /**
     * Description goes here...
     *
     * @param none
     * @return void
     */
    public function getDeleteFilesUrl()
    {
        return $this->getUrl('*/*/deleteFiles');
    }

    /**
     * New directory action target URL
     *
     * @return string
     */
    public function getOnInsertUrl()
    {
        return $this->getUrl('*/*/onInsert');
    }



    /**
     * Save file contents action target URL
     *
     * @return string
     */
    public function getOnSaveUrl()
    {
        return $this->getUrl('*/*/onSave');
    }

    /**
     * Create Backup action target URL
     *
     * @return string
     */
    public function getOnBackupUrl()
    {
        return $this->getUrl('*/*/onBackup');
    }



    /**
     * Target element ID getter
     *
     * @return string
     */
    public function getTargetElementId()
    {
	return $this->_target_element_id;
        //return $this->getRequest()->getParam('target_element_id');
    }



     /**
     * Add action button
     *
     * @param string $id
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $placement area, that button should be displayed in ('header', 'footer', null)
     * @return Mage_Adminhtml_Block_Widget_Container
     */
    protected function _addActionButton($id, $data, $level = 0, $sortOrder = 0, $area = 'header')
    {
        if (!isset($this->_actionbuttons[$level])) {
            $this->_actionbuttons[$level] = array();
        }
        $this->_actionbuttons[$level][$id] = $data;
        $this->_actionbuttons[$level][$id]['area'] = $area;
        if ($sortOrder) {
            $this->_actionbuttons[$level][$id]['sort_order'] = $sortOrder;
        } else {
            $this->_actionbuttons[$level][$id]['sort_order'] = count($this->_actionbuttons[$level]) * 10;
        }
        return $this;
    }

    /**
     * Public wrapper for protected _addActionButton method
     *
     * @param string $id
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $placement area, that button should be displayed in ('header', 'footer', null)
     * @return Mage_Adminhtml_Block_Widget_Container
     */
    public function addActionButton($id, $data, $level = 0, $sortOrder = 0, $area = 'header')
    {
        return $this->_addActionButton($id, $data, $level, $sortOrder, $area);
    }





     /**
     * Produce more buttons HTML
     *
     * @param string $area
     * @return string
     */
    public function getActionButtonsHtml($area = null)
    {
        $out = '';
        foreach ($this->_actionbuttons as $level => $buttons) {
            $_buttons = array();
            foreach ($buttons as $id => $data) {
                $_buttons[$data['sort_order']]['id'] = $id;
                $_buttons[$data['sort_order']]['data'] = $data;
            }
            ksort($_buttons);
            foreach ($_buttons as $button) {
                $id = $button['id'];
                $data = $button['data'];
                if ($area && isset($data['area']) && ($area != $data['area'])) {
                    continue;
                }
                $childId = $this->_prepareButtonBlockId($id);
                $child = $this->getChild($childId);

                if (!$child) {
                    $child = $this->_addButtonChildBlock($childId);
                }
                if (isset($data['name'])) {
                    $data['element_name'] = $data['name'];
                }
                $child->setData($data);

                $out .= $this->getChildHtml($childId);
            }
        }
        return $out;
    }

    public function getFiletypeNotice() {
	
		$allowed = Mage::getModel('hankeme_chameleon/skin_storage')->getAllowedExtensions();
		$impl = implode(', ', $allowed);
		return $this->helper('hankeme_chameleon')->__('Only files of the following types are shown: %s', $impl);

    }
}
