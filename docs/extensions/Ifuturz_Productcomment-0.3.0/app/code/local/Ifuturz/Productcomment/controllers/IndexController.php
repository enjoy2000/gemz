<?php
class Ifuturz_Productcomment_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$this->loadLayout()->renderLayout();
	}
	public function addAction()
	{

		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$customerid = $customer['entity_id'];
		$data = $this->getRequest()->getPost();
		
		$productid = $data['productid'];
		$obj = Mage::getModel('catalog/product');
		$product = $obj->load($productid);
		$url = $product->getProductUrl();
		$comment = $data['productcommentbox'];
		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql1 = "INSERT INTO `productcomment` (`product_id`,`customer_id`,`created_at`,`product_comment`) VALUES ('$productid','$customerid',now(),'$comment ')";
		$write->query($sql1);
		$this->_redirectUrl($url);
	}
}