<?php
class Ifuturz_Productcomment_Model_Mysql4_Productcomment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('productcomment/productcomment');
	}
	public function getproductcommentArray()
    {
        $options = array();
		$this->addOrder('applicant_group_code', 'asc');
        foreach ($this as $item) {
            $options[] = array(
               'value' => $item->getProductcommentId(),
               'label' => $item->getApplicantGroupCode()
            );
        }
        
        return $options;
    }
	 public function setRealGroupsFilter()
    {
        return $this->addFieldToFilter('productcomment_id', array('gt' => 0));
    }
	public function toOptionArray()
    {
        return parent::_toOptionArray('productcomment_id', 'applicant_group_code');
    }
	 public function toOptionHash()
    {
        return parent::_toOptionHash('productcomment_id', 'applicant_group_code');
    }
	//for applier tooltip module
	public function getAppliergroups()
    {
        $options = array();
		$this->addOrder('applicant_group_code', 'asc');
        foreach ($this as $item) {
            $options[] = array(
               'value' => $item->getProductcommentId(),
               'label' => $item->getApplicantGroupCode()
            );
        }
        if (count($options)>0) {
            array_unshift($options, array('title'=>null, 'value'=>'', 'label'=>Mage::helper('productcomment')->__('-- Please select --')));
        }
        return $options;
    }
}