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

$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$installer->startSetup(); 
$attribute  = array(
   'type'          => 'int',
   'backend_type'  => 'text',
   'frontend_input' => 'text',
   'is_user_defined' => true,
   'label'         => 'Seller id',
   'visible'       => true,
   'required'      => false,
   'user_defined'  => true,
   'searchable'    => false,
   'filterable'    => true,
   'comparable'    => true,
   'default'       => 0
);
$installer->addAttribute('order_item', 'seller_id', $attribute);
$installer->endSetup();
