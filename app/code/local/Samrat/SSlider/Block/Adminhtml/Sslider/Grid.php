<?php

class Samrat_SSlider_Block_Adminhtml_Sslider_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("ssliderGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("sslider/sslider")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("sslider")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("name", array(
				"header" => Mage::helper("sslider")->__("Name"),
				"index" => "name",
				));
				$this->addColumn("url", array(
				"header" => Mage::helper("sslider")->__("URL"),
				"index" => "url",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_sslider', array(
					 'label'=> Mage::helper('sslider')->__('Remove Sslider'),
					 'url'  => $this->getUrl('*/adminhtml_sslider/massRemove'),
					 'confirm' => Mage::helper('sslider')->__('Are you sure?')
				));
			return $this;
		}
			

}