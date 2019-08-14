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

class Hankeme_Chameleon_Helper_Design extends Hankeme_Chameleon_Helper_Data
{

    public function getStorageRoot($storeid){

			if($storeid != 0) return Mage::getConfig()->getOptions()->getDesignDir() . DS . 'frontend' . DS . Mage::getStoreConfig('design/package/name', $storeid) . DS .Mage::getStoreConfig('design/theme/skin', $storeid) . DS;
			else return Mage::getConfig()->getOptions()->getDesignDir() . DS . 'frontend' . DS;
	    }
}
