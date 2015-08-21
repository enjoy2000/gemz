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

class Cybage_Marketplace_Adminhtml_SellerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action breadcrumbs and active menu
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('marketplace/seller')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Sellers'), Mage::helper('adminhtml')->__('Manage Sellers'));
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
     * seller grid action for AJAX request
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('marketplace/adminhtml_seller_grid')->toHtml()
        );
    }

    /**
     * Seller edit action
     */
    public function editAction()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        if ($customer->getId()) {
            Mage::register('current_customer', $customer);
            $this->loadLayout();

            $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_seller_edit'))
                 ->_addLeft($this->getLayout()->createBlock('marketplace/adminhtml_seller_edit_tabs'));
                
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('Seller does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Seller save action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            try {
                $customer = Mage::getModel('customer/customer')->load($data['seller_id']);

                $customer->setSellerCommission($data['seller_commission']);
                $customer->setSellerProductState($data['seller_product_state']);
                $customer->setSellerProductStatus($data['seller_product_status']);
                $customer->setStatus($data['status']);
                $customer->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Seller details successfully updated.')
                );

                $this->sendTransactionalEmail($customer);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $customer->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function sendTransactionalEmail($customer)
    {
        // Transactional Email Template's ID
        $templateId = Mage::getStoreConfig('marketplace/seller/email_status_template');
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
        $statusCode = $customer->getStatus();        
        $sellerStatus = Mage::getModel('marketplace/customatributestatus')->toOptionArray();
        $status = $sellerStatus[$statusCode];
        // Set variables that can be used in email template
        $vars = array(
                      'customer'=>$customer,
                      'status'=>$status
                      );
        $translate = Mage::getSingleton('core/translate');
        // Send Transactional Email
        Mage::getModel('core/email_template')
        ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
        $translate->setTranslateInline(true);
    }

    /**
     * mass delete action 
     */
    public function massDeleteAction() {
        $sellerIds = $this->getRequest()->getParam('id');

        if (!is_array($sellerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select seller(s)'));
        } else {
            try {
                $deleted = Mage::getStoreConfig('marketplace/status/deleted');
                foreach ($sellerIds as $sellerId) {
                    $seller = Mage::getModel('customer/customer')->load($sellerId);
                    $seller->setStatus($deleted);
                    $seller->save();
                    $this->sendTransactionalEmail($seller);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were successfully deleted.', count($sellerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass approve action
     */
    public function massApproveAction() {
        $sellerIds = $this->getRequest()->getParam('id');

        if (!is_array($sellerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select seller(s)'));
        } else {
            try {
                $approved = Mage::getStoreConfig('marketplace/status/approved');
                foreach ($sellerIds as $sellerId) {
                    $seller = Mage::getModel('customer/customer')->load($sellerId);
                    $seller->setStatus($approved);
                    $seller->save();
                    $this->sendTransactionalEmail($seller);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were successfully approved.', count($sellerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass reject action
     */
    public function massRejectAction() {
        $sellerIds = $this->getRequest()->getParam('id');

        if (!is_array($sellerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select seller(s)'));
        } else {
            try {
                $rejected = Mage::getStoreConfig('marketplace/status/rejected');
                foreach ($sellerIds as $sellerId) {
                    $seller = Mage::getModel('customer/customer')->load($sellerId);
                    $seller->setStatus($rejected);
                    $seller->save();
                    $this->sendTransactionalEmail($seller);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were successfully rejected.', count($sellerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
