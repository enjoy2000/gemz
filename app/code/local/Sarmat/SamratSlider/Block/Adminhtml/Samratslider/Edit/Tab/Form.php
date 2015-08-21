<?php
class Sarmat_SamratSlider_Block_Adminhtml_Samratslider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("samratslider_form", array("legend"=>Mage::helper("samratslider")->__("Item information")));

				
						$fieldset->addField("name", "text", array(
						"label" => Mage::helper("samratslider")->__("Image Name"),
						"name" => "name",
						));
					
						$fieldset->addField("content", "textarea", array(
						"label" => Mage::helper("samratslider")->__("Slider Content"),
						"name" => "content",
						));
									
						$fieldset->addField('image', 'image', array(
						'label' => Mage::helper('samratslider')->__('Upload Image'),
						'name' => 'image',
						'note' => '(*.jpg, *.png, *.gif)',
						));

				if (Mage::getSingleton("adminhtml/session")->getSamratsliderData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getSamratsliderData());
					Mage::getSingleton("adminhtml/session")->setSamratsliderData(null);
				} 
				elseif(Mage::registry("samratslider_data")) {
				    $form->setValues(Mage::registry("samratslider_data")->getData());
				}
				return parent::_prepareForm();
		}
}
