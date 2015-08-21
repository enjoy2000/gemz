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

class Cybage_Marketplace_ProductquestionController extends Mage_Core_Controller_Front_Action
{
    //action to save question after submitting the form
    public function submitAction() {
        $this->_validateCustomerLogin();

        try {
            Mage::getModel('marketplace/question')->saveQuestions();
            Mage::getSingleton('core/session')->addSuccess($this->__('Question was successfully submitted!!'));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($this->__('Question was not submitted, Please Try After Some Time.'));
        }
        $block = $this->getLayout()->createBlock('core/messages', 'global_messages');
        $html = $block->toHtml();
        $this->getResponse()->setBody($html);
    }

    //action to disply link on product page
    public function linkAction() {
        $productId = $this->getRequest()->getParam('id');
        $_product = Mage::getModel('catalog/product')->load($productId);
        $status = $_product->getStatus();

        if ($status == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
            Mage::getSingleton('core/session')->addError($this->__('This product is not available now.'));
            $this->_redirectError(Mage::getUrl());
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     *    validate Customer Login and redirect previous page 
     * */
    protected function _validateCustomerLogin() {
        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()) {
            $session->setAfterAuthUrl(Mage::helper('core/url')->getCurrentUrl());
            $session->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
            $this->_redirect('customer/account/login/');
            return $this;
        }
    }

    //Marketplace My Products Question
    public function indexAction() {
        $this->_validateCustomerLogin();
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Products Questions'));
        $this->renderLayout();
    }

    //action to give reply to a question.
    public function replyAction() {
        $this->_validateCustomerLogin();
        $this->loadLayout();
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('marketplace/productquestion');
        }
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Products Questions Reply'));
        $this->renderLayout();
    }

    //action to save reply after submitting the form.
    public function repliedAction() {
        $this->_validateCustomerLogin();
        Mage::getModel('marketplace/reply')->saveReplies();
        Mage::getSingleton('core/session')->addSuccess($this->__('Reply was successfully submitted!!'));
        $this->_redirect('*/*');
    }
}
