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

class Cybage_Marketplace_Block_Adminhtml_Commission_Edit_Tab_Payhistory extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('payhistory');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('marketplace/commission_collection');
        $collection->getSelect()->where('seller_id='.$this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('marketplace')->__('ID'),
            'type'      => 'text',
            'align'     => 'left',
            'index'     => 'id'
        ));

        $this->addColumn('date', array(
            'header'    => Mage::helper('marketplace')->__('Payment Date'),
            'type'      => 'datetime',
            'align'     => 'left',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        $this->addColumn('amount', array(
            'header'    => Mage::helper('marketplace')->__('Amount'),
            'width'     => '350',
            'filter'     => false,
            'type'  => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'index'     => 'amount'
        ));

        return parent::_prepareColumns();
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
}
