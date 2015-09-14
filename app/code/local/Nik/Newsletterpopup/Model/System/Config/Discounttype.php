<?php
/*
* @copyright   Copyright ( c ) 2013 www.nik.com
*/

class Nik_Newsletterpopup_Model_System_Config_Discounttype {
	public function toOptionArray() {
		return array(
			Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => Mage::helper('salesrule')->__('Percent of product price discount'),
			Mage_SalesRule_Model_Rule::CART_FIXED_ACTION => Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),	
		);
	}
}