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

class Cybage_Marketplace_Block_Adminhtml_Seller_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('seller_form', array('legend'=>Mage::helper('marketplace')->__('Seller information')));

        $fieldset->addField('firstname', 'label', array(
            'label'     => Mage::helper('marketplace')->__('First Name'),
            'name'      => 'firstname',
        ));

        $fieldset->addField('lastname', 'label', array(
            'label'     => Mage::helper('marketplace')->__('Last Name'),
            'name'      => 'lastname',
        ));

        $fieldset->addField('email', 'label', array(
            'label'     => Mage::helper('marketplace')->__('Email'),
            'name'      => 'email',
        ));

        $status = array();
	
        foreach (Mage::getModel('marketplace/customatributestatus')->toOptionArray() as $key => $value) {
            $status[] = array (
                                'value'     => $key,
                                'label'     => $value,
                              );
        }
	
		/*
		 $status[] = array (
                                'value'     => 7,
                                'label'     => "Approved",
                              );
							  $status[] = array (
                                 'value'     => 8,
                                'label'     => "Pending",
                              );
							  $status[] = array (
                                 'value'     => 9,
                                'label'     => "Rejected",
                              );
							  $status[] = array (
                                'value'     => 10,
                                'label'     => "Deleted",
                              );
				*/			  
	

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('marketplace')->__('Status'),
            'name'      => 'status',
            'values'    => $status
        ));

        $commission_fieldset = $form->addFieldset('seller_form_commission', array('legend'=>Mage::helper('marketplace')->__('Seller Commission')));
        $commission_fieldset->addField('seller_commission', 'text', array(
            'label'     => Mage::helper('marketplace')->__('Commission (%)'),
            'name'      => 'seller_commission',
        ));

        $product_fieldset = $form->addFieldset('seller_form_product', array('legend'=>Mage::helper('marketplace')->__('Seller Product Approval')));
        $product_fieldset->addField('seller_product_state', 'select', array(
            'label'     => Mage::helper('marketplace')->__('New Product State'),
            'name'      => 'seller_product_state',
            'values'    => Mage::getModel('marketplace/seller_attribute_source_product_state')->toOptionArray()
        ));

        $product_fieldset->addField('seller_product_status', 'select', array(
            'label'     => Mage::helper('marketplace')->__('New Product Status'),
            'name'      => 'seller_product_status',
            'values'    => Mage::getModel('marketplace/seller_attribute_source_product_status')->toOptionArray()
        ));

        if ($customer = Mage::registry('current_customer')) {
            $form->setValues($customer->getData());
        }

        return parent::_prepareForm();
    }
}
