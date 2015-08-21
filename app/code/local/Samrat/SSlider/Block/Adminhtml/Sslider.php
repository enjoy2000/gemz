<?php


class Samrat_SSlider_Block_Adminhtml_Sslider extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_sslider";
	$this->_blockGroup = "sslider";
	$this->_headerText = Mage::helper("sslider")->__("Sslider Manager");
	$this->_addButtonLabel = Mage::helper("sslider")->__("Add New Item");
	parent::__construct();
	
	}

}