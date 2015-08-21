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

class Cybage_Marketplace_Block_Review_Product_View extends Mage_Catalog_Block_Product_View
{
    protected $_reviewsCollection;

    protected function _prepareLayout()
    {
        $sellerId = $this->getRequest()->getParam('seller_id');
        if(!empty($sellerId))
        {   
            $listAllowedValue = Mage::getStoreConfig('catalog/frontend/list_per_page_values');
            $listAllowedValues = explode(",", $listAllowedValue);
            
            $listAllowedValues = array_combine($listAllowedValues,$listAllowedValues);
            
            $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
            $pager->setAvailableLimit($listAllowedValues);
            $pager->setCollection($this->getReviewsCollection());
            $this->setChild('pager', $pager);
            $this->getReviewsCollection()->load();
            return $this;
        }
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    public function getReviewsCollection()
    {
        $sellerId = $this->getRequest()->getParam('seller_id');
        
        if(!empty($sellerId))
        {        
            $collection = Mage::getModel('catalog/product')->getCollection()
                          ->addAttributeToFilter('seller_id', $sellerId)
                          ->addAttributeToSelect('entity_id');
                        
                $productIds = array();
                foreach ($collection as $product)  
                {            
                    $productIds[] =  $product->getId();
                }
                
                if (null === $this->_reviewsCollection) 
                {
                    $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                        ->addStoreFilter(Mage::app()->getStore()->getId())
                        ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                        ->addFieldToFilter('entity_pk_value', array('in'=>$productIds))
                        ->setDateOrder();
                        
                }
                return $this->_reviewsCollection;            
        }
    }
    
    public function getRatingInfo($_review)
    {
                 $votesCollection = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection()
                ->setReviewFilter($_review->getId())
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->addRatingInfo(Mage::app()->getStore()->getId())
                ->load();
            return $votesCollection;

    }
}
