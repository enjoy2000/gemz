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

class Cybage_Marketplace_Model_Marketplace extends Mage_Core_Model_Abstract
{
    const CACHE_TAG = 'marketplace';
    const ATTRIBUTE_SET_NAME = 'default';

    /**
     *    Method : To get attribute set id
     **/
    public function getAttributeSet() {
        $attr_set_name = self::ATTRIBUTE_SET_NAME;
        $attribute_set_id = Mage::getModel('eav/entity_attribute_set')
                ->load($attr_set_name, 'attribute_set_name')
                ->getAttributeSetId();
        return $attribute_set_id;
    }

    /**
     *    Method : To get size of file to upload
     **/
    public function getFileSize() {
        return Mage::getStoreConfig('marketplace/upload_file_size');
    }

    /**
     * Get few Best Seller Product of the Seller based on admin configuration
     * @return Array
     * * */
    public function getBestSellerProducts() {
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $products = Mage::getResourceModel('reports/product_collection')
                ->addAttributeToSelect('*')
                ->addOrderedQty()
                ->addAttributeToFilter('seller_id', $customerId)
                ->setOrder('ordered_qty', 'desc');
        return $products;
    }

    /**
     * Get Last few orders of Seller Product
     * @return Array
     * * */
    public function getLastFewOrder() {
        //Get seller's product id
        $sellerProductIds = $this->getsellersProducts();
        $collection = Mage::getResourceModel('sales/order_item_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('product_id', array('in' => $sellerProductIds))
                ->setOrder('qty_ordered', 'desc');
        return $collection;
    }

    /**
     * Get Seller's produc ids
     * @return Array
     * * */
    public function getSellersProducts() {
        //Get Logged in customer id
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            //Get Seller_id attribute id
            $_product = Mage::getModel('catalog/product');
            $attributeId = $_product->getResource()->getAttribute('seller_id')->getAttributeId();
                         
            $select = $connectionRead->select('coe.entity_id')
                    ->from($this->resource()->getTableName('catalog_product_entity').' As coe')
                    ->joinLeft(array('cpet' => $this->resource()->getTableName('catalog_product_entity_int')), 'coe.entity_id = cpet.entity_id')
                    ->where('attribute_id=?', $attributeId)
                    ->where('value=?', $customerId);
            $sellerProducts = $connectionRead->fetchCol($select);
            return $sellerProducts;
        }
    }

    public function resource() {
        return Mage::getSingleton("core/resource");
    }

    public function read() {
        return $this->resource()->getConnection('core_read');
    }

    public function write() {
        return $this->resource()->getConnection('core_write');
    }

    /* Get question count for which reply not present. */
    public function getUnrepliedQueCount() {
        $unrepliedQueCount = 0;
        $subselect = $this->read()->select()->distinct(true)->from(array('q' => $this->resource()->getTableName('marketplace_askquestion_question')), array('q.entity_id'))
                ->joinRight(array('r' => $this->resource()->getTableName('marketplace_askquestion_reply')), ' q.entity_id = r.parent_id', array());

        $select = $this->read()->select()->from(array("q" => $this->resource()->getTableName("marketplace_askquestion_question")), array("count(distinct q.entity_id)"))
                ->where("q.entity_id not in ($subselect)");

        if ($product_str = implode(",", $this->getSellersProducts())) {
            $where = "q.product_id in (" . $product_str . ")";
            $select->where($where);
            $unrepliedQueCount = $this->read()->fetchOne($select);
        }

        return $unrepliedQueCount;
    }
}
