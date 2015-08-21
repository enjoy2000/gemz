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

class Cybage_Marketplace_Block_Adminhtml_Orderby_Grid extends Cybage_Marketplace_Block_Adminhtml_Orderby_Grid_Abstract
{
    protected $_columnGroupBy = 'seller_name';

    public function __construct() {
        parent::__construct();
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName() {
        return 'marketplace/orderby_collection';
    }

    protected function _prepareColumns() {
        $this->addColumn('seller_name', array(
            'header' => Mage::helper('sales')->__('Seller Name'),
            'index' => 'seller_name',
            'width' => '100px',
            'type' => 'text',
            'renderer' => 'Cybage_Marketplace_Block_Adminhtml_Orderby_Grid_Name',
            'totals_label' => Mage::helper('sales')->__(''),
            'sortable' => false,
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Period'),
            'index' => 'created_at',
            'width' => 100,
            'sortable' => false,
            'period_type' => $this->getPeriodType(),
            'totals_label' => Mage::helper('sales')->__('Total'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('total_item_count', array(
            'header' => Mage::helper('sales')->__('Ordered Items'),
            'index' => 'total_item_count',
            'width' => '100px',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false
        ));

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('total_income_amount', array(
            'header' => Mage::helper('sales')->__('Sales Total'),
            'index' => 'total_income_amount',
            'currency_code' => $currencyCode,
            'width' => '100px',
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addColumn('base_total_invoiced', array(
            'header' => Mage::helper('sales')->__('Invoiced'),
            'index' => 'base_total_invoiced',
            'currency_code' => $currencyCode,
            'width' => '100px',
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false
        ));

        $this->addColumn('base_total_refunded', array(
            'header' => Mage::helper('sales')->__('Refunded'),
            'index' => 'base_total_refunded',
            'currency_code' => $currencyCode,
            'width' => '100px',
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false
        ));
/*
        $this->addColumn('base_shipping_amount', array(
            'header' => Mage::helper('sales')->__('Shipping Amount'),
            'index' => 'base_shipping_amount',
            'currency_code' => $currencyCode,
            'width' => '100px',
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false
        ));
*/
        $this->addColumn('discount_amount', array(
            'header' => Mage::helper('sales')->__('Total Discount'),
            'index' => 'discount_amount',
            'currency_code' => $currencyCode,
            'width' => '100px',
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false
        ));
/*
        $this->addColumn('base_total_canceled', array(
            'header' => Mage::helper('sales')->__('Total Cancelled'),
            'index' => 'base_total_canceled',
            'currency_code' => $currencyCode,
            'width' => '100px',
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false
        ));
        */

        $this->addExportType('*/*/exportOrderCsv', Mage::helper('marketplace')->__('CSV'));
        $this->addExportType('*/*/exportOrderExcel', Mage::helper('marketplace')->__('Excel XML'));
        return parent::_prepareColumns();
    }
}
