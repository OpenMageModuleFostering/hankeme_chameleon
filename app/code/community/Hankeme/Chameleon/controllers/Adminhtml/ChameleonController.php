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
class Hankeme_Chameleon_Adminhtml_ChameleonController extends Mage_Adminhtml_Controller_Action
{
 
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
 
}
