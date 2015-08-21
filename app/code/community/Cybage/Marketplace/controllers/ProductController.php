<?php

/**

 * Cybage Marketplace Plugin

 *

 * NOTICE OF LICENSE

 *

 * This source file is subject to the Open Software License (OSL 3.0)

 * It is available on the World Wide Web at:

 * http://opensource.org/licenses/osl-3.0.php

 * If you are unable to access it on the World Wide Web, please send an email

 * To: Support_Magento@cybage.com.  We will send you a copy of the source file.

 *

 * @category   Marketplace Plugin

 * @package    Cybage_Marketplace

 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India

 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx

 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

 * @author     Cybage Software Pvt. Ltd. <Support_Magento@cybage.com>

 */



class Cybage_Marketplace_ProductController extends Mage_Core_Controller_Front_Action

{

    /**

     *    Create session 

     * */

    protected function _getSession() {

        return Mage::getSingleton('marketplace/session');

    }



    /**

     *    validate Customer Login and redirect previous page 

     * */

    protected function _validateCustomerLogin() {

        $session = Mage::getSingleton('customer/session');

        if (!$session->isLoggedIn()) {

            $session->setAfterAuthUrl(Mage::helper('core/url')->getCurrentUrl());

            $session->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());

            $this->_redirect('customer/account/login/');

            return $this;

        }elseif(!Mage::helper('marketplace')->isMarketplaceActiveSellar()){

            $this->_redirect('customer/account/');

        }

    }



    /**

     *    Add marketplace product

     * */

    public function indexAction() {

        $this->_validateCustomerLogin();

        $this->loadLayout();

        $this->_initLayoutMessages('marketplace/session');

        $this->renderLayout();

    }



    /**

     *    Add marketplace product

     * */

    public function addAction() {

        $this->_validateCustomerLogin();

        $checkCategory = Mage::helper('marketplace')->getCategoryCollection();

        $count = $checkCategory->getSize();

        if ($count == 0) {

            $session = $this->_getSession();

            $session->addError($this->__('Their is no category available, please try after some time.'));

            $this->_redirect('*/*/');

            return;

        }



        $this->loadLayout();



        $this->_initLayoutMessages('marketplace/session');

        $this->renderLayout();

    }



    /**

     *    Edit marketplace product

     * */

    public function editAction() {

        $this->_validateCustomerLogin();

        $session = $this->_getSession();

        $product = Mage::getModel('catalog/product');

        $productId = $this->getRequest()->getParam('id');



        if ($productId) {

            try {

                $product->load($productId);

                if ($product->getMarketplaceState() == Mage::helper('marketplace')->getDeletedOptionValue()) {

                    $session->addError($this->__('Not allow to update product details.'));

                    $this->_redirect('*/product/');

                    return;

                }

            } catch (Exception $e) {

                $product->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE);

                Mage::logException($e);

            }

        }



        /**

         *     Add categories to Form data 

         * */

        $product['categories_ids'] = $product->getCategoryIds();



        $session->setMarketplaceFormData($product->getData());

        $this->loadLayout();

        //  $this->getLayout()->getBlock('marketplace_edit')->setFormAction( Mage::getUrl('*/*/save') );

        $this->_initLayoutMessages('marketplace/session');

        $this->renderLayout();

    }



    /**

     * soft Delete product action

     */

    public function softDeleteAction() {



        $this->_validateCustomerLogin();

        $result = 0;

        $action = 1;

        if ($id = $this->getRequest()->getParam('id')) {



            $deletedOptionValue = Mage::helper('marketplace')->getDeletedOptionValue();

            $session = $this->_getSession();



            try {



                $website = Mage::app()->getStore($this->getRequest()->getParam('store', 0))->getWebsite();

                $stores = $website->getStoreIds();

                foreach ($stores as $storeId) {



                    Mage::getSingleton('catalog/product_action')

                            ->updateAttributes(

                                    array($id), array('status' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,

                                'marketplace_state' => $deletedOptionValue

                                    )

                                    , $storeId);

                }

                $session->addSuccess(Mage::helper('marketplace')->__('The product has been removed.'));

                //logging data for product delete action with success status

                $result = 1;

                $this->saveLoggingAction($action,$result,$id);

                $this->_redirect('*/product/');

            } catch (Exception $e) {

                $session->addError($this->__('Unable to remove record.'));

                //logging data for product delete action with failure status

                $result = 0;

                $this->saveLoggingAction($action,$result,$id);

                $this->_redirect('*/product/');

            }

        }



        return;

    }



    /**

     * Product Image Delete product action

     */

    public function productImageDeleteAction() {



        $this->_validateCustomerLogin();

        $productId = $this->getRequest()->getParam('product_id');

        $imageType = $this->getRequest()->getParam('image_type');

        if ($productId && $imageType) {

            $session = $this->_getSession();

            try {

                $product = Mage::getSingleton('catalog/product');

                $removeGalleryImages = array();

                Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

                $mediaApi = Mage::getModel("catalog/product_attribute_media_api");

                $mediaApiItems = $mediaApi->items($productId);

                foreach ($mediaApiItems as $item) {

                    // $mediaApi->remove($product_id, $item['file']);



                    $itemTypes = $item['types'];

                    if (count($itemTypes)) {

                        foreach ($itemTypes as $itemType) {

                            if ($imageType == $itemType) {

                                $removeGalleryImages[$itemType] = $item['file'];

                            }

                        }

                    }

                }

                foreach ($removeGalleryImages as $image) {

                    $mediaApi->remove($productId, $image);

                    $imagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . $image;

                    if (file_exists($imagePath))

                        @unlink($imagePath);

                }



                $product->save();

                $session->addSuccess(Mage::helper('marketplace')->__('The product Image has been removed.'));

                $this->_redirect("*/product/edit/id/$productId");

            } catch (Exception $e) {

                $session->addError($this->__($e->getMessage()));

                $this->_redirect("*/product/edit/id/$productId");

            }

        }



        return;

    }



    /**

     * save Marketplace product data

     *

     */

    public function saveAction() {

        $result = 0;

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $product = $this->_initProductSave();

        $session = $this->_getSession();



        try {

            $product->validate();

            if ($product->getIsError()) {

                Mage::throwException();

            }

            $product->save();

            if ($product->getIsEditMode()) {

                $action = 2;

                $session->addSuccess($this->__("Product Updated Successfully."));

            } else {

                $action = 3;

                $session->addSuccess($this->__("Product Added Successfully."));

            }

            //logging data for product edit and add action with success status

            $result = 1;

            $this->saveLoggingAction($action, $result, $product->getId());

            $this->_getSession()->setMarketplaceFormData();

            $this->_redirect('*/product/');

        } catch (Exception $e) {

            $result = 0;

            $session->addError($this->__($e->getMessage()));

            $productId = $this->getRequest()->getParam('id');

            if (!$productId) {

                $productId = $product->getId();

            }

            if ($productId) {

                $this->_redirect('*/product/edit/id/' . $productId);

                //logging data for product edit action with failure status 

                $action = 2;

                $this->saveLoggingAction($action, $result, $productId);

                return;

            }

            //logging data for product add action with failure status 

            $sellerId = $product->getSellerId();

            $action = 3;

            $this->saveLoggingAction($action, $result, $this->getRequest()->getParam('id'), $sellerId);

            $this->_redirect('*/product/add/');

            return;

        }

    }



    /**

     * Initialize product before saving

     *

     * @return Mage_Catalog_Model_Product

     */

    protected function _initProductSave() {

        $data = $this->getRequest()->getPost();



        $productData = $data['product'];

        $this->_getSession()->setMarketplaceFormData($productData);

        $qty = $productData['qty'];



        //get stock

        $productStockData = $this->getStockData($qty);



        /**

         * Websites

         */

        if (!isset($productData['website_ids'])) {

            $productData['website_ids'] = array(1);

        }

        $productModel = Mage::getModel('catalog/product');

        $productEdit = "";

        if ((isset($productData['id']) && $productId = $productData['id'])) {

            $productEdit = $productModel->load($productId);

            $productEdit->setIsEditMode(TRUE);

            $product = $this->getDefaultData($productEdit);

        } else {

            // default product data 

            $product = $this->getDefaultData($productModel);

        }



        $product->addData($productData);

        $product->addData($productStockData);

        //  $product->addData($productImageData);

        //get image data

        $product = $this->getUploadedProductImageData($productModel);



        return $product;

    }



    protected function getStockData($qty) {

        $productData = array();

        $productData['stock_data']['is_in_stock'] = 1;

        $productData['stock_data']['stock_id'] = 1;

        $productData['stock_data']['manage_stock'] = 1;



        $productData['stock_data']['use_config_manage_stock'] = 1;



        $productData['stock_data']['use_config_min_sale_qty'] = 1;



        $productData['stock_data']['use_config_max_sale_qty'] = 1;

        $productData['stock_data']['qty'] = $qty;



        return $productData;

    }



    protected function getDefaultData($product) {



        $marketplaceHelper = Mage::helper('marketplace');



        if (!$product->getIsEditMode()) {

            $attributeSetId = $product->getResource()->getEntityType()->getDefaultAttributeSetId();

            $product->setAttributeSetId($attributeSetId);

            $productType = Mage_Catalog_Model_Product_Type::DEFAULT_TYPE;

            $product->setTypeId($productType);

            $storeId = $this->getRequest()->getParam('store', 0);

            $product->setStoreId($storeId);

        }

       

        $customer=Mage::getSingleton('customer/session')->getCustomer();        

        $productState=$customer->getSellerProductState();

        $productStatus=$customer->getSellerProductStatus();        

        

        $newProductStatus=$productStatus?$productStatus:$marketplaceHelper->getNewProductStatus();

        $newProductState=$productState?$productState:$marketplaceHelper->getNewProductState();          

        

        $product->setStatus($newProductStatus);

        $product->setMarketplaceState($newProductState);

        

        $product->setVisibility($marketplaceHelper->getNewProductVisibility());        

        $product->setSellerId(Mage::getSingleton('customer/session')->getCustomerId());

        $product->setTaxClassId(1);

        

        return $product;

    }



    protected function getUploadedProductImageData($product) {

        //$product = Mage::getModel('catalog/product')->load($productId);

        $wasLockedMedia = false;

        if ($product->isLockedAttribute('media')) {

            $product->unlockAttribute('media');

            $wasLockedMedia = true;

        }

        $mediaArray = array();

        if ($_FILES) {
            $result = array();
            foreach($_FILES['image'] as $key1 => $value1)
                foreach($value1 as $key2 => $value2)
                    $result[$key2][$key1] = $value2;

            foreach ($result as $key => $value) {

                $fileSize = (int) Mage::helper('marketplace')->getNewProductUploadImageSize('validate');
                //var_dump($value);die;

                if ($value['size'] > $fileSize) {

                    $this->_getSession()->addError(Mage::helper('marketplace')->__('Unable to process your request due to large image size, please try again.'));

                    $product->setIsError(true);

                    return $product;

                }

                $imageName = $value['name'];

                $imageTmpName = $value['tmp_name'];



                if (isset($imageName) && (file_exists($imageTmpName))) {

                    try {

                        $uploader = new Varien_File_Uploader($value);

                        $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));

                        $uploader->setAllowRenameFiles(true);

                        $path1 = DS . 'temp' . DS . 'catalog' . DS . 'product';

                        //$path2 = DS . 'catalog' . DS . 'product';

                        $path = Mage::getBaseDir('media') . $path1;

                        $uploader->setFilesDispersion(true);

                        $uploader->save($path, $imageName);

                        $uploadedFile = $path1 . $uploader->getUploadedFileName();

                        $mediaArray[$key] = $uploadedFile;

                    } catch (Exception $e) {

                        $this->_getSession()->addError($e->getMessage());
                        //$this->_getSession()->addError("The selected file can't be uploaded.");

                        //die($e->getMessage());

                    }

                }

            }

        }



        // Remove unset images, add image to gallery if exists

        $importDir = Mage::getBaseDir('media');

        foreach ($mediaArray as $imageType => $fileName) {

            $filePath = $importDir . $fileName;

            if (file_exists($filePath)) {
                try {

                    //$product->addImageToMediaGallery($filePath, $imageType, false);
                    $product->addImageToMediaGallery($filePath,array('image','small_image','thumbnail'),true,false);

                } catch (Exception $e) {

                    $this->_getSession()->addError($e->getMessage());

                    $product->setIsError(true);

                    return $product;

                }

            } else {

                $this->_getSession()->addError("Image file Dose not exists ");

                $product->setIsError(true);

                return $product;

            }

        }

        $product->setIsError(false);

        return $product;

    }

    /**

     * Calling Log Model file for logging product add, edit or delete action

     * @param : $action(edit or add or delete), $result (success or failure),$productId(Mage_Catalog_Product_Model)

     * @return : void

     */

    public function saveLoggingAction($action,$result,$productId,$sellerId=null)

    {

      return Mage::getModel('marketplace/logging')->saveProductLog($action,$result,$productId,$sellerId);

    }



    // Used for product Import

    public function importAction() {

        $this->_validateCustomerLogin();

        $checkCategory = Mage::helper('marketplace')->getCategoryCollection();

        $count = $checkCategory->getSize();

        if ($count == 0) {

            $session = $this->_getSession();

            $session->addError($this->__('Their is no category available, please try after some time.'));

            $this->_redirect('*/*/');

            return;

        }



        $this->loadLayout();

        $this->_initLayoutMessages('marketplace/session');

        $this->renderLayout();

    }



    /* Sample CSV file for Marketplace product import.*/

    public function samplecsvAction() {

        if (Mage::getModel('customer/session')->isLoggedIn()) {

            $fileName = 'productimport.csv';

            $content =  array (

                            'type' => 'filename',

                            'value' => Mage::getBaseDir('media') . DS . 'marketplace' . DS . 'productimport.csv'

                        );

            $this->_prepareDownloadResponse($fileName, $content);

        } else {

            $this->_redirect('customer/account/login');

            return;

        }

    }



    public function importvalidateAction() {

        $this->_validateCustomerLogin();

        $session = $this->_getSession();

        $this->_initLayoutMessages('marketplace/session');

        $data = $this->getRequest()->getPost();

        $time = time();

        

        if ($data) {

            try {

                $marketplaceHelper = Mage::helper('marketplace');

                $customer=Mage::getSingleton('customer/session')->getCustomer();

                $productState=$customer->getSellerProductState();

                $productStatus=$customer->getSellerProductStatus();

                $newProductStatus=$productStatus?$productStatus:$marketplaceHelper->getNewProductStatus();

                $newProductState=$productState?$productState:$marketplaceHelper->getNewProductState();

                $newProductStateValue = Mage::getModel('eav/config')->getAttribute('catalog_product', 'marketplace_state')->getSource()->getOptionText($newProductState);



                $this->loadLayout();

                /** @var $import Mage_ImportExport_Model_Import */

                $import = Mage::getModel('importexport/import');

                $source = $import->setData($data)->uploadSource();



                // Modify CSV file

                $io = new Varien_Io_File();

                $io->streamOpen($source, 'r');

                $io->streamLock(true);

                $newCsvData = array();

                $i=0;



                while ($data = $io->streamReadCsv()) {

                    if ($i == 0) {

                        $data[] = '_attribute_set';

                        $data[] = '_type';

                        $data[] = '_product_websites';

                        $data[] = 'tax_class_id';

                        $data[] = 'visibility';

                        $data[] = 'seller_id';

                        $data[] = 'marketplace_state';

                        $data[] = 'status';

                        $data[] = 'media_gallery';



                        $newCsvData[] = $data;

                    } else {

                        $data[] = 'Default';

                        $data[] = Mage_Catalog_Model_Product_Type::DEFAULT_TYPE;

                        $data[] = 'base';

                        $data[] = 0;

                        $data[] = $marketplaceHelper->getNewProductVisibility();

                        $data[] = $customer->getCompanyName();

                        $data[] = $newProductStateValue;

                        $data[] = $newProductStatus;

                        $data[] = ' ';



                        if ($this->validateSellerCSV($data)) {

                            $newCsvData[] = $data;

                        }                        

                    }



                    $i++;

                }

                $io->close();

                unlink($source);



                $checkPath = Mage::getBaseDir() . DS . 'media' . DS . 'marketplace/'.$customer->getId();

                if(!file_exists($checkPath))

                {  

                    mkdir($checkPath, 0777);                   

                }

                $newSource = $checkPath. DS .'productimport.csv';

                $io = new Varien_File_Csv();

                $io->saveData($newSource, $newCsvData);



                $validationResult = $import->validateSource($newSource);



                if (!$import->getProcessedRowsCount()) {

                    $session->addError($this->__('File does not contain data or duplicate sku. Please upload another one'));

                } else {

                    if (!$validationResult) {

                        if ($import->getProcessedRowsCount() == $import->getInvalidRowsCount()) {

                            $session->addNotice(

                                $this->__('File is totally invalid. Please fix errors and re-upload file')

                            );

                        } elseif ($import->getErrorsCount() >= $import->getErrorsLimit()) {

                            $session->addNotice(

                                $this->__('Errors limit (%d) reached. Please fix errors and re-upload file', $import->getErrorsLimit())

                            );

                        } else {

                            if ($import->isImportAllowed()) {

                                $session->addNotice(

                                    $this->__('Please fix errors and re-upload file or simply press "Import" button to skip rows with errors'),

                                    true

                                );

                            } else {

                                $session->addNotice(

                                    $this->__('File is partially valid, but import is not possible'), false

                                );

                            }

                        }

                        // errors info

                        foreach ($import->getErrors() as $errorCode => $rows) {

                            $error = $errorCode . ' ' . $this->__('in rows:') . ' ' . implode(', ', $rows);

                            $session->addError($error);

                        }

                    } else {

                        if ($import->isImportAllowed()) {

                            $import->importSource();

                            

                            //Process Images

                            $status = $this->_uploadZipImages($time);

                            if($status !== false)

                            {

                                if($status[0] == 'success')

                                {

                                    $returnVal = Mage::getModel('marketplace/image')->process($newCsvData, $customer->getId(), $time);

                                    

                                    if($returnVal === true)

                                    {

                                        $customDir = Mage::getBaseDir() . DS . 'media' . DS . 'marketplace'. DS . $customer->getId(). DS . $time;

                                        self::delTree($customDir);

                                    }

                                    $session->addSuccess( $this->__($status[1]), true );

                                }

                                else

                                {

                                    $session->addError($this->__($status[1]));

                                }

                            }

                            

                            $import->invalidateIndex();

                            $session->addSuccess(

                                $this->__('Import successfully done.'), true

                            );

                            $this->_redirect('*/*/import');

                        } else {

                            $session->addError(

                                $this->__('File is valid, but import is not possible'), false

                            );

                        }

                    }

                    $session->addNotice($import->getNotices());

                    $session->addNotice($this->__('Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d', $import->getProcessedRowsCount(), $import->getProcessedEntitiesCount(), $import->getInvalidRowsCount(), $import->getErrorsCount()));

                }

            } catch (Exception $e) {

                $session->addNotice($this->__('Please fix errors and re-upload file'))->addError($e->getMessage());

            }

        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {

            $session->addError($this->__('File was not uploaded'));

        } else {

            $session->addError($this->__('Data is invalid or file is not uploaded'));

        }



        $this->_redirect('*/*/import');

    }

    

    private function _uploadZipImages($time)

    {

        if($_FILES['import_image']['name']) 

        {

            if($_FILES['import_image']['size'] > Mage::helper('marketplace')->getNewProductUploadImageSize('validate')) 

            {

                $message = array('failure', 'Unable to process request for zip upload due to large size, please try again.');

                return $message;

            }



            $message    = '';

            $customer   = Mage::getSingleton('customer/session')->getCustomer();

            

            $filename   = $_FILES['import_image']['name'];

            $source     = $_FILES['import_image']['tmp_name'];

            

            $name = explode('.', $filename);

            

            $continue = strtolower($name[count($name)-1]) == 'zip' ? true : false;

            if(!$continue) 

            {

                $message = array('failure', 'The file you are trying to upload is not a .zip file. ZIP upload failed. Please try again with correct file.');

                return $message;

            }



            $target = Mage::getBaseDir() . DS . 'media' . DS . 'marketplace'. DS . $customer->getId();

            if(!file_exists($target))

            {  

                mkdir($target, 0777);                   

            }

            

            $dir = $target. DS . $time;

            if(!file_exists($dir))

            {  

                mkdir($dir, 0777);                   

            }

            

            $file = $dir. DS . $time . '.zip'; 

            

            if(move_uploaded_file($source, $file)) 

            {

                $zip = new ZipArchive();

                $x = $zip->open($file);

                if ($x === true) 

                {

                    $zip->extractTo($dir); 

                    $zip->close();

            

                    unlink($file);

                    $this->_validateZip($dir);

                }

                $message = array('success', 'Zip file was uploaded and unpacked.');

            } else {

                $message = array('failure', 'There was a problem with the upload. Please try again.');

            }

            

            return $message;

        }

        

        return false;

    }

    

    private function _validateZip($dir)

    {

        $cnt = 0;

        $invalid = array();

        $larger = array();

        

        if ($handle = opendir($dir)) 

        {

           $fileSize = (int) Mage::helper('marketplace')->getNewProductUploadImageSize('validate');

            while (false !== ($entry = readdir($handle))) 

            {

                if ($entry != "." && $entry != "..") 

                {

                    $entry = $dir . DS . $entry;

                    if(is_file($entry))

                    {

                        $ext = substr(strrchr($entry, "."), 1);

                        if(in_array($ext, array('jpg', 'jpeg', 'gif', 'png')))

                        {

                            if(filesize($entry) > $fileSize)

                            {

                                $larger[] = basename($entry);

                                unlink($entry);

                            }

                        } else {

                            $invalid[] = basename($entry);

                            unlink($entry);

                        }

                    } elseif(is_dir($entry)) {

                        $invalid[] = basename($entry);

                        self::delTree($dir);

                    }

                }

            }

            closedir($handle);

        }

        

        if(!empty($invalid))

        {

            $this->_getSession()->addError($this->__('Below invalid files have been ignored. <br> '.implode("<br>", $invalid)));

        }

        

        if(!empty($larger))

        {   

            $this->_getSession()->addError($this->__('Unable to process request for below files due to large image size, please try again. <br> '.implode("<br>", $larger)));

        }

    }

    

    public static function delTree($dir) 

    { 

        $files = array_diff(scandir($dir), array('.','..')); 

        foreach ($files as $file) { 

          (is_dir("$dir/$file") && !is_link($dir)) ? self::delTree("$dir/$file") : unlink("$dir/$file");

        } 

        return rmdir($dir); 

    } 



    public function validateSellerCSV($data) {

        // validate sku

        $productModel = Mage::getModel('catalog/product')->getIdBySku($data[0]);

        if ($productModel){

            return false;

        } else {

            if ($data[1] != '')

                return true;

            else 

                return false;

        }

    }

}

