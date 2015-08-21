<?php
class Samrat_SSlider_Block_Adminhtml_Sslider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("sslider_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("sslider")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("sslider")->__("Item Information"),
				"title" => Mage::helper("sslider")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("sslider/adminhtml_sslider_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
