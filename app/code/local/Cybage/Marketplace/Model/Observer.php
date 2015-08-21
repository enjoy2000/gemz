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

class Cybage_Marketplace_Model_Observer {
    /*Save seller id and sipping charge in quote table.*/
    public function saveSellerId(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $quoteItem = $event->getQuoteItem();
        $product = $event->getProduct();
      
        $sellerId = $product->getData('seller_id');
        $shippingCharges = $product->getData('shipping_charges');
        
        $quoteItem->setData('seller_id',$sellerId);
        $quoteItem->setData('shipping_charges',$shippingCharges);
    }

    public $productActionModel;
    /**
     * Validate Product's markeplace status if status of the product is enabled while editing or adding product
     * @param catalog_product_save_before
     * @author Shilpag
     */
    public function validateProductMarketplaceStatus($observer)
    {

        
        $product = $product =$observer->getEvent()->getProduct();
        $productStatus = $product->getStatus();
        $isSeller = $product->getSellerId();
        $productMktStatus = $product->getMarketplaceState();

        if ($isSeller)
        {
            $this->getproductActionModel()->validateProductStatus($productStatus, $productMktStatus);
        }
    }

    /**
     * Validate Products markeplace status if status of the products is enabled while performing mass action on them
     * @param catalog_product_attribute_update_before
     * @author Shilpag
     */
    public function validateMassActionProductMarketplaceStatus($observer)
    {
        $productData = $observer->getEvent()->getData();
        $arrProductIds = $productData['product_ids'];
        $atributeData = $observer->getEvent()->getAttributesData();
        $productMktStatus = $atributeData['marketplace_state'];
        $productStatus = $atributeData['status'];
        $productAction = $this->getproductActionModel();
        foreach ($arrProductIds as $productId)
        {
            $product = Mage::getModel('catalog/product')->load($productId);
            //$attributeSetId = $product->getAttributeSetId();
            $isSeller = $product->getSellerId();
            if ($isSeller )
            {
                if ( $productStatus && $productMktStatus)
                {
                    $productAction->validateProductStatus($productStatus, $productMktStatus);
                } else if ( $productStatus ) {
                    $originalMarkeplaceStatus = $product->getMarketplaceStatus();
                    $productAction->validateProductStatus($productStatus, $originalMarkeplaceStatus);
                } else if ( $productMktStatus ) {
                    $originalproductStatus = $product->getStatus();
                    $productAction->validateProductStatus($originalproductStatus, $productMktStatus);
                }
            }
        }
    }

    /**
     * Get model instance for class TM_Adminhtml_Model_Product_Action
     */
    public function getproductActionModel() {
        if (!($this->productActionModel)) {
            $this->productActionModel = Mage::getModel('marketplace/product_action');
        }
        return $this->productActionModel;
    }
    
    public function pendingorders()
    {   
        $daylimit = Mage::getStoreConfig('marketplace/seller/order_pending_before_days',Mage::app()->getStore());        
        $date1=date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(strtotime('-'.$daylimit.'day', time()))); 
        $resource = Mage::getSingleton("core/resource"); 
        $orderItemsCollection = Mage::getResourceModel('sales/order_item_collection');
        $orderItemsCollection->getSelect()->join(array('sfo' =>$resource->getTableName('sales_flat_order')), 'main_table.order_id = sfo.entity_id', array('sfo.entity_id','sfo.status','sfo.created_at'));
        $orderItemsCollection->addAttributeToFilter('sfo.created_at',array('lteq' =>$date1));
        $orderItemsCollection->addAttributeToFilter('status', array('eq' =>'pending'));
        
        $arr_order_for_filter=array();
        foreach ($orderItemsCollection->getData() as $orderItem) {
            $sellerId=$orderItem['seller_id'];
            $arr_order_for_filter[$orderItem['order_id']]= $sellerId;
        }
        
        $orderDetail = array();            
        foreach($arr_order_for_filter as $key => $value){
            $orderDetail[$value][] = $key;
        }
        
        foreach($orderDetail as $key=>$value){
        
            $sellerId = $key;
            $customer = Mage::getModel('customer/customer')->load($sellerId);            
            $orderCount = count($value);
            $orderIds = implode(",", $value);
            $this->sendTransactionalEmail($customer,$orderCount,$orderIds);
        }
    }
    
    public function sendTransactionalEmail($customer,$orderCount,$orderIds)
    {
        // Transactional Email Template's ID
        $templateId = Mage::getStoreConfig('marketplace/seller/order_pending_reminder_template');        
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
        $vars = array(
        'customer'=>$customer,
        'ordercount'=>$orderCount,
        'orderids'=>$orderIds
        );
        $translate = Mage::getSingleton('core/translate');
        // Send Transactional Email
        Mage::getModel('core/email_template')
        ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
        $translate->setTranslateInline(true);
    }
}
