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

class Cybage_Marketplace_Adminhtml_CommissionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action breadcrumbs and active menu
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('marketplace/commission')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Sellers Payment'), Mage::helper('adminhtml')->__('Manage Sellers Payment'));
        return $this;
    }

    /**
     * index action 
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

     /**
     *  Commission grid action for AJAX request
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('marketplace/adminhtml_commission_grid')->toHtml()
        );
    }

    /**
     * Commission pay action
     */
    public function payAction()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        if ($customer->getId()) {
            Mage::register('current_customer', $customer);
            $this->loadLayout();

            $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_commission_edit'))
                 ->_addLeft($this->getLayout()->createBlock('marketplace/adminhtml_commission_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('Seller does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Commission save action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            try {
                if ($data['amount'] > 0) {
                    $actualdue = $data['amountrecived'] - $data['totalpayamount'];
                    
                    if ($data['amount'] <= $actualdue) {
                        $commission = Mage::getModel('marketplace/commission');
                        $commission->setSellerId($data['seller_id']);
                        $commission->setAmount($data['amount']);

                        $commission->save();

                        Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('adminhtml')->__('Payment details added successfully.')
                        );
                        $this->sendTransactionalEmail(Mage::getModel('customer/customer')->load($data['seller_id']),$commission);
                        $this->_redirect('*/*/');
                        return;
                    }  else {
                        Mage::getSingleton('adminhtml/session')->addError('Pay amount not exceed Amount recived.');
                        $this->_redirect('*/*/pay', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError('Please enter a number greater than 0 in Pay.');
                    $this->_redirect('*/*/pay', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/pay', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function sendTransactionalEmail($customer,$commission)
    {
        // Transactional Email Template's ID
        $templateId = Mage::getStoreConfig('marketplace/seller/email_payment_template');
        // Set sender information
        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
        $sender = array('name' => $senderName,
        'email' => $senderEmail);
        // Set recepient information
        $recepientEmail = $customer->getEmail();
        $recepientName =  $customer->getName();
        // Get Store ID
        $store = Mage::app()->getStore()->getId();
        // Set variables that can be used in email template
        $vars = array('customer'=>$customer,
                       'commission'=>$commission
                      );
        $translate = Mage::getSingleton('core/translate');
        // Send Transactional Email
        Mage::getModel('core/email_template')
        ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
        $translate->setTranslateInline(true);
    }

    public function payhistoryAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
