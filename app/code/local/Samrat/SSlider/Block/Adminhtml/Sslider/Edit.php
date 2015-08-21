<?php
	
class Samrat_SSlider_Block_Adminhtml_Sslider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "sslider";
				$this->_controller = "adminhtml_sslider";
				$this->_updateButton("save", "label", Mage::helper("sslider")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("sslider")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("sslider")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("sslider_data") && Mage::registry("sslider_data")->getId() ){

				    return Mage::helper("sslider")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("sslider_data")->getId()));

				} 
				else{

				     return Mage::helper("sslider")->__("Add Item");

				}
		}
}