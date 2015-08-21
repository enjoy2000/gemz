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

class Cybage_Marketplace_Model_Reply extends Mage_Core_Model_Abstract
{
    protected function _construct() {
        parent::_construct();
        $this->_init('marketplace/reply');
    }

    public function saveReplies() {
        $object = Mage::getModel('marketplace/reply');
        $object->setReply(strip_tags(Mage::app()->getRequest()->getPost('reply')));
        $object->setParentId(strip_tags(Mage::app()->getRequest()->getPost('parent_id')));
        $object->setCustomerId(Mage::getSingleton('customer/session')->getCustomer()->getId());
        $object->save();
    }

    public function getReplies($qId) {
        return Mage::getModel('marketplace/reply')->getCollection()->addFieldToFilter('parent_id', $qId)->setOrder('created_at', 'desc');
    }
}
