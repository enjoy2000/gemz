<?php
$installer = $this;
$installer->startSetup();
$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$attributeCode = SSTech_Profileimage_Model_Config::Profileimage_ATTR_CODE;

$setup->addAttribute(
        'customer', 
        $attributeCode, 
        array(
                'type'              => 'varchar',
                'input'             => 'file',
                'label'             => 'Upload Profileimage',
                'global'            => 1,
                'visible'           => 1,
                'required'          => 0,
                'user_defined'      => 1,
                'visible_on_front'  => 1,
                'source'            => 'profile/entity_profileimage',
         )
);

if (version_compare(Mage::getVersion(), '1.6.0', '<='))
{
      $customer = Mage::getModel('customer/customer');
      $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
      $setup->addAttributeToSet('customer', $attrSetId, 'General', $attributeCode);
}
if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
    Mage::getSingleton('eav/config')
    ->getAttribute('customer', $attributeCode)
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
