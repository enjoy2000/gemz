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

class Cybage_Marketplace_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * XML configuration paths
     */
    const XML_PATH_EMAIL_PROCESSING_TEMPLATE     = 'sales_email/order/processing_template';
    const XML_PATH_EMAIL_COMPLETED_TEMPLATE      = 'sales_email/order/completed_template';
    const XML_PATH_EMAIL_CANCELLED_TEMPLATE      = 'sales_email/order/cancelled_template';
    const XML_PATH_EMAIL_IDENTITY                = 'sales_email/order/identity';

    /**
     * Send email with order update information
     *
     * @param boolean $notifyCustomer
     * @param string $comment
     * @return Mage_Sales_Model_Order
     */
    public function sendOrderStatusEmail($status, $buyerEmail, $buyerName, $comment, $orderId, $sellerEmail,$sellerName){
        $storeId = $this->getStore()->getId();
        $templateId = '';

        if (strcmp($status, 'cancelled') == 0) {
           $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_CANCELLED_TEMPLATE);
        }
        
        if (strcmp($status, 'complete') == 0) {
           $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_COMPLETED_TEMPLATE);
        }
        
        if (strcmp($status, 'processing') == 0) {
           $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_PROCESSING_TEMPLATE);
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($buyerEmail, $buyerName);
        $emailInfo->addBcc($sellerEmail);

        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
            'name'     => $buyerName,
            'comment'  => $comment,
            'order_id' => $orderId,
            'seller_email' => $sellerEmail,
            'seller_name' => $sellerName)
                );

        $mailer->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');
        return $this;
    }

    /**
     * Completes the Shipment, followed by completing the Order life-cycle
     * It is assumed that the Invoice has already been generated
     * and the amount has been captured.
     */
    public function processOrder($status,$orderId,$trackingNo,$comment)
    {
        /**
         * Provide the Shipment Tracking Number,
         */
        $shipmentTrackingNumber = isset($trackingNo) ? $trackingNo : '';
        
        $customerEmailComments = isset($comment) ? $comment : '';
     
        $order = Mage::getModel('sales/order')->load($orderId);
        
        if (!$order->getId()) {
            Mage::throwException("Order does not exist, for the Shipment process to complete");
        }
        
        if (strcmp($status, 'cancelled') == 0) {
            $this->cancel()->save();
            return true;
        }

        try {
          /* Check if invoice is created for order then only shipment will be created.*/
              if($order->hasInvoices()) {
                $totalQtyOrdered = (float)$order->getTotalQtyOrdered();
            
                if (strcmp($status, 'processing') == 0) {
                        $shipment = Mage::getModel('sales/service_order', $order)
                                    ->prepareShipment($this->_getItemQtys($order));
             
                        $shipmentCarrierCode = 'SPECIFIC_CARRIER_CODE';
                        $shipmentCarrierTitle = 'SPECIFIC_CARRIER_TITLE';
             
                        $arrTracking = array(
                            'carrier_code' => isset($shipmentCarrierCode) ? $shipmentCarrierCode : $order->getShippingCarrier()->getCarrierCode(),
                            'title' => isset($shipmentCarrierTitle) ? $shipmentCarrierTitle : $order->getShippingCarrier()->getConfigData('title'),
                            'number' => $shipmentTrackingNumber,
                        );
             
                        $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
                        $shipment->addTrack($track);
             
                        // Register Shipment
                        $shipment->register();
             
                        // Save the Shipment
                        $this->_saveShipment($shipment, $order, $customerEmailComments);
             
                        // Finally, Save the Order
                        $this->_saveOrder($order);
                        
                        return true;

                }
            }else{
                return false;
            }
        } catch (Exception $e) {
            Mage::getModel('core/session')->addError($e->getMessage());
            $this->_redirect('marketplace/account/vieworder',$arguement=array('order_id' => $orderId));
        }
    }

    /**
     * Get the Quantities shipped for the Order, based on an item-level
     * This method can also be modified, to have the Partial Shipment functionality in place
     *
     * @param $order Mage_Sales_Model_Order
     * @return array
     */
    protected function _getItemQtys(Mage_Sales_Model_Order $order)
    {
        $qty = array();

        $customerId = Mage::getSingleton('customer/session')->getId();
        
        foreach ($order->getAllItems() as $_eachItem) {
            $sellerId = $_eachItem->getSellerId();
            
            //check for artial shipment
            if($sellerId == $customerId){
              if ($_eachItem->getParentItemId()) {
                    $qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
                } else {
                    $qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
                }
            }
        }

        return $qty;
    }

    /**
     * Saves the Shipment changes in the Order
     *
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @param $order Mage_Sales_Model_Order
     * @param $customerEmailComments string
     */
    protected function _saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments = '')
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
                           ->addObject($shipment)
                           ->addObject($order)
                           ->save();
     
        $emailSentStatus = $shipment->getData('email_sent');
        
        return $this;
    }

    /** Saves the Order, to complete the full life-cycle of the Order
     * Order status will now show as Complete
     * @param $order Mage_Sales_Model_Order
     */
    protected function _saveOrder(Mage_Sales_Model_Order $order)
    {
        $totalQtyOrdered = (float)$order->getTotalQtyOrdered();
        
         $qtyShipped = '';
        
        foreach ($order->getAllItems() as $_eachItem) {
            $qtyShipped += (float)$_eachItem->getQtyShipped();
        }

       if($qtyShipped < $totalQtyOrdered){
           $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
           $order->setData('status', Mage_Sales_Model_Order::STATE_PROCESSING);
       }else{
          $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
          $order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
       }

        $order->save();
     
        return $this;
    }

    /*Filter ordered items on the basis of seller id*/
    public function getOrderDetails($orderId) {
        $seller_id = Mage::getSingleton('customer/session')->getId();
        $customer = Mage::getModel('customer/customer')->load($seller_id);

        $seller = $customer->getData('company_name');
        
        $ordersCollection = Mage::getResourceModel('sales/order_item_collection')
                           ->addAttributeToFilter('order_id',array('in' => $orderId));

        if($seller){
              $ordersCollection->addAttributeToFilter('seller_id', array('in' => $seller_id));
        }

        return $ordersCollection;
    }
}
