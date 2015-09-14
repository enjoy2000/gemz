<?php

class Nik_Newsletterpopup_Model_Subscriber extends Mage_Newsletter_Model_Subscriber {
    const XML_PATH_SUCCESS_EMAIL_TEMPLATE_WITH_COUPON       = 'newsletterpopup/coupon_code_setting/email_template';
    const XML_PATH_SUCCESS_EMAIL_TEMPLATE       = 'newsletter/subscription/success_email_template';

	public function sendConfirmationSuccessEmail() {
		if ($this->getImportMode()) {
				return $this;
		}

		if(!Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE)
			 || !Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY)
		) {
				return $this;
		}

		$translate = Mage::getSingleton('core/translate');

		$translate->setTranslateInline(false);

		$email = Mage::getModel('core/email_template');
		if(Mage::helper('newsletterpopup')->isEnabled() && Mage::helper('newsletterpopup')->isAllowCreateCouponCode()) {
			$model = Mage::getModel('newsletterpopup/newsletterpopup');
			$couponcode = $model->createCouponCode();
			$oCoupon = Mage::getModel('salesrule/coupon')->load($couponcode, 'code');
			$sRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
			$discountAmount	= round(floatval($sRule->getDiscountAmount()),2);
			$discountType = $sRule->getSimpleAction();
			if($discountType == 'cart_fixed') {
				$discountAmount = Mage::helper('core')->currency($discountAmount);
			}else{
				$discountAmount = $discountAmount.'%';
			}
			$fromDate = $sRule->getFromDate();
			$toDate = $sRule->getToDate();
			try {
				$email->sendTransactional(
						Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE_WITH_COUPON),
						Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
						$this->getEmail(),
						$this->getName(),
						array('subscriber'=>$this,'coupon_code' => $couponcode,'discount_mount' => $discountAmount,'from_date' => $fromDate,'to_date' => $toDate)
				);
			}catch(Exception $e) {
				Mage::log($e->getMessage(), null, 'subscribe.coupon.log');
			}	
		}else {
			$email->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
                    Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
					$this->getEmail(),
					$this->getName(),
					array('subscriber'=>$this)
			);		
		}
		
		$translate->setTranslateInline(true);

		return $this;
	}
}