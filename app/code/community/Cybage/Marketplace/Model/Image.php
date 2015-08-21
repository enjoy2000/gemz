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



class Cybage_Marketplace_Model_Image extends Mage_Core_Model_Abstract

{

    public function process(array $csvData, $cid, $imageDir)

    {

        $cnt = count($csvData);

        $importDir = Mage::getBaseDir() . DS . 'media' . DS . 'marketplace' . DS . $cid . DS . $imageDir . DS;



        if($cnt > 1)

        {

            for($i=1; $i<$cnt; $i++)

            {

                try {

                

                    $sku = $csvData[$i][0];



                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);

                    $fileName = trim($csvData[$i][11]);

                    $filePath = $importDir . $fileName;

                    

                    if (file_exists($filePath) && $fileName != '') 

                    {

                        $types = array('image', 'small_image', 'thumbnail');

                        

                        foreach($types as $type) 

                        {

                            $product->addImageToMediaGallery($filePath, array($type), false);

                        }   

                        

                        $mediaGallery = $product->getMediaGallery();

                        if (isset($mediaGallery['images'])){

                            foreach ($mediaGallery['images'] as $key=>$image){

                                Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array($types[$key] => $image['file']), 0);

                            }

                        }

                       

                        $product->save();

                        

                        unlink($filePath);

                    } else {

                        $message = 'Image does not exist for sku '. $sku;

                        Mage::log($message, null, 'sellerimages_debug.log', true);

                    }    

                } catch(Exception $e) {   

                    Mage::logException($e);

                }            

            }

        }

        

        return true;

    }

}

