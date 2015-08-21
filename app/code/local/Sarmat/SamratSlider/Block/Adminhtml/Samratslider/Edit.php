<?php
	
class Sarmat_SamratSlider_Block_Adminhtml_Samratslider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "samratslider";
				$this->_controller = "adminhtml_samratslider";
				$this->_updateButton("save", "label", Mage::helper("samratslider")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("samratslider")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("samratslider")->__("Save And Continue Edit"),
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
				if( Mage::registry("samratslider_data") && Mage::registry("samratslider_data")->getId() ){

				    return Mage::helper("samratslider")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("samratslider_data")->getId()));

				} 
				else{

				     return Mage::helper("samratslider")->__("Add Item");

				}
		}
}