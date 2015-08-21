<?php
/**
 * ND Amex payment gateway
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so you can be sent a copy immediately.
 *
 * Original code copyright (c) 2008 Irubin Consulting Inc. DBA Varien
 *
 * @category ND
 * @package    ND_Amex
 * @copyright  Copyright (c) 2010 ND Amex
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class ND_Amex_Block_Server_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $server = $this->getOrder()->getPayment()->getMethodInstance();
        if(!$server->getFormFields()) {
            Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Some of the information you have provided is incorrect, please do try again. If the problem persists, please call American Express Customer Service Unit on toll free 800 124 2229 within the Kingdom of Saudi Arabia or +9661 474 9035 outside of the Kingdom. Thank you.'));
            $url = Mage::getUrl('checkout/cart');
            Mage::app()->getResponse()->setRedirect($url);
            return;
        }
        $form = new Varien_Data_Form();
        //$form->setAction($server->getAmexServerUrl())
        $form->setId('amex_server_checkout')
            ->setName('amex_server_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);                
        foreach ($server->getFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $form->setAction($server->getAmexTransactionUrl());

        $html = '<html><body>';
        $html.= $this->__('You will be redirected to American Express Pay Page in a few seconds ...');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("amex_server_checkout").submit();</script>';
        $html.= '</body></html>';
        $html = str_replace('<div><input name="form_key" type="hidden" value="'.Mage::getSingleton('core/session')->getFormKey().'" /></div>','',$html);
        
        return $html;
    }
}
