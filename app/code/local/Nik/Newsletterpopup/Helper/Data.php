<?php
/*
* @copyright   Copyright ( c ) 2013 www.nik.com
*/

class Nik_Newsletterpopup_Helper_Data extends Mage_Core_Helper_Abstract {
	const XML_PATH_ENABLED  = 'newsletterpopup/general/active';
    const XML_PATH_NOTIFICATION  = 'newsletterpopup/general/notification';
    const XML_PATH_INCLUDE_JS  = 'newsletterpopup/general/include_js';
	const XML_PATH_TITLE  = 'newsletterpopup/general/form_title';
	const XML_PATH_COOKIES_TIMEOUT  = 'newsletterpopup/general/timeout';
	const XML_PATH_POPUP_DELAY_TIME  = 'newsletterpopup/general/time_delay';
	const XML_PATH_ALLOW_CREAT_COUPON_CODE  = 'newsletterpopup/coupon_code_setting/allow_create';
    const XML_PATH_COUPON_CODE_LENGTH  = 'newsletterpopup/coupon_code_setting/coupon_length';
    const XML_PATH_DISCOUNTAMOUNT  = 'newsletterpopup/coupon_code_setting/discount_amount';
	const XML_PATH_USES_PER_COUPON  = 'newsletterpopup/coupon_code_setting/number_coupon_use';
	const XML_PATH_DISCOUNTTYPE  = 'newsletterpopup/coupon_code_setting/discount_type';
    const XML_PATH_COUPON_EXPIRE  = 'newsletterpopup/coupon_code_setting/coupon_expires';
	
	public function isEnabled() {
		return (int)Mage::getStoreConfig(self::XML_PATH_ENABLED);
	}
	
	public function includeJs() {
		return (int)Mage::getStoreConfig(self::XML_PATH_INCLUDE_JS);
	}
	
	public function popupDelayTime() {
		return (int)Mage::getStoreConfig(self::XML_PATH_POPUP_DELAY_TIME);
	}
	
	public function subscribeFormTitle() {
		return Mage::getStoreConfig(self::XML_PATH_TITLE);
	}
	
	public function timeCookiesTimeout() {
		return (int)Mage::getStoreConfig(self::XML_PATH_COOKIES_TIMEOUT);
	}
	
	public function getNotification() {
		return Mage::getStoreConfig(self::XML_PATH_NOTIFICATION);
	}
	
	public function isAllowCreateCouponCode() {
		return (int)Mage::getStoreConfig(self::XML_PATH_ALLOW_CREAT_COUPON_CODE);
	}
	
	public function getDiscountAmount() {
		return Mage::getStoreConfig(self::XML_PATH_DISCOUNTAMOUNT);
	}
	
	public function getCouponExpireIn() {
		return (int)Mage::getStoreConfig(self::XML_PATH_COUPON_EXPIRE);
	}
	
	public function getUsesPerCoupon() {
		return (int)Mage::getStoreConfig(self::XML_PATH_USES_PER_COUPON);
	}
	
	public function getLengthCouponCode() {
		return (int)Mage::getStoreConfig(self::XML_PATH_COUPON_CODE_LENGTH);
	}
	
	public function getDiscountType() {
		return Mage::getStoreConfig(self::XML_PATH_DISCOUNTTYPE);
	}
	
	public function isHideAfterClose() {
		return (bool) Mage::getStoreConfig('newsletterpopup/general/hide_after_close');
	}
	
	/* Get date when coupon code will be removed */
	public function getDateToRemoveCoupon($date, $days) {
		$new_date = strtotime ( $days.' day' , strtotime ( $date ) ) ;
		$new_date = date ( 'Y-m-d' , $new_date );		
		return $new_date;
	}
}