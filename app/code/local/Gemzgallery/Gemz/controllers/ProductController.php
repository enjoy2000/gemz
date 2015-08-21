<?php
# Controllers are not autoloaded so we will have to do it manually:
require_once 'Cybage/Marketplace/controllers/ProductController.php';
class Gemzgallery_Gemz_ProductController extends Cybage_Marketplace_ProductController
{
    /**
     * Override this method to upload multiple images for product
     * @param $product
     * @return mixed
     */
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
     * Create new action for delete image of product (support multiple images)
     */
    public function deleteImageAction()
    {
        $this->_validateCustomerLogin();

        $productId = (int) $this->getRequest()->getParam('product_id');
        $imageFile = $this->getRequest()->getParam('image_file');

        if ($productId && $imageFile) {
            $session = $this->_getSession();
            $product = Mage::getModel('catalog/product')->load($productId);
            $mediaGallery = $product->getMediaGallery();

            // if there is only one image, we don't allow to delete it
            if (count($mediaGallery['images']) == 1) {
                $session->addError(Mage::helper('marketplace')->__('You have only one image.'));
                return $this->_redirect("*/product/edit/id/$productId");
            }

            // if the image u want to delete is main image, we have to set another one to main image
            if ($imageFile == $product->getImage()) {
                  //if there are images
                if (isset($mediaGallery['images'])){
                    //loop through the images
                    foreach ($mediaGallery['images'] as $image){
                        //set the first image as the base image if it's not the image we want to delete
                        if ($image->getFile() != $imageFile) {
                            Mage::getSingleton('catalog/product_action')->updateAttributes(
                                array($product->getId()),
                                array(
                                    'image' => $image['file'],
                                    'thumbnail' => $image['file'],
                                    'small_image' => $image['file']
                                ),
                                0);
                            //stop
                            break;
                        }
                    }
                }
            }

            try {
                Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

                $mediaApi = Mage::getModel("catalog/product_attribute_media_api");

                $mediaApi->remove($productId, $imageFile);

                $imagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . $imageFile;

                if (file_exists($imagePath)) {
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

    public function changeMainImageAction()
    {
        $this->_validateCustomerLogin();

        $productId = (int) $this->getRequest()->getParam('product_id');
        $imageFile = $this->getRequest()->getParam('image_file');

        if ($productId && $imageFile) {
            $session = $this->_getSession();
            $product = Mage::getModel('catalog/product')->load($productId);

            try {
                Mage::getSingleton('catalog/product_action')->updateAttributes(
                    array($product->getId()),
                    array(
                        'image' => $imageFile,
                        'thumbnail' => $imageFile,
                        'small_image' => $imageFile
                    ),
                    0);
                $product->save();

                $session->addSuccess(Mage::helper('marketplace')->__('The main product image has been changed.'));
                $this->_redirect("*/product/edit/id/$productId");
            } catch (Exception $e) {
                $session->addError($this->__($e->getMessage()));
                $this->_redirect("*/product/edit/id/$productId");
            }
        }

        return;
    }

    public function getStylesAction()
    {
        $listStyle = Mage::helper('gemz')->getListStyles();
        $catIds = $this->getRequest()->getParam('cat_ids');
        $catIds = explode(',', $catIds);
        $result = [];
        foreach ($catIds as $catId) {
            $cat = Mage::getModel('catalog/category')->load((int)$catId);
            $currentCatStyles = $listStyle[$cat->getName()]['style'];
            $result = array_unique(array_merge($result, $currentCatStyles));
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }
}

