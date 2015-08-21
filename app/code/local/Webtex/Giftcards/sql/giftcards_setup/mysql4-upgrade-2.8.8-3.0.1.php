<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'), 'card_currency', 'varchar(50) NULL');

$this->endSetup();