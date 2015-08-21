<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'),'product_id', 'int(11) NULL');

$installer->endSetup();

