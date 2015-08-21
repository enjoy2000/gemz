<?php
class Ifuturz_Productcomment_Block_Productcomment extends Mage_Core_Block_Template
{
	public function _prepareLayout()
	{
		return parent::_prepareLayout();	
	}
	
	public function getProductcomment()
	{
		if(!$this->hasData('productcomment'))
		{
			$this->setData('productcomment',Mage::registry('productcomment'));
		}
		return $this->getData('productcomment');
	}
}