<?php
class SSTech_Profileimage_Model_Observer extends Varien_Object{
    public function saveCustomerProfileimage($observer){
        $fileName = null;
        $customer = $observer->getEvent()->getCustomer();
        if(isset($_FILES['profileimage-file'])){
            $profileimageFile = $_FILES['profileimage-file'];
            $profileimage = Mage::getModel('profileimage/profileimage');
            $profileimage->setProfileimageFileData($profileimageFile);
            try{
                $fileName = $profileimage->saveProfileimageFile();
                $customer->setData(SSTech_Profileimage_Model_Config::Profileimage_ATTR_CODE, $fileName);
            }catch(Exception $e){
                Mage::logException($e);
            }
        }    
        return $this;
    }   
    
}