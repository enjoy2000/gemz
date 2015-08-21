<?php
$installer = $this;
$installer->startSetup();

$attributeId = $installer->getAttribute('catalog_product', 'wts_gc_type', 'attribute_id');
if(!$attributeId) {
    $installer->addAttribute('catalog_product', 'wts_gc_type', array(
        'group' => 'General',
        'sort_order' => 100,
        'backend' => '',
        'type' => 'varchar',
        'input' => 'select',
        'option' => array('value' => array('email' => array('email'),
                                       'print' => array('print'),
                                       'offline' => array('offline'),)),
        'label' => 'Gift Card Type',
        'required' =>true,
        'visible' =>true,
        'visible_on_front' => false,
        'apply_to' => Webtex_Giftcards_Model_Product_Type::TYPE_GIFTCARDS_PRODUCT
    ));
}

$this->endSetup();