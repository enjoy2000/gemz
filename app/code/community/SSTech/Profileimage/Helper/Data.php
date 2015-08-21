<?php
class SSTech_Profileimage_Helper_Data extends Mage_Core_Helper_Abstract {
    const XML_PATH_UPLOAD_WIDGET_ENABLED = 'customer/profileimage_widget/enabled';
    public function isShowUploadWidget(){
        return Mage::app()->getStore()->getConfig(self::XML_PATH_UPLOAD_WIDGET_ENABLED);
    }
    public function _getProfileimage(){
        return Mage::getSingleton('profileimage/profileimage');
    }
}

?>
