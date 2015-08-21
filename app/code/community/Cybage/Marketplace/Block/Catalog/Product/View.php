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

class Cybage_Marketplace_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    /*
    * @param sellerId (int)
    * @return (String)Seller Name
    */
    public function getSellerCompany($productSellerId)
    {
        $seller = $this->getSellerobject()->load($productSellerId);
        return $seller->getCompanyName();
    }

    /*
    * @param sellerId (int)
    * @return (String)Seller Name
    */
    public function getSellerName($sellerId)
    {
        $seller = $this->getSellerobject()->load($sellerId);
        return $seller->getFirstname()." ".$seller->getLastname();
    }

    /*
    * @created  09-Oct-2013
    * @modified 10-Oct-2013
    */
    public function getSellerobject()
    {
        return mage::getModel('customer/customer');
    }
}
