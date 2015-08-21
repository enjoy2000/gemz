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

class Cybage_Marketplace_Block_Sales_Order_History extends Mage_Sales_Block_Order_History
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('marketplace/sales/order/history.phtml');
        $sellerId=Mage::getSingleton('customer/session')->getId();

        $orderItems = Mage::getResourceModel('sales/order_item_collection')
        ->addFieldToSelect('order_id')
        ->addFieldToFilter('seller_id', $sellerId)
        ->distinct(true);
        $arr_order_for_filter=array();
        foreach ($orderItems->getData() as $orderItem) {
            $arr_order_for_filter[]=$orderItem['order_id'];
        }

        $orders = Mage::getResourceModel('sales/order_collection')
        ->addFieldToSelect('*')

        //->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
        ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
        ->setOrder('created_at', 'desc');

        $dateTo = (Mage::app()->getRequest()->getParam('date_to')!='')?date('Y-m-d H:i:s', (strtotime(Mage::app()->getRequest()->getParam('date_to') . "+1 day")-1)) : '';
        $dateFrom = (Mage::app()->getRequest()->getParam('date_from')!=="")? date('Y-m-d H:i:s', strtotime(Mage::app()->getRequest()->getParam('date_from'))): '' ;
        $orderStatus = Mage::app()->getRequest()->getParam('orderstatus');
        
        if($dateFrom!='')
        {
            $orders->addFieldToFilter('created_at', array('from' => "$dateFrom"));
            Mage::getSingleton('core/session')->setDateFrom(Mage::app()->getRequest()->getParam('date_from'));
        }
        else
        {
            Mage::getSingleton('core/session')->setDateFrom('');
        }
        if($dateTo!='')
        {
          Mage::getSingleton('core/session')->setDateTo(Mage::app()->getRequest()->getParam('date_to'));
            $orders->addFieldToFilter('created_at', array('to' => "$dateTo"));
        }
        else
        {
         Mage::getSingleton('core/session')->setDateTo('');
        }

        if ($orderStatus != '') {
            Mage::getSingleton('core/session')->setSalesOrderSatus(Mage::app()->getRequest()->getParam('orderstatus'));
            $orders->addFieldToFilter('status', "$orderStatus");
        }else
        {
            Mage::getSingleton('core/session')->setSalesOrderSatus('');
        }

        $orders->addFieldToFilter('entity_id',array('in'=>$arr_order_for_filter));
        $this->setOrders($orders);

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.history.pager')
            ->setCollection($this->getOrders());
        $this->setChild('pager', $pager);
        $this->getOrders()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($order)
    {
        return $this->getUrl('*/account/vieworder', array('order_id' => $order->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
