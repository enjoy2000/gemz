<?php
require_once Mage::getModuleDir('controllers', 'Mage_Newsletter').DS.'SubscriberController.php';
class Nik_Newsletterpopup_SubscriberController extends Mage_Newsletter_SubscriberController {
    public function newAction() {
		parent::newAction();
		$timeCookiesTimeout = Mage::helper('newsletterpopup')->timeCookiesTimeout();
	
		//Set or remove cookie //
		if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
			$email = (string) $this->getRequest()->getPost('email');
			$period = $timeCookiesTimeout*86400;
			Mage::getModel('core/cookie')->set('email_subscribed', $email, $period);
		}
		else{
			if (isset($_COOKIE['email_subscribed']))
				setcookie('email_subscribed',$email,time()-$timeCookiesTimeout*86400,'/');
		}
	}
}
