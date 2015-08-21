<?php

class Ifuturz_Productcomment_Block_Adminhtml_Productcomment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	public function __construct()
    {
      parent::__construct();
      $this->setTemplate('productcomment/form.phtml');	 
	}
		
    protected function _prepareForm()
    {  
	  $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('productcomment_form', array('legend'=>Mage::helper('productcomment')->__('Productcomment information')));
		
	 
      if ( Mage::getSingleton('adminhtml/session')->getproductcommentData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getproductcommentData());
          Mage::getSingleton('adminhtml/session')->setproductcommentData(null);
      } elseif ( Mage::registry('productcomment_data') ) {
          $form->setValues(Mage::registry('productcomment_data')->getData());
      }
      return parent::_prepareForm();
  }
}