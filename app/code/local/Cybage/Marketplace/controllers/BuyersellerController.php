<?php
/**
 * Cybage Marketplace Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_Magento@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Marketplace Plugin
 * @package    Cybage_Marketplace
 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_Magento@cybage.com>
 */

class Cybage_Marketplace_BuyersellerController extends Mage_Core_Controller_Front_Action
{
    public function commentAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction() {
        try {
            Mage::getModel('marketplace/buyerseller')->saveComments();
            
            Mage::getSingleton('core/session')->addSuccess($this->__('Question was successfully submitted!!'));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($this->__('Question was not submitted, Please Try After Some Time.'.$e));
        }
        $block = $this->getLayout()->createBlock('core/messages', 'global_messages');
        $html = $block->toHtml();
        $this->getResponse()->setBody($html);
        
    }

    public function replyQuestionAction() {
        
        try {
            Mage::getModel('marketplace/buyerseller')->replyComments();	
            Mage::getSingleton('core/session')->addSuccess($this->__('Question was successfully submitted!!'));
            return $this->_redirect('marketplace/order/history/');
        
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($this->__('Question was not submitted, Please Try After Some Time. '.$e));
        }
        $block = $this->getLayout()->createBlock('core/messages', 'global_messages');
        $html = $block->toHtml();
        $this->getResponse()->setBody($html);

    }

    public function replyAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function notificationAction() {
        
        $this->loadLayout();
        $this->renderLayout();
    }
}
