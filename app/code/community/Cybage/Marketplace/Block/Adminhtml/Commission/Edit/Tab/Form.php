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

class Cybage_Marketplace_Block_Adminhtml_Commission_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('commission_form', array('legend'=>Mage::helper('marketplace')->__('Seller Payment Information')));

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

        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('status')
            ->addAttributeToFilter('status', array('notnull'=>1))
            ->addAttributeToFilter('entity_id', Mage::registry('current_customer')->getId())
            ->joinTable('sales/order_item',
                'seller_id=entity_id',
                array(
                        'totalsales' => 'IFNULL(SUM(base_row_total), 0)',
                        'amountrecived' => 'IFNULL(SUM(base_row_invoiced), 0)',
                        'amountremain' => 'IFNULL((SUM(base_row_total) - SUM(base_row_invoiced)), 0)',
                     ),
                null,
                'left')
            ->groupByEmail();

        $query = '('.$collection->getSelect()->__toString().')';
        $collection->getSelect()->reset()->from(array('e' => new Zend_Db_Expr($query)));
        $collection->joinTable(array('commission' => 'marketplace/commission'),
                'seller_id=entity_id',
                array(
                        'totalpayamount' => 'IFNULL(SUM(commission.amount), 0)',
                     ),
                null,
                'left');
        $collection->getSelect()->group('e.entity_id');

        $totalsales = 0;
        $amountrecived = 0;
        $amountremain = 0;
        $totalpayamount = 0;
        
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $totalsales = $item->getTotalsales();
                $amountrecived = $item->getAmountrecived();
                $amountremain = $item->getAmountremain();
                $totalpayamount = $item->getTotalpayamount();
            }
        }

        $fieldset->addField('totalsales', 'label', array(
            'label'     => Mage::helper('marketplace')->__('Total Sales'),
            'name'      => 'totalsales',
        ));

        $fieldset->addField('labelamountrecived', 'label', array(
            'label'     => Mage::helper('marketplace')->__('Amount Received'),
            'name'      => 'amountrecived',
        ));

        $fieldset->addField('amountrecived', 'hidden', array(
            'name' => 'amountrecived',
        ));

        $fieldset->addField('amountremain', 'label', array(
            'label'     => Mage::helper('marketplace')->__('Amount Remaining'),
            'name'      => 'amountremain',
        ));

        $fieldset->addField('labeltotalpayamount', 'label', array(
            'label'     => Mage::helper('marketplace')->__('Payment done till date'),
            'name'      => 'totalpayamount',
        ));

        $fieldset->addField('totalpayamount', 'hidden', array(
            'name' => 'totalpayamount',
        ));

        $fieldset->addField('amount', 'text', array(
            'label'     => Mage::helper('marketplace')->__('Pay'),
            'name'      => 'amount',
            'class' => 'validate-greater-than-zero',
            'note'  => Mage::helper('marketplace')->__('Pay = (Amount Recived - Total Payamount)'),
        ));

        if ($customer = Mage::registry('current_customer')) {
            $customer->setTotalsales($totalsales);
            $customer->setAmountrecived($amountrecived);
            $customer->setLabelamountrecived($amountrecived);
            $customer->setAmountremain($amountremain);
            $customer->setTotalpayamount($totalpayamount);
            $customer->setLabeltotalpayamount($totalpayamount);
            $form->setValues($customer->getData());
        }

        return parent::_prepareForm();
    }
}
