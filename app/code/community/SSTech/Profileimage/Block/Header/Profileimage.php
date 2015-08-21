<?php
class SSTech_Profileimage_Block_Header_Profileimage extends Mage_Core_Block_Template {
    protected  $_profileimage = null;
    const DEFAULT_WIDTH = 75;
    const DEFAULT_HEIGHT = 75;
    
    public function __construct() {
        parent::__construct();
        $customer = $this->getCustomerFromSession();
        $this->getCustomerFromSession();
       
        if($customer){
            $customerObj = Mage::getModel('customer/customer')->load($customer->getId());
            if($profileimage = $customerObj->getSstechProfileimage()){
                $this->_profileimage = $profileimage; 
            }else{
                $this->_profileimage = null;     
            }
        }
    }
    
    protected function getCustomerFromSession(){
        return Mage::getSingleton('customer/session')->getCustomer();
    }
     /*
        @params 
        @author Severtek
        @comments adjust width
    */
    protected function getWidth(){
        $configWidth = (int)Mage::getStoreConfig(
                          'customer/profileimage_group/profileimage_field_width',
                          Mage::app()->getStore()
                        );
        if($configWidth > 0){
            $width = $configWidth;
        }else{
            $width = self::DEFAULT_WIDTH;
        }
        return $width;
    }
    
    
    public function getProfileimage(){
        return $this->_profileimage;
    }
     /*
        @params 
        @author Severtek
        @comments adjust height
    */
    protected function getHeight(){
        $configHeight = (int)Mage::getStoreConfig(
                           'customer/profileimage_group/profileimage_field_height',
                           Mage::app()->getStore()
                        );
        if($configHeight > 0){
            $height = $configHeight;
        }else{
            $height = self::DEFAULT_HEIGHT;
        }
        
        return $height;
    }
    public function getUploadUrl(){
        return Mage::getUrl('*/customer/upload');
    }
    public function getProfileimagePath(){
        return $this->getUrl('profileimage/customer/viewProfileimage');
    }    
    public function getProfileimageHtml(){
        $html = "<img src='"
                .$this->getProfileimagePath().
                "' width ='".$this->getWidth()
                ."' height='".$this->getHeight()
                ."'/>";
        return $html; 
    }
}
