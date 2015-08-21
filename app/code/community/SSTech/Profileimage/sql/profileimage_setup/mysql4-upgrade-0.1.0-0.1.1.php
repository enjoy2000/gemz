<?php

$installer = $this;

$installer->startSetup();

$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$customerEntityType = $setup->getEntityTypeId('customer');

$attributeCode = SSTech_Profileimage_Model_Config::Profileimage_ATTR_CODE;
$setup->removeAttribute($customerEntityType, $attributeCode); 

$setup->addAttribute(
        $customerEntityType, 
        $attributeCode, 
        array(
                'type'              => 'varchar',
                'input'             => 'image',
                'label'             => 'Upload Profileimage',
                'global'            => 1,
                'visible'           => 1,
                'required'          => 0,
                'user_defined'      => 1,
                'visible_on_front'  => 1,
         )
);

if (version_compare(Mage::getVersion(), '1.6.0', '<='))
{
      $customer = Mage::getModel('customer/customer');
      $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
      $setup->addAttributeToSet($customerEntityType, $attrSetId, 'General', $attributeCode);
}
if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
    Mage::getSingleton('eav/config')
    ->getAttribute($customerEntityType, $attributeCode)
    ->setData(
               'used_in_forms', 
                array(
                       'adminhtml_customer',
                       'customer_account_create',
                       'customer_account_edit',
                       'checkout_register'
                )
    )
    ->save();
}
$installer->endSetup(); 
