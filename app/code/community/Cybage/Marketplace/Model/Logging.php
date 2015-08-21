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

class Cybage_Marketplace_Model_Logging extends Mage_Core_Model_Abstract
{
    const LOGGING_SUCCESS_ACTION      = 'SUCCESS';
    const LOGGING_FAILURE_ACTION      = 'FAILURE';
    const LOGGING_PRODUCT_DELETE_ACTION  = 'DELETE';
    const LOGGING_PRODUCT_EDIT_ACTION = 'EDIT';
    const LOGGING_PRODUCT_SAVE_ACTION = 'SAVE';

    /**
     * Initialize model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('marketplace/logging');
    }

    /**
     * get resource module for connection with database
     * @return : Mage_Core_Model_Resource
     */
    public function resource()
    {
        return Mage::getSingleton("core/resource");
    }

    /**
     * Get permission for reading data from database
     * @return : Read Object
     */
    public function read()
    {
        return $this->resource()->getConnection('core_read');
    }

    /**
     * Get permission for writing data to database
     * @return : write Object
     */
    public function write()
    {
        return $this->resource()->getConnection('core_write');
    }

    /**
     * Save logging for product add, edit or delete action for marketplace product
     * @param : $actionId (delete,save or edit), $result (success or failure) , $productId (Mage_catalog_Model_Product)
     * @return : void
     */
    public function saveProductLog($actionId, $result, $productId,$prodSellerId=0)
    {
        if ( $productId ){
            $product = Mage::getModel('catalog/product')->load($productId);
            $sellerId = $product->getSellerId();
        }
        else{
            $sellerId = $prodSellerId;
        }
        if ( $sellerId ){
            $customerIp = $_SERVER['REMOTE_ADDR'];
            $logDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
            if ( $result == 1 ){
                $resultText = self::LOGGING_SUCCESS_ACTION;
            }
            else{
                $resultText = self::LOGGING_FAILURE_ACTION;
            }
            if ( $actionId == 1 ){
                $action = self::LOGGING_PRODUCT_DELETE_ACTION;
            }
            else if ( $actionId == 2 ){
                $action = self::LOGGING_PRODUCT_EDIT_ACTION;
            }
            else{
                $action = self::LOGGING_PRODUCT_SAVE_ACTION;
            }
            $logObject = Mage::getModel('marketplace/logging');
            $logObject->setCustomerId($sellerId);
            $logObject->setAction($action);
            $logObject->setCustomerIp($customerIp);
            $logObject->setCreatedAt($logDate);
            $logObject->setResult($resultText);
            $logObject->save();
        }
        return;
    }
}
