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



class Cybage_Marketplace_Block_Adminhtml_Seller_Grid extends Mage_Adminhtml_Block_Widget_Grid

{

    public function __construct() {

        parent::__construct();

        $this->setId('sellerGrid');

        $this->setUseAjax(true);

        $this->setDefaultSort('entity_id');

        $this->setSaveParametersInSession(true);

    }



    protected function _prepareCollection() {

        $collection = Mage::getResourceModel('customer/customer_collection')

            ->addNameToSelect()

            ->addAttributeToSelect('status')

            ->addAttributeToFilter('seller_subscriber', 1);



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



        $this->addColumn('action',

            array(

            'header'    => Mage::helper('marketplace')->__('Action'),

            'width'     => '50px',

            'type'      => 'action',

            'getter'     => 'getId',

            'actions'   => array(

                array(

                'caption' => Mage::helper('marketplace')->__('Edit'),

                'url'     => array(

                    'base'=>'*/*/edit',

                    'params'=>array('store'=>$this->getParam('store',''))

                ),

                    'field'   => 'id'

                )

                ),

                'filter'    => false,

                'sortable'  => false,

                'index'     => 'stores',

        ));



        return parent::_prepareColumns();

    }



    protected function _prepareMassaction()

    {

        $this->setMassactionIdField('id');

        $this->getMassactionBlock()->setFormFieldName('id');



        $this->getMassactionBlock()->addItem('delete', array(

            'label' => Mage::helper('marketplace')->__('Delete'),

            'url' => $this->getUrl('*/*/massDelete'),

            'confirm' => Mage::helper('marketplace')->__('Are you sure you want to delete selected records?')

        ));



        $this->getMassactionBlock()->addItem('approve', array(

            'label' => Mage::helper('marketplace')->__('Approve'),

            'url' => $this->getUrl('*/*/massApprove'),

            'confirm' => Mage::helper('marketplace')->__('Are you sure you want to approve selected records?')

        ));



        $this->getMassactionBlock()->addItem('reject', array(

            'label' => Mage::helper('marketplace')->__('Reject'),

            'url' => $this->getUrl('*/*/massReject'),

            'confirm' => Mage::helper('marketplace')->__('Are you sure you want to reject selected records?')

        ));



        return $this;

    }



    /*

     * get grid url

     */

    public function getGridUrl()

    {

        return $this->getUrl('*/*/grid', array('_current'=>true));

    }



    public function getRowUrl($row)

    {

        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));

    }

}

