<?php
class Nik_Newsletterpopup_Model_Observer {
	public function loadSubscribeForm($observer){
		if (Mage::app()->getRequest()->getModuleName() == 'newsletterpopup') {
			die('newsletterpopup');
		}
		else{
			die('adadsada');
		}
	}
}