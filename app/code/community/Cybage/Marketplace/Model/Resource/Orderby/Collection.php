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

class Cybage_Marketplace_Model_Resource_Orderby_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_periodFormat;
    protected $_selectedColumns = array();
    protected $_from = null;
    protected $_to = null;
    protected $_orderStatus = null;
    protected $_period = null;
    protected $_storesIds = 0;
    protected $_applyFilters = true;
    protected $_isTotals = false;
    protected $_isSubTotals = false;
    protected $_aggregatedColumns = array();
    protected $_marketplaceuser = 0;
    protected $_tablealias = 'main_table';

    /**
     * Initialize custom resource model
     */
    public function __construct() {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/order_item');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _getSelectedColumns() {
        if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT('.$this->_tablealias.'.created_at, \'%Y-%m\')';
        } elseif ('quarter' == $this->_period) {
            $this->_periodFormat = 'CONCAT(YEAR('.$this->_tablealias.'.created_at), \', Quarter\', QUARTER(created_at))'; //'DATE_FORMAT(created_at, \'%Y-Quarter %q \')';
        } else {
            $this->_periodFormat = 'DATE_FORMAT('.$this->_tablealias.'.created_at, \'%Y- Week %u\')';
        }

        $this->_selectedColumns = array(
            'created_at' => $this->_periodFormat,
            'seller_name' => $this->_tablealias.'.seller_id',
            'total_item_count' => 'SUM('.$this->_tablealias.'.qty_ordered)',
            'total_income_amount' => 'IFNULL(SUM(('.$this->_tablealias.'.base_row_total)), 0)',
            'base_total_invoiced' => 'IFNULL(SUM('.$this->_tablealias.'.base_row_invoiced),0)',
            'base_total_refunded' => 'IFNULL(SUM('.$this->_tablealias.'.base_amount_refunded),0)',
            //'base_shipping_amount' => 'IFNULL(SUM(base_shipping_amount),0)',
            'discount_amount' => 'IFNULL(SUM('.$this->_tablealias.'.base_discount_amount),0)',
            //'base_total_canceled' => 'IFNULL(SUM(base_total_canceled),0)'
        );

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _initSelect() {
        $this->getSelect()->from(array($this->_tablealias => $this->getResource()->getMainTable()), $this->_getSelectedColumns());
        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_tablealias.'.seller_id')->group($this->_periodFormat);
        }
        return $this;
    }

    /**
     * Set marketplace user id
     *
     * @param $marketplaceUser
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setMarketplaceUser($marketplaceUser) {
        $this->_marketplaceuser = $marketplaceUser;
        return $this;
    }

    /**
     * Set array of columns that should be aggregated
     *
     * @param array $columns
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setAggregatedColumns(array $columns) {
        $this->_aggregatedColumns = $columns;
        return $this;
    }

    /**
     * Retrieve array of columns that should be aggregated
     *
     * @return array
     */
    public function getAggregatedColumns() {
        return $this->_aggregatedColumns;
    }

    /**
     * Set date range
     *
     * @param mixed $from
     * @param mixed $to
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setDateRange($from = null, $to = null) {
        $this->_from = $from;
        $this->_to = $to;
        return $this;
    }

    /**
     * Set period
     *
     * @param string $period
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setPeriod($period) {
        $this->_period = $period;
        return $this;
    }

    /**
     * Apply date range filter
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyDateRangeFilter() {
        if (!is_null($this->_from)) {
            $this->_from = date('Y-m-d G:i:s', strtotime($this->_from));
            $this->getSelect()->where($this->_tablealias.'.created_at >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->_to = date('Y-m-d G:i:s', strtotime($this->_to));
        }
        $this->getSelect()->where($this->_tablealias.'.created_at <= ?', $this->_to);
        return $this;
    }

    /**
     * Set store ids
     *
     * @param mixed $storeIds (null, int|string, array, array may contain null)
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function addStoreFilter($storeIds) {
        $this->_storesIds = $storeIds;
        return $this;
    }

    /**
     * Apply stores filter to select object
     *
     * @param Zend_Db_Select $select
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyStoresFilterToSelect(Zend_Db_Select $select) {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];

        if ($nullCheck) {
            $select->where('store_id IN(?) OR store_id IS NULL', $storeIds);
        } else {
            $select->where('store_id IN(?)', $storeIds);
        }

        return $this;
    }

    /**
     * Apply stores filter
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyStoresFilter() {
        return $this->_applyStoresFilterToSelect($this->getSelect());
    }

    /**
     * Set status filter
     *
     * @param string|array $state
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function addOrderStatusFilter($orderStatus) {
        $this->_orderStatus = $orderStatus;
        return $this;
    }

    /**
     * Apply order status filter
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyOrderStatusFilter() {
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
        $resource = Mage::getSingleton("core/resource");
        $this->getSelect()->join(array('sfo' =>$resource->getTableName('sales_flat_order')), $this->_tablealias.'.order_id = sfo.entity_id', array('sfo.entity_id','sfo.status'));
        $this->getSelect()->where('sfo.status IN(?)', $orderStatus);
        return $this;
    }

    /**
     * Set apply filters flag
     *
     * @param boolean $flag
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setApplyFilters($flag) {
        $this->_applyFilters = $flag;
        return $this;
    }

    /**
     * Getter/Setter for isTotals
     *
     * @param null|boolean $flag
     * @return boolean|TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function isTotals($flag = null) {
        if (is_null($flag)) {
            return $this->_isTotals;
        }
        $this->_isTotals = $flag;
        return $this;
    }

    /**
     * Getter/Setter for isSubTotals
     *
     * @param null|boolean $flag
     * @return boolean|TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function isSubTotals($flag = null) {
        if (is_null($flag)) {
            return $this->_isSubTotals;
        }
        $this->_isSubTotals = $flag;
        return $this;
    }

    /**
     * Apply seller name filter
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applySellerNameFilter() {
        if (!$this->_marketplaceuser) {
            $this->getSelect()->where($this->_tablealias.'.seller_id > ?', 0);
        } else {
            $this->getSelect()->where($this->_tablealias.'.seller_id = ?', $this->_marketplaceuser);
        }
        return $this;
    }

    /**
     * Load data
     * Redeclare parent load method just for adding method _beforeLoad
     *
     * @return  Varien_Data_Collection_Db
     */
    public function load($printQuery = false, $logQuery = false) {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_initSelect();
        if ($this->_applyFilters) {
            $this->_applyDateRangeFilter()
                    ->_applyOrderStatusFilter()
                    ->_applySellerNameFilter();
        }
        return parent::load($printQuery, $logQuery);
    }
}
