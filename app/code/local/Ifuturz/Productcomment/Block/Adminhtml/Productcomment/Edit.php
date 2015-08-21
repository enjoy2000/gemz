<?php

class Ifuturz_Productcomment_Block_Adminhtml_Productcomment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'productcomment';
        $this->_controller = 'adminhtml_productcomment';
       
        $this->_updateButton('save', 'label', Mage::helper('productcomment')->__('Save Productcomment'));
        $this->_updateButton('delete', 'label', Mage::helper('productcomment')->__('Delete Productcomment'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('productcomment_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'productcomment_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'productcomment_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('productcomment_data') && Mage::registry('productcomment_data')->getId() ) 
		{
			//$name=Mage::getModel('register')->getCollection()->getCountryName(Mage::registry('register_data')->getCountry());
			return Mage::helper('productcomment')->__("Edit Productcomment '%s'", $this->htmlEscape(Mage::registry('productcomment_data')->getProductcommentName()));
        } 
		else 
		{
            return Mage::helper('productcomment')->__('Add Productcomment');
        }
	}
}