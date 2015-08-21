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

class Cybage_Marketplace_Model_Selleroptions extends Mage_Core_Model_Abstract
{
    public function getCommentCollction(){
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $_order = Mage::getModel('sales/order')->load($orderId);
        
        $collection = Mage::getResourceModel('sales/order_status_history_collection')
                     ->addFieldToFilter('comment',array('neq'=>NULL))
                     ->setOrder('entity_id', 'desc')
                     ->addFieldToFilter('parent_id', $orderId)
                     ->load();
        
        return $collection;
    }

    /*Calculate aggregate of seller ratings.*/
    public function getSellerRatingsAggregate(){
        $sellerName = urldecode(Mage::registry('seller_company'));
        $seller     = Mage::helper('marketplace')->getSellerInfo($sellerName);
        $_sellerId  = $seller['entity_id'];
        
        $_avgRating = $_percent = $_prodCount = $_reviewCount = 0;
        $optionCount = 1;
        $_sellerReviewData = array();
        
        $_productCollection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToSelect('entity_id')
                            ->addAttributeToFilter('seller_id',array('eq' => $_sellerId ));
                            
        $_prodCount = count($_productCollection);
                    
        foreach($_productCollection as $_product){
            $_productId = $_product->getEntityId();

            $_reviews = Mage::getModel('review/review')
                      ->getResourceCollection()
                      ->addStoreFilter(Mage::app()->getStore()->getId()) 
                      ->addEntityFilter('product', $_productId)
                      ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                      ->setDateOrder()
                      ->addRateVotes();
                      
            $_reviewCount = $_reviewCount + count($_reviews);
            
            if ($_reviewCount > 0) {
               foreach ($_reviews->getItems() as $_review) {
                  foreach( $_review->getRatingVotes() as $_vote ) {
                    $optionCount++;
                    $_percent = $_percent + $_vote->getPercent();
                  }
                }
            }
        }

        $_avgRating = $_percent / $_prodCount;
        $_avgRating = $_avgRating/$optionCount;
        $_sellerReviewData['avgrating'] = $_avgRating;
        $_sellerReviewData['reviewcount'] = $_reviewCount;
        $_sellerReviewData['seller_id'] = $_sellerId;

        return $_sellerReviewData;
    }
    
    public function getSellerAllRatingsAggregate($_sellerId) {
        $_avgRating = $_percent = $_prodCount = $_reviewCount = 0;
        $optionCount = 1; 
        $_sellerReviewData = array();
        
        $_productCollection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToSelect('entity_id')
                            ->addAttributeToFilter('seller_id',array('eq' => $_sellerId ));
                            
        $_prodCount = count($_productCollection);
                    
        foreach($_productCollection as $_product){
            $_productId = $_product->getEntityId();

            $_reviews = Mage::getModel('review/review')
                      ->getResourceCollection()
                      ->addStoreFilter(Mage::app()->getStore()->getId()) 
                      ->addEntityFilter('product', $_productId)
                      ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                      ->setDateOrder()
                      ->addRateVotes();
                      
            $_reviewCount = $_reviewCount + count($_reviews);
            
            if ($_reviewCount > 0) {
               foreach ($_reviews->getItems() as $_review) {
                  foreach( $_review->getRatingVotes() as $_vote ) {
                    $optionCount++;
                    $_percent = $_percent + $_vote->getPercent();
                  }
                }
            }
        }

        $_avgRating = $_percent / $_prodCount;
        $_avgRating = $_avgRating/$optionCount;
        $_sellerReviewData['avgrating'] = $_avgRating;
        $_sellerReviewData['reviewcount'] = $_reviewCount;
        $_sellerReviewData['seller_id'] = $_sellerId;
        
        return $_sellerReviewData;
    }

    /*Get seller "approved" status id for filtering result set*/
    public function getSellerApprovedStatusId(){
       /*Get customer "status" attribute options*/
        $options = Mage::getModel('eav/config')->getAttribute('customer', 'status')->getSource()->getAllOptions(); //get all options
        
        $optionId = false;
        foreach ($options as $option) {
            if ($option['label'] == 'Approved'){
                $optionId = $option['value']; 
                break;
            }
        }
        
        return $optionId;
    }
}
