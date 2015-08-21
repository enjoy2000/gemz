<?php
/**
 * SSTech Profileimage Module.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Team
 * that is bundled with this package of SSTech Infomatix Pvt. Ltd.
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * SSTech Support does not guarantee correct work of this package
 * on any other Magento edition except Magento COMMUNITY edition.
 * =================================================================
 */
class SSTech_Profileimage_Model_Profileimage extends Mage_Core_Model_Abstract{
    
    protected $_supportedExtensions = array('jpg', 'JPG', 'png', 'PNG', 'gif', 'GIF');
    protected $_file = null;

    public function getProfileimageBasePath() {
        return Mage::getBaseDir('media') . DS . 'customer';
    }

    public function setProfileimageFileData($fileData) {
        $this->_file = $fileData;
    }
    
    public function getProfileimageFileData() {
        return $this->_file;
    }

    public function saveProfileimageFile() {
        
        $uploadedFile = null;
        
        if ($fileData = $this->getProfileimageFileData()) {
            
            $uploader = new Varien_File_Uploader($this->getProfileimageFileData());
            
            $uploader->setFilesDispersion(true);
            $uploader->setFilenamesCaseSensitivity(false);
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowedExtensions($this->_supportedExtensions);
            
            $uploader->save($this->getProfileimageBasePath(), $fileData['name']);
            $uploadedFile = $uploader->getUploadedFileName();
        }
        return $uploadedFile;
    }

}