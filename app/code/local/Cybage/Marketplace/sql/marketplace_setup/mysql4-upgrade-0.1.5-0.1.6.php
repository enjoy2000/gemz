<?php
/**
 * Cybage Marketplace Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_Magento@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Marketplace Plugin
 * @package    Cybage_Marketplace
 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_Magento@cybage.com>
 */

$installer = $this;
$installer->startSetup();
$installer->run("        
CREATE TABLE `{$installer->getTable('marketplace/buyerseller')}` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned,  
  `customer_id` int(10) unsigned,  
  `product_id` int(10) unsigned,
  `flag` int(10) unsigned,
  `comment` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`entity_id`),
   CONSTRAINT `FK_buyersellercomm_notifications_order_id` FOREIGN KEY (`order_id`) REFERENCES {$this->getTable('sales_flat_order')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_buyersellercomm_notifications_customer_id` FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_buyersellercomm_notifications_product_id` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB;

CREATE TABLE `{$installer->getTable('marketplace/logging')}` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL default '0',  
  `action` enum('SAVE','EDIT','DELETE') NOT NULL default 'SAVE',  
  `customer_ip` varchar(45),
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `result` enum('SUCCESS','FAILURE') NOT NULL default 'FAILURE',
  PRIMARY KEY (`log_id`)
)
ENGINE = InnoDB;"
);

$installer->endSetup();