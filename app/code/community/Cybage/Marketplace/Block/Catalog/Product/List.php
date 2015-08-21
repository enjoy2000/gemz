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

class Cybage_Marketplace_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
    protected $_prodCollection;

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_prodCollection)) {
            $collection = parent::_getProductCollection();

            /*Get customer "status" attribute options*/
            $_approvedStatusId = Mage::getStoreConfig('marketplace/status/approved');

            if($_approvedStatusId) {
                $_customerData = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('status', $_approvedStatusId)->load();
                $where = 'ce.value IS NULL';
                $_sellerIds = array();

                /*Get array of seller ids with stats approved*/
                foreach($_customerData as $_data){
                   $_sellerIds[] = $_data['entity_id'];
                }

                if ($_sellerIds) {
                    $where .= " OR ce.value in (".implode(',', $_sellerIds).")";
                }
                $resource = Mage::getSingleton('core/resource');
                $tableName = $resource->getTableName('catalog_product_entity_int');                
                $collection->getSelect()->joinLeft($tableName.' As ce', 'ce.entity_id=e.entity_id AND ce.attribute_id='.Mage::helper('marketplace')->getProductSellerAttributeId(),null);
                $collection->getSelect()->where($where);
            }            
            $this->_prodCollection = $collection;
        }

        return $this->_prodCollection;
    }
}
