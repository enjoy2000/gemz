<?php 
$installer = $this;

$installer->startSetup();


$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('productcomment')};
CREATE TABLE {$this->getTable('productcomment')} (
  `productcomment_id` int(11) unsigned NOT NULL auto_increment,
	`product_id` int(11)  NULL,
	`customer_id` int(11)  NULL,
	`created_at` datetime NULL,
	`product_comment` text,
  PRIMARY KEY (`productcomment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('productcomment_lck')};
CREATE TABLE {$this->getTable('productcomment_lck')} ( 	
	`flag` varchar(4),
	`value` ENUM('0','1')  DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$installer->getTable('productcomment_lck')}` VALUES ('LCK','1');
");


$installer->endSetup(); 