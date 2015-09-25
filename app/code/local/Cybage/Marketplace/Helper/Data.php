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

class Cybage_Marketplace_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MARKETPLACE_PRODUCT_VISIBILITY = 'marketplace/product/visibility';
    const MARKETPLACE_PRODUCT_STATUS = 'marketplace/product/status';
    const MARKETPLACE_PRODUCT_STATE = 'marketplace/product/state';
    const MARKETPLACE_PRODUCT_UPLOAD_IMAGE_SIZE = 'marketplace/product/upload_image_size';
    const DELETED_OPTION_LABEL = "Deleted";
    const APPROVED_OPTION_LABEL = "Approved";
    const MARKETPLACE_ENABLE = "marketplace/marketplace/enable";
    const MARKETPLACE_STATUS_APPROVED = "marketplace/status/approved";
    const MARKETPLACE_PRODUCT_PENDING = 'marketplace/product/pending';
    const MARKETPLACE_PRODUCT_APPROVED = 'marketplace/product/approved';
    const MARKETPLACE_PRODUCT_REJECTED = 'marketplace/product/rejected';
    const MARKETPLACE_PRODUCT_DELETED = 'marketplace/product/deleted';

    /**
     * Collection of Allowed Category for Marketplace
     * */
    public function getCategoryCollection() {
        return Mage::getModel('catalog/category')->getCollection()
                        ->addAttributeToFilter('is_active', 1)
                        ->addAttributeToFilter('category_marketplace', 1) //category_marketplace
        ;
    }

    public function getNewProductVisibility() {

        return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_VISIBILITY);
    }

    public function isMarketplaceEnabled() {
        return (bool) Mage::getStoreConfig(self::MARKETPLACE_ENABLE, Mage::app()->getStore());
    }

    public function isMarketplaceActiveSellar() {
        if ($this->isMarketplaceEnabled()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getStatus() == Mage::getStoreConfig(self::MARKETPLACE_STATUS_APPROVED) && (bool) $customer->getSellerSubscriber()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getNewProductStatus() {

        return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_STATUS);
    }

    public function getNewProductState() {

        return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_STATE);
    }

    public function getNewProductUploadImageSize($check=null) {
        if ($check == 'validate') {
            return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_UPLOAD_IMAGE_SIZE);
        } else {
            return round(Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_UPLOAD_IMAGE_SIZE)/(1024 * 1024), 2) .' MB';
        }
    }

    /**
     *    Method : To get Product Pending Status Id
     * */
    public function getProductPendingStatusId() {
        return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_PENDING);
    }

    /**
     *    Method : To get Product Pending Status Id
     * */
    public function getProductApprovedStatusId() {
        return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_APPROVED);
    }

    /**
     *    Method : To get Product Pending Status Id
     * */
    public function getProductRejectedStatusId() {
        return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_REJECTED);
    }

    /**
     *    Method : To get Product Pending Status Id
     * */
    public function getProductDeletedStatusId() {
        return Mage::getStoreConfig(self::MARKETPLACE_PRODUCT_DELETED);
    }

    public function getDeletedOptionValue() {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'marketplace_state');
        $optionValue = "";
        foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
            if ($option['label'] == self::DELETED_OPTION_LABEL) {
                $optionValue = $option['value'];
            }
        }
        if ($optionValue) {
            return $optionValue;
        } else {
            return FALSE;
        }
    }

    public function getApprovedOptionValue() {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'marketplace_state');
        $optionValue = "";
        foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
            if ($option['label'] == self::APPROVED_OPTION_LABEL) {
                $optionValue = $option['value'];
            }
        }
        if ($optionValue) {
            return $optionValue;
        } else {
            return FALSE;
        }
    }

    public function getSku($productId) {
        return Mage::getModel('catalog/product')->load($productId)->getSku();
    }

    public function getProductName($productId) {
        return Mage::getModel('catalog/product')->load($productId)->getName();
    }

    public function isMarketplaceApprovedProduct($currentProduct) {
        $marketplaceState = $currentProduct->getMarketplaceState();
        $marketplaceApprovedOptionValue = $this->getApprovedOptionValue();

        if ($marketplaceState === $marketplaceApprovedOptionValue) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function isSelfProduct($currentProduct) {

        $sellerId = $currentProduct->getSellerId();
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if ($sellerId === $customerId) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param customer_id
     * @return customer object
     * @author srinidhid@cybage.com
     */
    public function getSellerInfo($productSellerName) {
        $customer = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('company_name', $productSellerName)
                ->getFirstItem()
                ->load();
        return $customer->getData();
    }

    /* To fetch MarketOrders From sales ordered to seller in Csv */

    public function getMarketOrders() {
        $dateTo = Mage::getSingleton('core/session')->getDateTo();
        $dateFrom = Mage::getSingleton('core/session')->getDateFrom();
        $orderStatus = Mage::getSingleton('core/session')->getSalesOrderSatus();

        $sellerId = Mage::getSingleton('customer/session')->getId();

        $orderItemCollection = Mage::getResourceModel('sales/order_item_collection')
                ->addFieldToSelect('order_id')
                ->addFieldToFilter('main_table.seller_id', $sellerId)
                ->distinct(true);
        $resource = Mage::getSingleton("core/resource");
        $tblSalesFlatOrder = $resource->getTableName('sales_flat_order');        
        $orderItemCollection->getSelect()
                ->join($tblSalesFlatOrder, $tblSalesFlatOrder.'.entity_id=main_table.order_id', array('increment_id', 'status'))
                ->columns(new Zend_Db_Expr("CONCAT(`$tblSalesFlatOrder`.`customer_firstname`, ' ',`$tblSalesFlatOrder`.`customer_lastname`) AS billname"));

        $orderItemCollection->getSelect()->columns('SUM(row_total + shipping_charges) AS Total');
        $orderItemCollection->getSelect()->columns('SUM(row_invoiced) AS Amount Received');
        $orderItemCollection->getSelect()->columns('SUM((row_total + shipping_charges) - row_invoiced) AS Amount Remain');
        $orderItemCollection->getSelect()->group('main_table.order_id');

        $dateFrom = date('Y-m-d H:i:s', strtotime($dateFrom));
        $dateTo = date('Y-m-d H:i:s', strtotime($dateTo . "+1 day"));

        if ($dateFrom != '') {
            $orderItemCollection->addFieldToFilter($tblSalesFlatOrder.'.created_at', array('from' => "$dateFrom"));
        }
        if ($dateTo != '') {
            $orderItemCollection->addFieldToFilter($tblSalesFlatOrder.'.created_at', array('to' => "$dateTo"));
        }
        if ($orderStatus != '') {
            $orderItemCollection->addFieldToFilter($tblSalesFlatOrder.'.status', "$orderStatus");
        }

        return($orderItemCollection);
    }

    /**
     * Generates CSV file with product's list according to the collection in the $this->_list
     * @return array
     */
    public function generateMarketCsv() {
        $this->getMarketListOrders();
        if (!is_null($this->_list)) {
            $orders = $this->_list->getItems();
            if (count($orders) > 0) {
                $io = new Varien_Io_File();
                $path = Mage::getBaseDir('var') . DS . 'export' . DS;
                $name = md5(microtime());
                $file = $path . DS . $name . '.csv';
                $io->setAllowCreateFolders(true);
                $io->open(array('path' => $path));
                $io->streamOpen($file, 'w+');
                $io->streamLock(true);
                $io->streamWriteCsv($this->_getCsvHeaders());

                foreach ($orders as $order) {
                    $data = array();
                    $data[] = $order->getData('increment_id');
                    $data[] = $order->getData('billname');
                    $data[] = $order->getData('status');
                    $data[] = $order->getData('Total');
                    $data[] = $order->getData('Amount Received');
                    $data[] = $order->getData('Amount Remain');
                    $io->streamWriteCsv($data);
                }

                return array
                    (
                    'type' => 'filename',
                    'value' => $file,
                    'rm' => true
                );
            }
        }
    }

    /* Retrieve Market list to export data orders */

    public function getMarketListOrders() {
        $orders = $this->getMarketOrders();
        $this->setList($orders);
    }

    /**
     * Sets current collection
     * @param $query
     */
    public function setList($collection) {
        $this->_list = $collection;
    }

    /* Generate headers for CSV file */

    protected function _getCsvHeaders() {
        $headers = array('Order#', 'Bill to Name', 'Status', 'Total sales', 'Amount Received', 'Amount Remain');
        return $headers;
    }

    /**
      Get order sutotal.shipping charge and grand total for specific seller.
     * */
    public function getOrderTotals($orderId) {
        $marketPlaceModel = Mage::getModel('marketplace/order');
        $orderItemCollection = $marketPlaceModel->getOrderDetails($orderId);

        $orderTotal = array();
        $orderTotal['subtotal'] = 0;  
        $orderTotal['shippingcharge'] = 0;
        foreach ($orderItemCollection as $_item) {
            $orderTotal['subtotal'] += (float) $_item->getRowTotal();
            $orderTotal['shippingcharge'] += (float) $_item->getShippingCharges();
        }

        $orderTotal['grandtotal'] = $orderTotal['subtotal'] + $orderTotal['shippingcharge'];

        return $orderTotal;
    }

    /**
     * @param seller_id
     * @return customer name
     */
    public function getSellerName($seller_id) {
        $customer = Mage::getModel('customer/customer')->load($seller_id);
        return $customer->getCompanyName();
    }

    public function getProductSellerAttributeId(){
       $_sellerAttrId = $_attributeData = '';
       $_attributeData = Mage::getModel('eav/entity_attribute')->getCollection()
       ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
       ->addFieldToFilter('attribute_code', 'seller_id'); 

        if($_attributeData){
           foreach($_attributeData as $_data){
               $_sellerAttrId = $_data['attribute_id'];
           }
        }

        return $_sellerAttrId;
    }
}
