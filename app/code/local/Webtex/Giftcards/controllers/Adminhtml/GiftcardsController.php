<?php

class Webtex_Giftcards_Adminhtml_GiftcardsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards');
        $this->_addBreadcrumb($this->__('Gift Cards'), $this->__('Gift Cards'));
        $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_card'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('giftcards/giftcards')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('giftcards_data', $model);
            $this->loadLayout();
            $this->_setActiveMenu('customer/giftcards');
            $this->_addBreadcrumb($this->__('Gift Cards'), $this->__('Gift Cards'));
            $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_card_edit'))
                ->_addLeft($this->getLayout()->createBlock('giftcards/adminhtml_card_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giftcards')->__('Card does not exists'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function importAction()
    {
        $this->_title($this->__('Import Gift Cards from CSV'));

        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards');
        $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_card_load'));
             //->_addLeft($this->getLayout()->createBlock('giftcards/adminhtml_card_load_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('giftcards/giftcards');
            if(strlen($data['card_currency']) != 3){
                $data['card_currency'] = '';
            }
            $model->setData($data);
            $model->setId($this->getRequest()->getParam('id'));
            // set card ready for activate
            // $model->setCardStatus(2);
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift card was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Unable find gift card to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if (($id = $this->getRequest()->getParam('id')) > 0) {
            try {
                Mage::getModel('giftcards/giftcards')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift card was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $cardIds = $this->getRequest()->getParam('card');
        if (!is_array($cardIds)) {
            $this->_getSession()->addError($this->__('Please select gift card(s)'));
        } else {
            try {
                foreach ($cardIds as $cardId) {
                    Mage::getModel('giftcards/giftcards')->load($cardId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d geftcard(s) were successfully deleted', count($cardIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function resendAction()
    {
        try {
            if (($cardId = $this->getRequest()->getParam('id')) > 0) {
                $card = Mage::getModel('giftcards/giftcards')->load($cardId);
                if($card->getCardType() == 'email'){
                    $card->send();
                } else {
                    $this->_getSession()->addError($this->__('Unable to send this Gift Card type.'));
                }
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function massResendAction()
    {
        $cardIds = $this->getRequest()->getParam('card');
        if (!is_array($cardIds)) {
            $this->_getSession()->addError($this->__('Please select gift card(s)'));
        } else {
            try {
                foreach ($cardIds as $cardId) {
                    Mage::getModel('giftcards/giftcards')->load($cardId)->send();
                }
                $this->_getSession()->addSuccess(
                    $this->__('%d giftcard(s) were successfully resent', count($cardIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function printAction()
    {
        if (($cardId = $this->getRequest()->getParam('id')) > 0) {
            echo $this->getLayout()->createBlock('giftcards/adminhtml_print')->toHtml();
        } else {
            $this->_redirect('*/*/');
        }
    }


    public function activateGiftCardAction()
    {
        //Mage::getSingleton('giftcards/session')->clear();exit;

        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

        $result = array();

        if ($card->getId() && ($card->getCardStatus() == 1)) {
            $card->activateCard();

            Mage::getSingleton('adminhtml/session_quote')->addSuccess(
                $this->__('Gift Card "%s" was applied.', Mage::helper('core')->escapeHtml($giftCardCode))
            );
            Mage::getSingleton('giftcards/session')->setActive('1');
            $this->_setSessionVars($card);
            $result['success'] = true;
            $result['card_code'] = $card->getCardCode();

        } else {
            if($card->getId() && ($card->getCardStatus() == 2)) {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            } else {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            }
        }
        die(json_encode($result));
    }


    public function deActivateGiftCardAction()
    {
        $oSession = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $cardIds = $oSession->getGiftCardsIds();
        $sessionBalance = $oSession->getGiftCardBalance();
        $newSessionBalance = $sessionBalance - $cardIds[$cardId]['balance'];
        unset($cardIds[$cardId]);
        if(empty($cardIds))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }
        $oSession->setGiftCardBalance($newSessionBalance);
        $oSession->setGiftCardsIds($cardIds);
        exit;

    }

    private function _setSessionVars($card)
    {
        $oSession = Mage::getSingleton('giftcards/session');

        $giftCardsIds = $oSession->getGiftCardsIds();

        //append applied gift card id to gift card session
        //append applied gift card balance to gift card session
        if (!empty($giftCardsIds)) {
            $giftCardsIds = $oSession->getGiftCardsIds();
            if (!array_key_exists($card->getId(), $giftCardsIds)) {
                $giftCardsIds[$card->getId()] =  array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
                $oSession->setGiftCardsIds($giftCardsIds);

                $newBalance = $oSession->getGiftCardBalance() + $card->getCardBalance();
                $oSession->setGiftCardBalance($newBalance);
            }
        } else {
            $giftCardsIds[$card->getId()] = array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
            $oSession->setGiftCardsIds($giftCardsIds);

            $oSession->setGiftCardBalance($card->getCardBalance());
        }
    }


    public function ajaxUpdateGiftCardBlockAction()
    {
        $oGiftCardSession = Mage::getSingleton('giftcards/session');
        $response = $oGiftCardSession->getFrontOptions();
        die(json_encode($response));
    }

}