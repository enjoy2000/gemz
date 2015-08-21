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

class Cybage_Marketplace_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Front controller looks for collectRoutes() although it is not defined
     * in abstract router class!
     *
     * (non-PHPdoc)
     * @see Mage_Core_Controller_Varien_Router_Standard::collectRoutes()
     */
    public function collectRoutes()
    {
        // nothing to do here
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request) {
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        $sellerAtttributeName = Mage::getConfig()->getNode('default/seller_page/attribute_name');
        $seoDisplay = Mage::getConfig()->getNode('default/seller_page/seo_display');

        if (empty($sellerAtttributeName)) {
            //Seller attribute not configured
            return false;
        }

        $pageId = $request->getPathInfo();
        $param = explode('/', $pageId);
        $seller = '';
        if (count($param) > 1 and strtolower($param[1]) == $seoDisplay and !empty($param[2])) {
            //Identify Seller
          $sellerPage = $param[2];
            if (strpos($sellerPage, '.') !== false) {

            $sellerPage = urldecode(substr($sellerPage,0,-5));
                if($sellerPage) {
                    $seller = str_replace('-',' ', $sellerPage);
                } else {
                    return false;
                }
            } else {
                $seller = $sellerPage;
            }
        } else {
            return false;
        }

        if ($seller) {
            Mage::register('seller_company', $seller);

            $realModule = 'Cybage_Marketplace';
            $request->setModuleName('marketplace');
            $request->setRouteName('marketplace');
            $request->setControllerName('seller');
            $request->setActionName('sellerinfo');
            $request->setControllerModule($realModule);
            $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, ltrim($request->getRequestString(), '/'));
            $file = Mage::getModuleDir('controllers', $realModule) . DS . 'SellerController.php';
            include $file;

            //compatibility with 1.3
            $class = $realModule . '_SellerController';
            $controllerInstance = new $class($request, $this->getFront()->getResponse());
            $request->setDispatched(true);
            $controllerInstance->dispatch('sellerinfo');
        }
        return true;
    }
}
