<?php
class Samrat_SSlider_Block_Adminhtml_Sslider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("sslider_form", array("legend"=>Mage::helper("sslider")->__("Item information")));

				
						$fieldset->addField("name", "text", array(
						"label" => Mage::helper("sslider")->__("Name"),
						"name" => "name",
						));
					
						$fieldset->addField("content", "textarea", array(
						"label" => Mage::helper("sslider")->__("Content"),
						"name" => "content",
						));
					
						$fieldset->addField("url", "text", array(
						"label" => Mage::helper("sslider")->__("URL"),
						"name" => "url",
						));
									
						$fieldset->addField('image', 'image', array(
						'label' => Mage::helper('sslider')->__('Image'),
						'name' => 'image',
						'note' => '(*.jpg, *.png, *.gif)',
						));

				if (Mage::getSingleton("adminhtml/session")->getSsliderData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getSsliderData());
					Mage::getSingleton("adminhtml/session")->setSsliderData(null);
				} 
				elseif(Mage::registry("sslider_data")) {
				    $form->setValues(Mage::registry("sslider_data")->getData());
				}
				return parent::_prepareForm();
		}
}
