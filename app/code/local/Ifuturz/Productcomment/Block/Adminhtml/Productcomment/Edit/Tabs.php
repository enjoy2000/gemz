<?php
class Ifuturz_Productcomment_Block_Adminhtml_Productcomment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('productcomment_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('productcomment')->__('Productcomment Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('productcomment')->__('Productcomment Information'),
          'title'     => Mage::helper('productcomment')->__('Productcomment Information'),
          'content'   => $this->getLayout()->createBlock('productcomment/adminhtml_productcomment_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}