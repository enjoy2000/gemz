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

class Cybage_Marketplace_Block_Adminhtml_Commission_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('commissionGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('status')
            ->addAttributeToFilter('status', array('notnull'=>1))
            ->joinTable('sales/order_item',
                'seller_id=entity_id',
                array(
                        'totalsales' => 'IFNULL(SUM(base_row_total), 0)',
                        'amountrecived' => 'IFNULL(SUM(base_row_invoiced), 0)',
                        'amountremain' => 'IFNULL((SUM(base_row_total) - SUM(base_row_invoiced)), 0)',
                     ),
                null,
                'left')
            ->groupByEmail();

        $query = '('.$collection->getSelect()->__toString().')';
        $collection->getSelect()->reset()->from(array('e' => new Zend_Db_Expr($query)));
        $collection->joinTable(array('commission' => 'marketplace/commission'),
                'seller_id=entity_id',
                array(
                        'totalpayamount' => 'IFNULL(SUM(commission.amount), 0)',
                     ),
                null,
                'left');
        $collection->getSelect()->group('e.entity_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('marketplace')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('marketplace')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('marketplace')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('marketplace')->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getModel('marketplace/customatributestatus')->toOptionArray(),
        ));

        $this->addColumn('totalsales', array(
            'header'    => Mage::helper('marketplace')->__('Total Sales'),
            'width'     => '150',
            'filter'     => false,
            'type'  => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'index'     => 'totalsales'
        ));

        $this->addColumn('amountrecived', array(
            'header'    => Mage::helper('marketplace')->__('Amount Received'),
            'width'     => '150',
            'filter'     => false,
            'type'  => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'index'     => 'amountrecived'
        ));

        $this->addColumn('amountremain', array(
            'header'    => Mage::helper('marketplace')->__('Amount Remaining'),
            'width'     => '150',
            'filter'     => false,
            'type'  => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'index'     => 'amountremain'
        ));

        $this->addColumn('totalpayamount', array(
            'header'    => Mage::helper('marketplace')->__('Payment done till date'),
            'width'     => '150',
            'filter'     => false,
            'type'  => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'index'     => 'totalpayamount'
        ));

        $this->addColumn('action',
            array(
            'header'    => Mage::helper('marketplace')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getId',
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'renderer'  => 'Cybage_Marketplace_Block_Adminhtml_Widget_Column_Renderer_Paylink',
        ));

        return parent::_prepareColumns();
    }

    /*
     * get grid url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
}
