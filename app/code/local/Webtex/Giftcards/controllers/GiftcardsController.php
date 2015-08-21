<?php

class Webtex_Giftcards_GiftcardsController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->_redirect('*/*/balance');
    }

    public function balanceAction()
    {
        if (!Mage::helper('customer')->isLoggedIn()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function printAction()
    {
        if (($cardCode = $this->getRequest()->getParam('code'))) {
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            $this->_redirect('/');
        }
    }
}
