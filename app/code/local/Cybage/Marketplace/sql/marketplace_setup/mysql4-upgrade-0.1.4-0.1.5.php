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

$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('marketplace/commission')}` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `seller_id` int(10) unsigned,
  `amount` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_commission_id` FOREIGN KEY (`seller_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE = InnoDB;
");

$this->endSetup();
