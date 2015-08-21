<?php
class Sarmat_SamratSlider_Block_Adminhtml_Samratslider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("samratslider_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("samratslider")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("samratslider")->__("Item Information"),
				"title" => Mage::helper("samratslider")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("samratslider/adminhtml_samratslider_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
