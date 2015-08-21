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

class Cybage_Marketplace_Model_Question extends Mage_Core_Model_Abstract
{
    protected function _construct() {
        parent::_construct();
        $this->_init('marketplace/question');
    }

    public function saveQuestions() {
        $object = Mage::getModel('marketplace/question');
        $object->setQuestion(strip_tags(Mage::app()->getRequest()->getPost('question')));
        $object->setProductId(Mage::app()->getRequest()->getPost('product_id'));
        $object->setCustomerId(Mage::getSingleton('customer/session')->getCustomer()->getId());
        $object->save();
        return;
    }

    public function getQuestions($products, $flag) {
        $collection = Mage::getModel('marketplace/question')->getCollection();
        if ($flag) {
            $collection->addFieldToFilter('product_id', array('in' => $products))->setOrder('entity_id', 'DESC');
        } else {
            $collection->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())->setOrder('entity_id', 'DESC');
            if (!empty($products)) {
                $collection->addFieldToFilter('product_id', array('nin' => $products));
            }
        }
        $collection->load();
        return $collection;
    }

    public function getMyQuestions($products, $flag='in') {
        return Mage::getModel('marketplace/question')->getCollection()
                        ->addFieldToFilter('product_id', array($flag => $products))
                        ->setOrder('entity_id', 'DESC');
    }
}
