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
$this->addAttribute('catalog_category', 'category_marketplace', array(
    'group' => 'General Information',
    'type' => 'varchar',
    'label' => 'Allow Marketplace',
    'input' => 'select',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible' => true,
    'required' => true,
    'user_defined' => false,
    'visible_on_front' => 1,
    'source' => 'eav/entity_attribute_source_boolean',
));

$this->addAttribute('catalog_product', 'seller_id', array(
    'label' => 'Seller',
    'type' => 'int',
    'required' => 0,
    'visible' => true,
    'input' => 'select',
    'source' => 'marketplace/source_option',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$this->addAttribute('catalog_product', 'delivery_time', array(
    'label' => 'Delivery Time',
    'type' => 'text',
    'required' => 0,
    'visible' => true,
    'input' => 'text',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$this->addAttribute('catalog_product', 'shipping_charges', array(
    'label' => 'Shipping Charges',
    'type' => 'text',
    'required' => 0,
    'visible' => true,
    'input' => 'text',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$this->addAttribute('catalog_product', 'marketplace_state', array(
   'label' => 'Marketplace State',
    'type' => 'varchar',
    'required' => 0,
    'visible' => true,
    'input' => 'select',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,    
    'option' => array(
        'value' => array(
            'approved' => array(0 => 'Approved'),
            'pending' => array(0 => 'Pending'),
            'rejected' => array(0 => 'Rejected'),
            'deleted' => array(0 => 'Deleted'),
        )
    ),
));

// Add status for seller
$this->addAttribute('customer', 'status', array(
    'label'        => 'Status',
    'visible'      => true,
    'required'     => false,
    'type'         => 'int',
    'input'        => 'select',
    'source'        => 'eav/entity_attribute_source_table',
));

$tableOptions        = $this->getTable('eav_attribute_option');
$tableOptionValues   = $this->getTable('eav_attribute_option_value');

// add options for level of politeness
$attributeId = (int)$this->getAttribute('customer', 'status', 'attribute_id');
foreach (array('Pending','Approved','Rejected','Deleted') as $sortOrder => $label) {
    // add option
    $data = array(
        'attribute_id' => $attributeId,
        'sort_order'   => $sortOrder,
    );
    $this->getConnection()->insert($tableOptions, $data);

    // add option label
    $optionId = (int)$this->getConnection()->lastInsertId($tableOptions, 'option_id');
    $data = array(
        'option_id' => $optionId,
        'store_id'  => 0,
        'value'     => $label,
    );
    $this->getConnection()->insert($tableOptionValues, $data);
}

// Add seller commision attribute
$this->addAttribute('customer', 'seller_commission', array(
    'label'        => 'Seller Commission',
    'visible'      => true,
    'required'     => false,
    'type'         => 'text',
    'input'        => 'text',
));

// Add seller new product state
$this->addAttribute('customer', 'seller_product_state', array(
    'label'        => 'Seller Product State',
    'visible'      => true,
    'required'     => false,
    'type'         => 'int',
    'input'        => 'select',
    'source'        => 'eav/entity_attribute_source_table',
    'option' => array(
        'value' => array(
            'approved' => array(0 => 'Approved'),
            'pending' => array(0 => 'Pending'),
            'rejected' => array(0 => 'Rejected'),
            'deleted' => array(0 => 'Deleted'),
        )
    ),
));

// Add seller new product status
$this->addAttribute('customer', 'seller_product_status', array(
    'label'        => 'Seller Product Status',
    'visible'      => true,
    'required'     => false,
    'type'         => 'int',
    'input'        => 'select',
    'source'        => 'eav/entity_attribute_source_table',
    'option' => array(
        'value' => array(
            'enabled' => array(0 => 'Enabled'),
            'disabled' => array(0 => 'Disabled'),
        )
    ),
));

$this->endSetup();
