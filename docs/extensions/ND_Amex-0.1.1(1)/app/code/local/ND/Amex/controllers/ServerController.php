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

class ND_Amex_ServerController extends ND_Amex_Controller_Abstract
{
    protected $_redirectBlockType = 'amex/server_redirect';
    
    public function responseAction()
    {
        $responseParams = $this->getRequest()->getParams();   
        //echo '<pre>';print_r($responseParams);die;
        /*$this->loadLayout();  
        $this->renderLayout();*/        
        if($responseParams['vpc_TxnResponseCode']=='7')
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('core')->__($responseParams['vpc_Message']));
            $this->_redirect('checkout/cart');
            return;
        }
        elseif($responseParams['vpc_TxnResponseCode']=='0')
        {            
            Mage::getModel('migsvpc/server')->afterSuccessOrder($responseParams);
            $cart = Mage::getSingleton('checkout/cart');
            $cart->truncate();
            $cart->save();
            $cart->getItems()->clear()->save();
            //Mage::getSingleton('core/session')->addSuccess(Mage::helper('core')->__($responseParams['vpc_Message']));
            $this->_redirect('checkout/onepage/success');
            return;
        }
        else
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('core')->__($responseParams['vpc_Message']));
            $this->_redirect('checkout/cart');
            return;
        }
    }
}
