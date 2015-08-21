<?php


class Sarmat_SamratSlider_Block_Adminhtml_Samratslider extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_samratslider";
	$this->_blockGroup = "samratslider";
	$this->_headerText = Mage::helper("samratslider")->__("Samratslider Manager");
	$this->_addButtonLabel = Mage::helper("samratslider")->__("Add New Item");
	parent::__construct();
	
	}

}