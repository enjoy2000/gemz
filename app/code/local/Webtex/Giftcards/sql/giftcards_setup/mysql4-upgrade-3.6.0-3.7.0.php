<?php
$installer = $this;
$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('giftcards_pregenerated')} (
    `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_code` text NOT NULL,
    `card_status` smallint NOT NULL,
    `product_id` int(10) NOT NULL,
    PRIMARY KEY  (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");


$attributeId = $installer->getAttribute('catalog_product', 'wts_gc_pregenerate', 'attribute_id');
if(!$attributeId) {
    $installer->addAttribute('catalog_product', 'wts_gc_pregenerate', array(
        'group' => 'General',
        'sort_order' => 150,
        'backend' => '',
        'type' => 'varchar',
        'input' => 'select',
        'option' => array('value' => array('yes' => array('Yes'),
                                       'no' => array('No'),)),
        'label' => 'Use Pre-Generated Codes',
        'required' =>false,
        'visible' =>true,
        'visible_on_front' => false,
        'apply_to' => Webtex_Giftcards_Model_Product_Type::TYPE_GIFTCARDS_PRODUCT
    ));
}

$this->endSetup();
