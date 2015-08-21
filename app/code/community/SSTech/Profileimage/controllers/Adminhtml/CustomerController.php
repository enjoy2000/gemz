<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'CustomerController.php';
class SSTech_Profileimage_Adminhtml_CustomerController extends Mage_Adminhtml_CustomerController {
     /*
        @params 
        @author Severtek
        @comments view image in admin under customer 
    */
    public function viewfileAction() {
        $file = null;
        $plain = false;
        if ($this->getRequest()->getParam('file')) {
           
            $file = Mage::helper('core')->urlDecode($this->getRequest()->getParam('file'));
        } else if ($this->getRequest()->getParam('image')) {
            
            $file = Mage::helper('core')->urlDecode($this->getRequest()->getParam('image'));
            $plain = true;
        } else {
            return $this->norouteAction();
        }
        if (strpos($file, 'sstech_profileimage') !== false) {
            $path = Mage::getBaseDir('media') . DS . 'sstech_profileimage' . DS;
        } else {
            $path = Mage::getBaseDir('media') . DS . 'customer';
        }
        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $path));
        $fileName = $ioFile->getCleanPath($path . $file);
        $path = $ioFile->getCleanPath($path);

        if ((!$ioFile->fileExists($fileName) || strpos($fileName, $path) !== 0) && !Mage::helper('core/file_storage')->processStorageFile(str_replace('/', DS, $fileName))
        ) {
            return $this->norouteAction();
        }
        if ($plain) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            $ioFile->streamOpen($fileName, 'r');
            $contentLength = $ioFile->streamStat('size');
            $contentModify = $ioFile->streamStat('mtime');

            $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Content-type', $contentType, true)
                    ->setHeader('Content-Length', $contentLength)
                    ->setHeader('Last-Modified', date('r', $contentModify))
                    ->clearBody();
            $this->getResponse()->sendHeaders();

            while (false !== ($buffer = $ioFile->streamRead())) {
                echo $buffer;
            }
        } else {
            $name = pathinfo($fileName, PATHINFO_BASENAME);
            $this->_prepareDownloadResponse($name, array(
                'type' => 'filename',
                'value' => $fileName
            ));
        }
        exit();
    }
}
