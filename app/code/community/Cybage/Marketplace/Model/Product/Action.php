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

class Cybage_Marketplace_Model_Product_Action extends Mage_Catalog_Model_Product_Action
{
    /**
     * Validate product's status against markeplace status os the same product
     * @param : Mage_Catalog_Product_Model($productStatus, $markeplaceStatus)
     */
    public function validateProductStatus($productStatus, $markeplaceStatus)
    {
        if ( $productStatus == Mage_Catalog_Model_Product_Status::STATUS_ENABLED && !($markeplaceStatus == Mage::helper('marketplace')->getProductApprovedStatusId()) )
        {
            Mage::throwException('Please change marketplace status to approved or make product status as disabled');
        }
    }
}
