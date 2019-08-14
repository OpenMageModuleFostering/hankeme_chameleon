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

class Hankeme_Chameleon_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Currenty selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId = null;


    /**
     * Set a specified store ID value
     *
     * @param <type> $store
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }


    public function getStorageRoot($storeid){


			if($storeid != 0) return Mage::getConfig()->getOptions()->getSkinDir() . DS . 'frontend' . DS . Mage::getStoreConfig('design/package/name', $storeid) . DS .Mage::getStoreConfig('design/theme/skin', $storeid) . DS;
			else return Mage::getConfig()->getOptions()->getSkinDir() . DS . 'frontend' . DS;
	    }

	public function getCurrentPath($storeid){
		if (!$this->_currentPath) {
		    $currentPath = $this->getStorageRoot($storeid);
		    $path = $this->_getRequest()->getParam($this->getTreeNodeName());
		    if ($path) {
		        $path = $this->convertIdToPath($path);
		        if (is_dir($path)) {
		            $currentPath = $path;
		        }
		    }
		    $io = new Varien_Io_File();
		    if (!$io->isWriteable($currentPath) && !$io->mkdir($currentPath)) {
		        $message = Mage::helper('cms')->__('The directory %s is not writable by server.',$currentPath);
		        Mage::throwException($message);
		    }
		    $this->_currentPath = $currentPath;
		}
		return $this->_currentPath;
	}

    /**
     * Return URL based on current selected directory or root directory for startup
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        if (!$this->_currentUrl) {
            $path = str_replace(Mage::getConfig()->getOptions()->getSkinDir(), '', $this->getCurrentPath());
            $path = trim($path, DS);
            $this->_currentUrl = Mage::app()->getStore($this->_storeId)->getBaseUrl('skin') .
                                 $this->convertPathToUrl($path) . '/';
        }
        return $this->_currentUrl;
    }

    public function convertPathToUrl($path)
    {
        return str_replace(DS, '/', $path);
    }

    public function getTreeNodeName()
    {
        return 'node';
    }

    public function convertPathToId($path)
    {
        $path = str_replace($this->getStorageRoot(), '', $path);
        return $this->idEncode($path);
    }

    public function convertIdToPath($id)
    {
        $path = $this->idDecode($id);
        if (!strstr($path, $this->getStorageRoot())) {
            $path = $this->getStorageRoot() . $path;
        }
        return $path;
    }

    public function idEncode($string)
    {
        return strtr(base64_encode($string), '+/=', ':_-');
    }
    public function idDecode($string)
    {
        $string = strtr($string, ':_-', '+/=');
        return base64_decode($string);
    }

    public function getShortFilename($filename, $maxLength = 20)
    {
        if (strlen($filename) <= $maxLength) {
            return $filename;
        }
        return substr($filename, 0, $maxLength) . '...';
    }

    /**
     * Prepare File Content insertion for textarea (as_is mode)
     *
     * @param string $filename Filename transferred via Ajax
     * @param bool $renderAsContent return plain file contents or link to file
     * @return string
     */
    public function getFileContents($filename, $renderAsContent = false)
    {

	$path = $this->getCurrentPath($this->_storeId);

	if ($renderAsContent) {
		try{
			$file = new Varien_Io_File();
			$file->cd($path);
			$data = $file->read($filename);
			return $data;
		}
		catch(Exception $e) {
			return $e->getMessage();
		}

		return $filePath;

        } else {
            $fileurl = $this->getCurrentUrl() . $filename;
            return $fileurl;
        }

    }

    /**
     * Create File Backup
     *
     * @param string $filename Filename transferred via Ajax
     * @return string
     */

    public function createBackup($filename)
    {
		$path = $this->getCurrentPath($this->_storeId);
		try{
			$dest = 'Backup_'.date('Y-m-d-H-i-s').'_'.$filename;
			$file = new Varien_Io_File();
			$file->cd($path);
			$result = $file->cp($filename, $dest);
			if($result) {
				$data = $this->__('Backup %s was created', $dest);
			}
			else{
				$data = $this->__('Failed to create Backup');
			}
			return $data;
		}
		catch(Exception $e) {
			return $e->getMessage();
		}
    }


    /**
     * Save File Content
     *
     * @param string $filename Filename transferred via Ajax
     * @return string
     */

    public function saveFileContent($filename, $content)
    {
	$path = $this->getCurrentPath($this->_storeId);

		try{
			$file = new Varien_Io_File();
			$file->cd($path);
			$data = $file->write($filename, $content);
			if($data) return $this->__('%s was saved', $filename);
			else return $this->__('Error saving %s', $filename);
		}
		catch(Exception $e) {
			return $e->getMessage();
		}
    }

    public function createFile($filename) {

		$path = $this->getCurrentPath($this->_storeId);

		try{
			$file = new Varien_Io_File();
			if(!$file->isWriteable($path)) {
				return $this->__('Permission error, cant create %s', $filename);
			}
			$file->cd($path);
			if($file->fileExists($filename)) return $this->__('File %s already exist.', $filename);

			$content = (string) ' ';

			$data = $file->write($filename, $content);
			if($data) return $this->__('%s was created', $filename);
			else return $this->__('Error creating %s', $filename);
		}
		catch(Exception $e) {
			return $e->getMessage();
		}
    }

    public function deleteFile($filename) {

		$path = $this->getCurrentPath($this->_storeId);

		try{
			$file = new Varien_Io_File();
			
			$file->cd($path);

			if($file->fileExists($filename)) {
				$result = $file->rm($filename);
				if($result) return $this->__('File %s was deleted.', $filename);
				return $this->__('File %s could not be deleted.', $filename);
			}

			return $this->__('File %s does not exist.', $filename);
		}
		catch(Exception $e) {
			return $e->getMessage();
		}
    }

    public function createDirectory($name, $path) {

		try{
			$file = new Varien_Io_File();
			
			$file->cd($path);
			$result = $file->mkdir($name, 0755);
			if($result) return $this->__('Directory %s was created.', $name);
			return $this->__('Directory %s could not be created.', $name);
		}
		catch(Exception $e) {
			return $e->getMessage();
		}
    }

    public function deleteDirectory($path) {
        // prevent accidental root directory deleting
        $rootCmp = rtrim($this->getStorageRoot(), DS);
        $pathCmp = rtrim($path, DS);

	$base = $rootCmp . DS . 'base';
	$basedefault = $rootCmp . DS . 'base' . DS . 'default';
	$default = $rootCmp . DS . 'default';

        if (($base == $pathCmp) || ($basedefault == $pathCmp) || ($default == $pathCmp) || ($rootCmp == $pathCmp)) {
            return $this->__('Cannot delete core storage directory %s.', $path);
        };

        $file = new Varien_Io_File();

        if (!$file->rmdir($path, true)) return $this->__('Cannot delete directory %s.', $path);

	return $this->__('Successfully deleted directory %s.', $path);
    }
}
