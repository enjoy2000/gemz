<?php
class Ifuturz_Productcomment_Block_Adminhtml_Productcomment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
	
    $this->_controller = 'adminhtml_productcomment';
    $this->_blockGroup = 'productcomment';
    $this->_headerText = Mage::helper('productcomment')->__('Productcomment Management');
    $this->_addButtonLabel = Mage::helper('productcomment')->__('Add Productcomment');
    parent::__construct();
	$this->_removeButton('add');
  }
}