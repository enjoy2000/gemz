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

class Cybage_Marketplace_Block_Order_Selleroptions extends Mage_Core_Block_Template 
{
    public function __construct()
    {
        parent::__construct();
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $collection = Mage::getModel('marketplace/selleroptions')->getCommentCollction($orderId);
        $this->setCollection($collection);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all'));		
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

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

    /*Hide status dropdown if all items for specific seller are shipped*/
    public function getItemsShipmentStatus(){
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $_orderedItems = Mage::getSingleton('marketplace/order')->getOrderDetails($orderId);
        $totalQtyOrdered = $qtyShipped = 0;

        foreach ($_orderedItems as $_orderedItem) {
            $qtyShipped += (float)$_orderedItem->getQtyShipped();
            $totalQtyOrdered += (float)$_orderedItem->getQtyOrdered();
        }

        if($qtyShipped == $totalQtyOrdered){
           return false;
        }else{
          return true;
        } 
    }
}
