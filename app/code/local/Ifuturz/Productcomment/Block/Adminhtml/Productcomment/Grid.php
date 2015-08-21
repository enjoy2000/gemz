<?php

class Ifuturz_Productcomment_Block_Adminhtml_Productcomment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {      
	  parent::__construct();
      $this->setId('productcommentGrid');
      $this->setDefaultSort('productcomment_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('productcomment/productcomment')->getCollection();
	  //echo "<pre>"; print_r($collection->getData());exit;				
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  { 
      
      $this->addColumn('productcomment_id', array(
          'header'    => Mage::helper('productcomment')->__('ID'),
          'align'     =>'left',
          'index'     => 'productcomment_id',
		  'width'     => '100',
      ));
	  $this->addColumn('product_id', array(
          'header'    => Mage::helper('productcomment')->__('Product ID'),
          'index'     => 'product_id',
		  'width'     => '100',
      ));
	  $this->addColumn('customer_id', array(
          'header'    => Mage::helper('productcomment')->__('Customer ID'),
          'index'     => 'customer_id',
		  'width'     => '100',
      ));
	  $this->addColumn('product_comment', array(
          'header'    => Mage::helper('productcomment')->__('Product Comments'),
          'index'     => 'product_comment',
      ));
	  $this->addColumn('created_at', array(
          'header'    => Mage::helper('productcomment')->__('Created At'),
		  'type'      => 'datetime',
		  'gmtoffset' => true,
		  'align'     => 'center',
		  'width'     => '150',
          'index'     => 'created_at',
      ));
	  
	 	
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('productcomment_id');
        $this->getMassactionBlock()->setFormFieldName('productcomment');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('productcomment')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('productcomment')->__('Are you sure?')
        ));       
        return $this;
    }

}