<?php

class Webtex_Giftcards_Adminhtml_ProductController extends Mage_Adminhtml_Controller_Action
{
    protected function _initProduct()
    {
        $id = $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($id);
        Mage::unregister('current_product');
        Mage::register('current_product', $product);
    }

    public function pregeneratedAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function deletecardAction()
    {
        $id = $this->getRequest()->getParam('card_id');
        $product = $this->getRequest()->getParam('id');
        $card = Mage::getModel('giftcards/pregenerated')->load($id);
        $card->delete();
        $this->getResponse()->setRedirect($this->getUrl('adminhtml/catalog_product/edit/id/'.$product));
    }

    public function generateAction()
    {
        $count = $this->getRequest()->getParam('count');
        $product = $this->getRequest()->getParam('product_id');
        $cardModel = Mage::getModel('giftcards/pregenerated');
        for($i = 0; $i < $count; $i++){
            $cardModel->setCardId(null);
            $cardModel->setCardCode($this->_getUniqueCardCode());
            $cardModel->setProductId($product);
            $cardModel->setCardStatus(1);
            $cardModel->save();
        }
    }

    private function _getUniqueCardCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $mask = '#####-#####-#####';

        $cardCode = $mask;
        while (strpos($cardCode, '#') !== false) {
            $cardCode = substr_replace($cardCode, $characters[mt_rand(0, strlen($characters)-1)], strpos($cardCode, '#'), 1);
        }
        
        return $cardCode;
    }


    public function importAction()
    {
        try {
            $productId = $this->getRequest()->getParam('id');
            $fileName = $this->getRequest()->getParam('Filename'); 
            $path = Mage::getBaseDir('var').DS.'import'.DS;
            $uploader = new Mage_Core_Model_File_Uploader('file');
            $uploader->setAllowedExtensions(array('csv'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
	    $result = $uploader->save($path, $fileName);

	    $io = new Varien_Io_File();
	    $io->open(array('path' => $path));
	    $io->streamOpen($path.$fileName, 'r');
	    $io->streamLock(true);

	    while($data = $io->streamReadCsv(';', '"')){
		    if($data[0]){
                                $model = Mage::getModel('giftcards/pregenerated')->load($data[0], 'card_code');
                                if($model->getId()){
                                    continue;
                                }
                                $model->setCardCode($data[0]);
                                $model->setCardStatus(1);
                                $model->setProductId($productId);
                                $model->save();
	            } else {
		        continue;
		    }
		}

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}