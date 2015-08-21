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

class Cybage_Marketplace_Model_Resource_Customer extends Mage_Customer_Model_Resource_Customer
{
    /**
     * Check customer scope, email,company name and confirmation key before saving
     */
    protected function _beforeSave(Varien_Object $customer)
    {
        parent::_beforeSave($customer);

        if (!$customer->getEmail()) {
            throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('Customer email is required'));
        }

        $adapter = $this->_getWriteAdapter();
        $bind    = array('email' => $customer->getCompanyName());

        $select = $adapter->select()
            ->from($this->getEntityTable(), array($this->getEntityIdField()))
            ->where('email = :email');

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }
        if ($customer->getId()) {
            $bind['entity_id'] = (int)$customer->getId();
            $select->where('entity_id != :entity_id');
        }

        $result = $adapter->fetchOne($select, $bind);
        if ($result) {
            throw Mage::exception(
                'Mage_Customer', Mage::helper('customer')->__('This customer email already exists'),
                Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS
            );
        }

        /*Get attribute id of attribute company_name*/
        $code = 'company_name';
        $companyAttribute = Mage::getResourceModel('eav/entity_attribute_collection')
                           ->setCodeFilter($code)
                           ->getFirstItem();

        $companyAttrId = $companyAttribute->getId();

        /*Check if same company name already exist*/
        $writeAdapter = $this->_getWriteAdapter();
        $companybind    = array('company_name' => $customer->getCompanyName());
        $resource = Mage::getSingleton("core/resource");
        $compselect = $writeAdapter->select()
            ->from($resource->getTableName('customer_entity_varchar'), array($this->getEntityIdField()))
            ->where('value = :company_name AND attribute_id ='.$companyAttrId);

        if ($customer->getId()) {
            $companybind['entity_id'] = (int)$customer->getId();
            $compselect->where('entity_id != :entity_id');
        }

        $compresult = $writeAdapter->fetchOne($compselect, $companybind);
        if ($compresult) {
            throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('Company name already exist'));
        }
       /*Check if same company name already exist*/
        // set confirmation key logic
        if ($customer->getForceConfirmed()) {
            $customer->setConfirmation(null);
        } elseif (!$customer->getId() && $customer->isConfirmationRequired()) {
            $customer->setConfirmation($customer->getRandomConfirmationKey());
        }
        // remove customer confirmation key from database, if empty
        if (!$customer->getConfirmation()) {
            $customer->setConfirmation(null);
        }

        return $this;
    }
}
