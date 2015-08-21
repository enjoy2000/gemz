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

class Cybage_Marketplace_Block_Adminhtml_Orderby_Grid_Abstract extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_resourceCollectionName = '';
    protected $_currentCurrencyCode = null;
    protected $_storeIds = array();
    protected $_aggregatedColumns = null;

    public function __construct() {
        parent::__construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setUseAjax(false);
        if (isset($this->_columnGroupBy)) {
            $this->isColumnGrouped($this->_columnGroupBy, true);
        }
        $this->setEmptyCellLabel(Mage::helper('reports')->__('No records found for this period.'));
    }

    public function getResourceCollectionName() {
        return $this->_resourceCollectionName;
    }

    public function getCollection() {
        if (is_null($this->_collection)) {
            $this->setCollection(Mage::getModel('reports/grouped_collection'));
        }
        return $this->_collection;
    }

    protected function _getAggregatedColumns() {
        if (is_null($this->_aggregatedColumns)) {
            foreach ($this->getColumns() as $column) {
                if (!is_array($this->_aggregatedColumns)) {
                    $this->_aggregatedColumns = array();
                }
                if ($column->hasTotal()) {
                    $this->_aggregatedColumns[$column->getId()] = "{$column->getTotal()}({$column->getIndex()})";
                }
            }
        }
        return $this->_aggregatedColumns;
    }

    /**
     * Add column to grid
     * Overriden to add support for visibility_filter column option
     * It stands for conditional visibility of the column depending on filter field values
     * Value of visibility_filter supports (filter_field_name => filter_field_value) pairs
     *
     * @param   string $columnId
     * @param   array $column
     * @return  Mage_Adminhtml_Block_Report_Grid_Abstract
     */
    public function addColumn($columnId, $column) {
        if (is_array($column) && array_key_exists('visibility_filter', $column)) {
            $filterData = $this->getFilterData();
            $visibilityFilter = $column['visibility_filter'];
            if (!is_array($visibilityFilter)) {
                $visibilityFilter = array($visibilityFilter);
            }
            foreach ($visibilityFilter as $k => $v) {
                if (is_int($k)) {
                    $filterFieldId = $v;
                    $filterFieldValue = true;
                } else {
                    $filterFieldId = $k;
                    $filterFieldValue = $v;
                }
                if (!$filterData->hasData($filterFieldId) || $filterData->getData($filterFieldId) != $filterFieldValue) {
                    return $this;  // don't add column
                }
            }
        }
        return parent::addColumn($columnId, $column);
    }

    protected function _prepareCollection() {
        $filterData = $this->getFilterData();

        if ($filterData->getData('from') == null || $filterData->getData('to') == null) {
            $this->setCountTotals(false);
            $this->setCountSubTotals(false);
            return parent::_prepareCollection();
        }

        $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
                ->setPeriod($filterData->getData('period_type'))
                ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                ->setMarketplaceUser($filterData->getData('marketplace_user'))
                ->addStoreFilter(explode(',', $filterData->getData('store_ids')))
                ->addOrderStatusFilter($filterData->getData('order_statuses'))
                ->setAggregatedColumns($this->_getAggregatedColumns());

        if ($this->_isExport) {
            $this->setCollection($resourceCollection);
            return $this;
        }

        if ($filterData->getData('show_empty_rows', false)) {
            Mage::helper('reports')->prepareIntervalsCollection(
                    $this->getCollection(), $filterData->getData('from', null), $filterData->getData('to', null), $filterData->getData('period_type')
            );
        }

        if ($this->getCountSubTotals()) {
            $this->getSubTotals();
        }

        if ($this->getCountTotals()) {
            $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->setMarketplaceUser($filterData->getData('marketplace_user'))
                    ->addStoreFilter(explode(',', $filterData->getData('store_ids')))
                    ->addOrderStatusFilter($filterData->getData('order_statuses'))
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->isTotals(true);
            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        }

        $this->getCollection()->setColumnGroupBy($this->_columnGroupBy);
        $this->getCollection()->setResourceCollection($resourceCollection);

        return parent::_prepareCollection();
    }

    public function getCountTotals() {
        if (!$this->getTotals()) {
            $filterData = $this->getFilterData();
            $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->setMarketplaceUser($filterData->getData('marketplace_user'))
                    ->addStoreFilter(explode(',', $filterData->getData('store_ids')))
                    ->addOrderStatusFilter($filterData->getData('order_statuses'))
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->isTotals(true);
            if (count($totalsCollection->getItems()) < 1 || !$filterData->getData('from')) {
                $this->setTotals(new Varien_Object());
            } else {
                foreach ($totalsCollection->getItems() as $item) {
                    $this->setTotals($item);
                    break;
                }
            }
        }
        return parent::getCountTotals();
    }

    public function getSubTotals() {
        $filterData = $this->getFilterData();
        $subTotalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
                ->setPeriod($filterData->getData('period_type'))
                ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                ->setMarketplaceUser($filterData->getData('marketplace_user'))
                ->addStoreFilter(explode(',', $filterData->getData('store_ids')))
                ->addOrderStatusFilter($filterData->getData('order_statuses'))
                ->setAggregatedColumns($this->_getAggregatedColumns())
                ->isSubTotals(true);
        $this->setSubTotals($subTotalsCollection->getItems());
        return parent::getSubTotals();
    }

    public function setStoreIds($storeIds) {
        $this->_storeIds = $storeIds;
        return $this;
    }

    public function getCurrentCurrencyCode() {
        if (is_null($this->_currentCurrencyCode)) {
            $this->_currentCurrencyCode = (count($this->_storeIds) > 0) ? Mage::app()->getStore(array_shift($this->_storeIds))->getBaseCurrencyCode() : Mage::app()->getStore()->getBaseCurrencyCode();
        }
        return $this->_currentCurrencyCode;
    }
}
