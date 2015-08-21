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
$this->addAttribute('customer', 'company_banner', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Company Banner',
    'global' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'visible_on_front' => true,
    'note' => 'Upload 700px X 100px for better look'
));
$this->addAttribute('customer', 'company_logo', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Company logo',
    'global' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'visible_on_front' => true,
    'note' => 'Upload 180px X 180px for better look'
));
$this->addAttribute('customer', 'company_locality', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Company Locality',
    'global' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'visible_on_front' => true,
));
$this->addAttribute('customer', 'company_name', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Company Name',
    'global' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'visible_on_front' => true,
));
$this->addAttribute('customer', 'company_description', array(
    'type' => 'text',
    'input' => 'text',
    'label' => 'Company Description',
    'global' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'visible_on_front' => true,
    'is_wysiwyg_enabled'=>true
));

$this->run("
CREATE TABLE `{$this->getTable('marketplace/question')}` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `question` text NOT NULL,
  `customer_id` int(10) unsigned,
  `product_id` int(10) unsigned,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`entity_id`),
  CONSTRAINT `FK_askquestion_question_customer_id` FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_askquestion_question_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB;

CREATE TABLE `{$this->getTable('marketplace/reply')}` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `reply` text NOT NULL,
  `customer_id` int(10) unsigned,
  `parent_id` int(10) unsigned NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`entity_id`),
  CONSTRAINT `FK_askquestion_reply_customer_id` FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_askquestion_reply_askquestion_question` FOREIGN KEY (`parent_id`) REFERENCES {$this->getTable('marketplace/question')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB;
            
        ");

$this->addAttribute('customer', 'seller_subscriber', array(
    'type' => 'int',
    'input' => 'text',
    'label' => 'Seller Subscriber',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'default' => 0,
    'visible_on_front' => 1,
));
$this->endSetup();