<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/quote'),'use_giftcards','tinyint(1) NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote'),'giftcards_discount','decimal(12,4) NULL');

$installer->endSetup();

