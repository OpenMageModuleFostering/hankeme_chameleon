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
class Hankeme_Chameleon_Model_Skin_Storage_Collection extends Varien_Data_Collection_Filesystem
{
    protected function _generateRow($filename)
    {
        $filename = preg_replace('~[/\\\]+~', DIRECTORY_SEPARATOR, $filename);
        
        return array(
            'filename' => $filename,
            'basename' => basename($filename),
            'mtime'    => filemtime($filename)
        );
    }
}
