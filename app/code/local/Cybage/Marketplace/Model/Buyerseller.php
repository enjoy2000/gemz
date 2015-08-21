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

class Cybage_Marketplace_Model_Buyerseller extends Mage_Core_Model_Abstract 
{
    const XML_PATH_EMAIL_COMMENT_TEMPLATE      = 'sales_email/product_comment';
    const XML_PATH_EMAIL_IDENTITY                = 'sales_email/order/identity';

    public function _construct() {
        parent::_construct();
        $this->_init('marketplace/buyerseller');
    }

    public function resource() {
        return  Mage::getSingleton("core/resource");
    }

    public function read() {
        return $this->resource()->getConnection('core_read');
    }

    public function write() {
        return $this->resource()->getConnection('core_write');
    }

    public function saveComments() {
        $comment = Mage::app()->getRequest()->getPost('question');
        $order_id = Mage::app()->getRequest()->getPost('order_id');
        $customer_id = Mage::app()->getRequest()->getPost('customer_id');
        $product_id = Mage::app()->getRequest()->getPost('product_id');
        $notify = Mage::app()->getRequest()->getPost('notify');
        $flag = "1";
        $object = Mage::getModel('marketplace/buyerseller');
        $object->setComment(strip_tags($comment));
        $object->setOrderId($order_id);
        $object->setCustomerId($customer_id);
        $object->setProductId($product_id);
        $object->setFlag($flag);
        $object->save();

        if($notify) {
            $order = Mage::getModel('sales/order')->load($order_id);
            $incrementId =  $order->getIncrementId();
            $seller_id =  $this->getSellerInfo($order_id);
            $customer = Mage::getModel('customer/customer')->load($seller_id);
            $toName = $customer->getName();
            $to = $customer->getEmail();
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $fromName = $customer->getName();
            $from = $customer->getEmail();

            $productName = Mage::getModel('catalog/product')->load($product_id)->getName();
            $this->notifyEmail($to, $toName, $comment, $incrementId,$productName,$from,$fromName);
        }
    }

    public function replyComments() {
        $comment = Mage::app()->getRequest()->getPost('reply');
        $order_id = Mage::app()->getRequest()->getPost('order_id');
        $customer_id = Mage::app()->getRequest()->getPost('customer_id');
        $product_id = Mage::app()->getRequest()->getPost('product_id');
        $notify = Mage::app()->getRequest()->getPost('notify');
        $flag = "1";

        $object = Mage::getModel('marketplace/buyerseller');
        $object->setComment(strip_tags($comment));
        $object->setOrderId($order_id);
        $object->setCustomerId($customer_id);
        $object->setProductId($product_id);
        $object->setFlag($flag);
        $object->save();
        $this->updateFlag();

        if($notify) {
            $order = Mage::getModel('sales/order')->load($order_id);
            $incrementId =  $order->getIncrementId();
            $buyer =  $this->getBuyerinfo($order_id);
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $fromName = $customer->getName();
            $from = $customer->getEmail();
            $to = $buyer['email'];
            $toName = $buyer['name'];
            $product = Mage::getModel('catalog/product')->load($product_id);
            $productName = $product->getName();
            $this->notifyEmail($to, $toName, $comment, $incrementId,$productName,$from,$fromName);
        }

        return true;
    }

    public function getActiveNotificationForOrder($oid,$pid=null) {
        if($pid) {
            $select =$this->read()->select()->from($this->resource()->getTableName("marketplace_buyersellercomm_notifications"),"count('flag')")
            ->where("order_id=?",$oid)
            ->where("product_id=?",$pid)
            ->where("flag=?","1");
            $result = $this->read()->fetchAll($select);

            foreach ($result as $data) {
                return $data["count('flag')"];
            }
        } else {
            $select = $this->read()->select()->from($this->resource()->getTableName("marketplace_buyersellercomm_notifications"),"count('flag')")
            ->where("order_id=?",$oid)
            ->where("flag=?","1");
            $result = $this->read()->fetchAll($select);

            foreach ($result as $data) {
                return $data["count('flag')"];
            }
        }
    }

    public function getColection() {
        $params = Mage::app()->getRequest()->getParams();
        if(!empty($params['order'])){
         $oid =$params['order'];
        }        
        if(!empty($params['product'])){
        $pid =$params['product'];
        }
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $sellerId=$customerData->getId();
            }
        //Create query to gate prodcut name
         
        $selectProducNameId = $this->read()->select()
                            ->from(array('ea' => $this->resource()->getTableName('eav_attribute')),array('ea.attribute_id'))
                            ->where('ea.attribute_code=?','name')
                            ->join(array('eet' => $this->resource()->getTableName('eav_entity_type')),'ea.entity_type_id = eet.entity_type_id',array())
                            ->where('eet.entity_type_code=?','catalog_product');
        $producNameId = $this->read()->fetchOne($selectProducNameId);
        
        //Create query to gate customer first name ,last name
        $selectCustomerfirstnameId = $this->read()->select()
                            ->from(array('ea' => $this->resource()->getTableName('eav_attribute')),array('ea.attribute_id'))
                            ->where('ea.attribute_code =?','firstname')
                            ->join(array('eet' => $this->resource()->getTableName('eav_entity_type')),'ea.entity_type_id = eet.entity_type_id',array())
                            ->where('eet.entity_type_code=?','customer');
        $customerfirstnameId = $this->read()->fetchOne($selectCustomerfirstnameId);
        
        $selectCustomerLastNameId = $this->read()->select()
                            ->from(array('ea' => $this->resource()->getTableName('eav_attribute')),array('ea.attribute_id'))
                            ->where('ea.attribute_code =?','lastname')
                            ->join(array('eet' => $this->resource()->getTableName('eav_entity_type')),'ea.entity_type_id = eet.entity_type_id',array())
                            ->where('eet.entity_type_code=?','customer');
        $customerLastNameId = $this->read()->fetchOne($selectCustomerLastNameId);

        if(!empty($pid)) {
            $select = $this->read()->select()->from(array('buyer'=>$this->resource()->getTableName('marketplace_buyersellercomm_notifications')),
                                                    array('sales.increment_id','prod.value','comment','created_at','sales.customer_firstname','sales.customer_lastname'))
                                              ->joinLeft(array('sales'=>$this->resource()->getTableName('sales_flat_order')),
                                                            'buyer.order_id = sales.entity_id',array())
                                              ->joinLeft(array('prod'=>$this->resource()->getTableName('catalog_product_entity_varchar')),
                                                                            'buyer.product_id = prod.entity_id',array())
                                               ->where('buyer.product_id=?',$pid)
                                              ->where('buyer.order_id=?',$oid)
                                              ->where('buyer.flag=?','1')
                                              ->where('prod.attribute_id=?',$producNameId)
                                              ->where('prod.store_id=?',Mage::app()->getStore()->getCode());
        } else {
            $select = $this->read()->select()
            ->from(array('buyer'=>$this->resource()->getTableName('marketplace_buyersellercomm_notifications')),
                    array("prod.value","comment","created_at","sales.seller_id","buyer.customer_id",new Zend_Db_Expr("CONCAT_WS(' ', custfirstname.value, custlastname.value) as fullname")))
                    ->where('buyer.order_id=?',$oid)
            ->joinLeft(array('prod'=>$this->resource()->getTableName('catalog_product_entity_varchar')),
                        'buyer.product_id = prod.entity_id',array())
                    //Remove hard coded value for product name and added by query
                ->where('prod.attribute_id=?',$producNameId)
                ->where('prod.store_id=?',Mage::app()->getStore()->getCode())
            ->joinLeft(array('sales'=>$this->resource()->getTableName('sales_flat_order_item')),
                        'buyer.order_id = sales.order_id',array())
                    //Added seller condition
                     ->where('sales.seller_id=?',$sellerId)
            ->joinLeft(array('custfirstname'=>$this->resource()->getTableName('customer_entity_varchar')),
                        'buyer.customer_id = custfirstname.entity_id',array())
                    //Remove hard coded value for customer name and added by query
                ->where('custfirstname.attribute_id=?',$customerfirstnameId)
            ->joinLeft(array('custlastname'=>$this->resource()->getTableName('customer_entity_varchar')),
                        'buyer.customer_id = custlastname.entity_id',array())
                ->where('custlastname.attribute_id=?',$customerLastNameId);
        }

        $result = $this->read()->fetchAll($select);
        $collection= array(); 
        foreach ($result as $data) {
            $collection[] = array($data);
        }

        return $collection;
    }

    public function updateFlag() {
         $oid = Mage::app()->getRequest()->getPost('order_id');
          $pid = Mage::app()->getRequest()->getPost('product_id');
          $sql = "update marketplace_buyersellercomm_notifications set flag = 0 where order_id=$oid and product_id = $pid and flag =1";
          $this->write()->query($sql);
          return true;
    }

    public function getTotalNotificationCount() {
        $sellerId = Mage::getSingleton('customer/session')->getId();

        $orderItems = Mage::getResourceModel('sales/order_item_collection')
                    ->addFieldToSelect('order_id')
                    ->addFieldToFilter('seller_id', $sellerId)
                    ->distinct(true);

        $arr_order_for_filter=array();
        foreach ($orderItems->getData() as $orderItem) {
            $arr_order_for_filter[]=$orderItem['order_id'];
        }

        $orderIds = implode(', ',$arr_order_for_filter);
        if($orderIds) {
            $select = $this->read()->select()->from($this->resource()->getTableName("marketplace_buyersellercomm_notifications","count('flag')"))
            ->where("order_id IN (".$orderIds.")")
            ->where("flag=?","1");
            $result = $this->read()->fetchAll($select);

            foreach ($result as $data) {
                return $data["count('flag')"];
            }
        } else {
            return "";
        }
    }

    public function notifyEmail($to, $toName, $comment, $orderId,$productName, $from,$fromName) {
        $storeId = Mage::app()->getStore()->getCode();
        $templateId = '';
        $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_COMMENT_TEMPLATE);

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($to, $toName);
        $emailInfo->addBcc($from);
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'name'     => $toName,
                'comment'  => $comment,
                'product'=>$productName,
                'order_id' => $orderId,
                'seller_email' => $from,
                'seller_name' => $fromName)
        );

        $mailer->send();
        $this->setEmailSent(true);
        return $this;
    }

    public function getSellerInfo($order) {
        $select = $this->read()->select()->distinct(true)->from(array('sales'=>$this->resource()->getTableName('sales_flat_order_item')),
                                                        array('seller_id'))
                                                 ->where('sales.order_id=?',$order);
        $result = $this->read()->fetchAll($select);

        foreach ($result as $data) {
            return $data["seller_id"];
        }
    }

    public function getBuyerinfo($orderid) {
        $select = $this->read()->select()->distinct(true)->from(array('sales'=>$this->resource()->getTableName('sales_flat_order')),
                array('customer_email','customer_firstname','customer_lastname'))
                ->where('sales.entity_id=?',$orderid);
        $result = $this->read()->fetchAll($select);

        foreach ($result as $data) {
            $buyer=  array("name"=>$data["customer_firstname"]." ".$data['customer_lastname'],"email"=>$data["customer_email"]);
            return $buyer;
        }
    }
}
