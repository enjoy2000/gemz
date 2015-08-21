<?php
/**
 * Created by JetBrains PhpStorm.
 * User: saa
 * Date: 2/1/13
 * Time: 5:14 PM
 * To change this template use File | Settings | File Templates.
 */

class Webtex_Giftcards_PreviewController extends Mage_Core_Controller_Front_Action
{
    public function previewAction()
    {
        $data = $this->getRequest()->getParams();

        $product = Mage::getModel('catalog/product')->load($data['product']);

        if(Mage::helper('giftcards')->isUseDefaultPicture() || !$product->getId()) {
            $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
        } else {
            if ($product->getId() && $product->getImage() != 'no_selection') {
                $picture = Mage::helper('catalog/image')->init($product, 'image');
            } else {
                $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
            }
        }

        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        if($currencySymbol == '€') {
            $currencySymbol = '&euro;';
        } elseif($currencySymbol == '£') {
            $currencySymbol = '&pound;';
        }

        $storeId = Mage::app()->getStore()->getId();
        $post = array(
            'amount'        => $currencySymbol.$data['price'],
            'code'          => 'XXXX-XXXX-XXXX',
            'email-to'      => $data['mail-to'],
            'email-from'    => $data['mail-from'],
            'link'          => '#',
            'email-message' => $data['mail-message'],
            'store-phone'   => Mage::getStoreConfig('general/store_information/phone'),
            'picture'       => $picture,
        );

        $mailTemplate = Mage::getModel('core/email_template');
        $postObject = new Varien_Object();
        $postObject->setData($post);
        $postObject->setStoreId($storeId);

        if($data['card-type'] == 'email')
        {
            $template = 'giftcards/email/email_template';
        }
        elseif ($data['card-type'] == 'print') {
            $template = 'giftcards/email/print_template';
        }


        $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
            Mage::getStoreConfig($template),
            'general',
            '',
            null,
            array('data' => $postObject)
        );


        //$emailTemplate  = Mage::getModel('core/email_template')->loadDefault('giftcards_email_email_template');
        $mail  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $mail .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2).'" lang="'.substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2).'">';
        $mail .= $mailTemplate->getProcessedTemplate();
        $mail .= '</html>';
        
        echo $mail;exit;
    }
}