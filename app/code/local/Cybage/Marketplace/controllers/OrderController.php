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

class Cybage_Marketplace_OrderController extends Mage_Core_Controller_Front_Action
{
    /**
     * Add order comment action
     */
    public function addCommentAction() {
        $data = $this->getRequest()->getPost();
        if (!$data) {
            $this->_redirect('marketplace/account/vieworder',$arguement=array('order_id' => $orderId));
        }

        try {
            $error = array();
            $orderId = $buyerName = $buyerEmail = $sellerEmail = $sellerName = '';
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);

            if (empty($data['status']) ) {
                $error['status'] = $this->__('Please select a status');
            }

            if(empty($error)) {
                if($order->hasInvoices() || $data['status'] == 'cancelled') {
                    $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                    $order->addStatusHistoryComment($data['sellerform_comment'], $data['status'])->setIsCustomerNotified($notify);
                    $comment = isset($data['sellerform_comment'])? trim(strip_tags($data['sellerform_comment'])) : '';
                    $trackingNo = isset($data['trackin_no']) ? $data['trackin_no']:'';
                
                    $order->save();            

                    if($notify) {
                        /*Get Buyer information*/
                        $buyerEmail = $order->getCustomerEmail();
                        $buyerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();

                        /*Get Seller information*/
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                        $sellerEmail = $customer->getEmail();
                        $sellerName =  $customer->getName();
                        $order->sendOrderStatusEmail(($data['status']), $buyerEmail, $buyerName, $comment, $orderId, $sellerEmail,$sellerName);
                    }

                   
                        Mage::getSingleton('core/session')->addSuccess('Order status is updated.');
                        $this->_redirect('marketplace/account/vieworder',$arguement=array('order_id' => $orderId));
                   
                } else {
                   Mage::getModel('core/session')->addError("Invoice is not generated for this order,please contact administrator.");
                   $this->_redirect('marketplace/account/vieworder',$arguement=array('order_id' => $orderId));
                }
            } else {
                Mage::getSingleton('core/session')->addSuccess('Comment is not added.');
                Mage::throwException(implode('<br/>', $error));
            }
        } catch (Exception $e) {
            Mage::getModel('core/session')->addError($e->getMessage());
            $this->_redirect('marketplace/account/vieworder',$arguement=array('order_id' => $orderId));
        }
    }

    /**
     * Check order view availability
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }

    /**
     * Init layout, messages and set active block for customer
     *
     * @return null
     */
    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
        }
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }

    /**
     * Order view page
     */
    public function viewAction()
    {
        $this->_viewAction();
    }

    /**
     * Print Order Action
     */
    public function printAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * Customer order history
     */
    public function historyAction()
    {
        $this->_validateCustomerLogin();
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Orders'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /*To print individual order in order view page.*/
    public function printOrderAction(){
       $this->loadLayout();
       $this->renderLayout();
    }

    /* Generate CSV file for Marketplace order grid.*/
    public function expoAction(){
        if (Mage::getModel('customer/session')->isLoggedIn()) {
            $fileName = 'order.csv';
            $content = Mage::helper('marketplace')->generateMarketCsv();
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_redirect('customer/account/login');
            return;
        }
    }

    /* Generate XML file for Marketplace order grid.*/
    public function expoXmlAction(){
       if (Mage::getModel('customer/session')->isLoggedIn()) {
            $orders = Mage::helper('marketplace')->getMarketOrders();
            $filename = 'order.xml';

            $data[0] = array(
                $this->__("Order#"), $this->__("Bill to Name"),
                $this->__("Status"), $this->__("Total Sales"),
                $this->__("Amount Received"), $this->__("Amount Remain"),
            );
            foreach ($orders as $order) {
                $data[] = array(
                    $order['increment_id'], $order['billname'],$order['status'],
                    round($order['Total'], 2), round($order['Amount Received'], 2), round($order['Amount Remain'], 2)
                );
            }
            // Unparsing in Excel Format
            $xmlObj = new Varien_Convert_Parser_Xml_Excel();
            $xmlObj->setVar('single_sheet', $filename);
            $xmlObj->setData($data);
            $xmlObj->unparse();
            $content = $xmlObj->getData();
            // Force Download
            $this->_prepareDownloadResponse($filename, $content);
        } else {
            $this->_redirect('customer/account/login');
            return;
        }
    }

    public function shipAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _initShipment()
    {
        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        } elseif ($orderId) {
            $order      = Mage::getModel('sales/order')->load($orderId);

            /**
             * Check order existing
            */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            $savedQtys = $this->_getItemQtys();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        Mage::throwException($this->__('Tracking number cannot be empty.'));
                    }
                    $track = Mage::getModel('sales/order_shipment_track')
                    ->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }

        Mage::register('current_shipment', $shipment);
        return $shipment;
    }

    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
        ->addObject($shipment)
        ->addObject($shipment->getOrder())
        ->save();

        return $this;
    }

    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');

        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
           
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $shipment->register();
            $comment = '';

            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);
            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            $labelCreatedMessage    = $this->__('The shipping label has been created.');

            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/order/history/');
        }
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
        }elseif(!Mage::helper('marketplace')->isMarketplaceActiveSellar()){
            $this->_redirect('customer/account/');
        }
    }
}
