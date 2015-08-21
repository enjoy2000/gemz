<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'),'mail_delivery_date','DATE NULL');

$this->endSetup();