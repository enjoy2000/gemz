<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'),'hash_data','TEXT');

$this->endSetup();
