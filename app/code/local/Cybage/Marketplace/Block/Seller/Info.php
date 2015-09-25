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

class Cybage_Marketplace_Block_Seller_Info extends Mage_Core_Block_Template
{
    protected $_seller;

    /**
     * Function to get the seller info
     * @created  at 08-Oct-2013
     * @modified at 09-Oct-2013
     * @return object
     */
    public function getSellerData()
    {
        $sellerName = urldecode(Mage::registry('seller_company'));
        $seller = Mage::helper('marketplace')->getSellerInfo($sellerName);
        $this->_seller = $seller;
        return $seller;
    }

    public function getProfileImage()
    {
        if (isset($this->_seller['sstech_profileimage']) && $_file_name = $this->_seller['sstech_profileimage']) {
            $_media_dir = Mage::getBaseDir('media') . DS . 'customer' . DS;

            // Here i create a resize folder. for upload new category image
            $cache_dir = $_media_dir . 'resize' . DS;

            if (file_exists($cache_dir . $_file_name)) {
                $img = Mage::getBaseUrl('media') .  'customer' . DS . $_file_name;
            } elseif (file_exists($_media_dir . $_file_name)) {
                if (!is_dir($cache_dir)) {
                    mkdir($cache_dir);
                }

                $_image = new Varien_Image($_media_dir . $_file_name);
                $_image->constrainOnly(false);
                $_image->keepAspectRatio(true);
                $_image->keepFrame(true);
                $_image->keepTransparency(true);
                $_image->backgroundColor(array(255,255,255));
                $_image->resize(300); // change image height, width
                $_image->save($cache_dir . $_file_name);

                $img = Mage::getBaseUrl('media') . 'customer' . DS . 'resize' . DS . $_file_name;

            }
        }

        if (!isset($img)) {
            $img = Mage::getBaseUrl('media') . "default_user.jpg";
        }

        return $img;
    }
}
