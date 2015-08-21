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

class Cybage_Marketplace_Block_Product_Askquestion_Link extends Mage_Core_Block_Template
{
    public function _construct() {
        $currentProduct = Mage::registry('current_product');
        $marketplaceHelper = Mage::helper('marketplace');
        $url = '';
        if ($marketplaceHelper->isMarketplaceApprovedProduct($currentProduct) && !$marketplaceHelper->isSelfProduct($currentProduct)) {
            $url = Mage::getBaseUrl() . 'marketplace/productquestion/link/id/' . $currentProduct->getId();
            $this->setData('url', $url);
        }
    }
}
