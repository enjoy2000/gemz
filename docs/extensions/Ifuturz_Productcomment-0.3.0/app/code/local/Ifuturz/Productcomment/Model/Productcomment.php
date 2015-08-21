<?php
class Ifuturz_Productcomment_Model_Productcomment extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('productcomment/productcomment');
	}
   
}