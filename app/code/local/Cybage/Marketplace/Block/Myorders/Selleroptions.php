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

class Cybage_Marketplace_Block_Myorders_Selleroptions extends Mage_Catalog_Block_Product_List_Toolbar
{
    /* public function getPagerHtml()
    {
        $pagerBlock = $this->getLayout()->createBlock('page/html_pager');
 
        if ($pagerBlock instanceof Varien_Object) {
 
           
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());
 
            $pagerBlock->setUseContainer(false)
            ->setShowPerPage(false)
            ->setShowAmounts(false)
            ->setLimitVarName($this->getLimitVarName())
            ->setPageVarName($this->getPageVarName())
            ->setLimit($this->getLimit())
            ->setCollection($this->getCollection());
            return $pagerBlock->toHtml();
        }
        return '';
    }*/

    /* Set order status options in dropdown.*/
    public function selectAction($id) {
        $order = Mage::getModel('sales/order')->load($id);
        
        if ($order->getStatus() == 'pending') {
            return array('processing', 'cancelled' );
        } else if ($order->getStatus() == 'processing') {
            if($order->hasInvoices()){
              return array('processing');
            }else{
               return array('processing', 'cancelled' );
            }
        }
    }
}
