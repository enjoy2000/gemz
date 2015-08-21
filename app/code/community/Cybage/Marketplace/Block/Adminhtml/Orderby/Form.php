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

class Cybage_Marketplace_Block_Adminhtml_Orderby_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Report field visibility
     */
    protected $_fieldVisibility = array();

    /**
     * Report field opions
     */
    protected $_fieldOptions = array();

    /**
     * Set field visibility
     *
     * @param string Field id
     * @param bool Field visibility
     */
    public function setFieldVisibility($fieldId, $visibility) {
        $this->_fieldVisibility[$fieldId] = (bool) $visibility;
    }

    /**
     * Get field visibility
     *
     * @param string Field id
     * @param bool Default field visibility
     * @return bool
     */
    public function getFieldVisibility($fieldId, $defaultVisibility = true) {
        if (!array_key_exists($fieldId, $this->_fieldVisibility)) {
            return $defaultVisibility;
        }
        return $this->_fieldVisibility[$fieldId];
    }

    /**
     * Set field option(s)
     *
     * @param string $fieldId Field id
     * @param mixed $option Field option name
     * @param mixed $value Field option value
     */
    public function setFieldOption($fieldId, $option, $value = null) {
        if (is_array($option)) {
            $options = $option;
        } else {
            $options = array($option => $value);
        }
        if (!array_key_exists($fieldId, $this->_fieldOptions)) {
            $this->_fieldOptions[$fieldId] = array();
        }
        foreach ($options as $k => $v) {
            $this->_fieldOptions[$fieldId][$k] = $v;
        }
    }

    /**
     * Add fieldset with general report fields
     *
     * @return Cybage_Marketplace_Block_Adminhtml_Orderby_Form
     */
    protected function _prepareForm() {
        $actionUrl = $this->getUrl('*/*/');
        $form = new Varien_Data_Form(
                        array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('marketplace')->__('Filter')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('store_ids', 'hidden', array(
            'name' => 'store_ids'
        ));

        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('company_name')
            ->addAttributeToSelect('status')
            ->addAttributeToFilter('status', array('eq'=>Mage::getStoreConfig('marketplace/status/approved')));

        $values[0] = 'All';
        foreach ($collection as $data) {
            $values[$data->getId()] = $data->getCompanyName();
        }

        $fieldset->addField('marketplace_user', 'select', array(
            'name' => 'marketplace_user',
            'options' => $values,
            'label' => Mage::helper('marketplace')->__('Marketplace Seller'),
            'title' => Mage::helper('marketplace')->__('Marketplace Seller')
        ));

        $fieldset->addField('period_type', 'select', array(
            'name' => 'period_type',
            'options' => array(
                'week' => Mage::helper('marketplace')->__('Weekly'),
                'month' => Mage::helper('marketplace')->__('Monthly'),
                'quarter' => Mage::helper('marketplace')->__('Quarterly')
            ),
            'label' => Mage::helper('marketplace')->__('Period'),
            'title' => Mage::helper('marketplace')->__('Period')
        ));

        $fieldset->addField('from', 'date', array(
            'name' => 'from',
            'format' => $dateFormatIso,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => Mage::helper('marketplace')->__('From'),
            'title' => Mage::helper('marketplace')->__('From'),
            'required' => true
        ));

        $fieldset->addField('to', 'date', array(
            'name' => 'to',
            'format' => $dateFormatIso,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => Mage::helper('marketplace')->__('To'),
            'title' => Mage::helper('marketplace')->__('To'),
            'required' => true
        ));

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {

            $statuses = Mage::getModel('sales/order_config')->getStatuses();
            $values = array();

            foreach ($statuses as $code => $label) {
                //if (false === strpos($code, 'pending')) {
                if (in_array($code, array('canceled', 'complete', 'processing'))) {
                    $values[] = array(
                        'label' => Mage::helper('marketplace')->__($label),
                        'value' => $code
                    );
                }
            }

            $fieldset->addField('show_order_statuses', 'select', array(
                'name' => 'show_order_statuses',
                'label' => Mage::helper('marketplace')->__('Order Status'),
                'options' => array(
                    '0' => Mage::helper('marketplace')->__('Any'),
                    '1' => Mage::helper('marketplace')->__('Specified'),
                ),
                'note' => Mage::helper('marketplace')->__('Applies to Any of the Specified Order Statuses'),
                    ), 'to');

            $fieldset->addField('order_statuses', 'multiselect', array(
                'name' => 'order_statuses',
                'values' => $values,
                'display' => 'none'
                    ), 'show_order_statuses');

            // define field dependencies
            if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
                $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                                ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                                ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                                ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
                );
            }
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _initFormValues() {
        $this->getForm()->addValues($this->getFilterData()->getData());
        return parent::_initFormValues();
    }

    /**
     * This method is called before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _beforeToHtml() {
        $result = parent::_beforeToHtml();

        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
            // apply field visibility
            foreach ($fieldset->getElements() as $field) {
                if (!$this->getFieldVisibility($field->getId())) {
                    $fieldset->removeField($field->getId());
                }
            }
            // apply field options
            foreach ($this->_fieldOptions as $fieldId => $fieldOptions) {
                $field = $fieldset->getElements()->searchById($fieldId);
                /** @var Varien_Object $field */
                if ($field) {
                    foreach ($fieldOptions as $k => $v) {
                        $field->setDataUsingMethod($k, $v);
                    }
                }
            }
        }

        return $result;
    }
}
