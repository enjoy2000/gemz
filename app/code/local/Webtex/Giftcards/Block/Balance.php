<?php
class Webtex_Giftcards_Block_Balance extends Mage_Core_Block_Template
{
    public function getCurrentBalance()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        if($giftCardCode) {
            $oCard = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

            if($oCard->getId() > 0) {
                $aResult['card_code'] = $oCard->getCardCode();

                $aResult['card_balance'] = $this->_convertBalanceToCurrency($oCard);
            } else {
                $aResult['card_code'] = $giftCardCode;
                $aResult['card_balance'] = 0;
            }

            return $aResult;
        }

        return false;
        //return Mage::helper('giftcards')->getCustomerBalance(Mage::getSingleton('customer/session')->getCustomerId());
    }


    public function getGiftCardBalance()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

        if ($card->getId() && ($card->getCardStatus() == 1)) {
            $card->activateCard();

            $this->_getSession()->addSuccess(
                $this->__('Gift Card "%s" was applied.', Mage::helper('core')->escapeHtml($giftCardCode))
            );
            Mage::getSingleton('giftcards/session')->setActive('1');
            $this->_setSessionVars($card);
        } else {
            if($card->getId() && ($card->getCardStatus() == 2)) {
                $this->_getSession()->addError(
                    $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            } else {
                $this->_getSession()->addError(
                    $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            }
        }
    }

    //convert gift card currency to current currency. See Discount/Observer
    private function _convertBalanceToCurrency($oCard)
    {
        $baseCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
        $currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $cardCurrency = $oCard->getCardCurrency();
        $fCardBalance = $oCard->getCardBalance();


        if (is_null($cardCurrency)) {
            $cardCurrency = $baseCurrency;
        }
        //got 1 website. or different websites but baseCurrency is same.
        if(is_null($cardCurrency))
        {
            $cardCurrency = $baseCurrency;
        }
        //got 1 website. or different websites but baseCurrency is same.
        if($baseCurrency == $currentCurrency) {
            if($cardCurrency != $currentCurrency) {
                $balance = Mage::helper('giftcards')->currencyConvert($fCardBalance, /*from*/ $cardCurrency, /*to*/$baseCurrency);
            } else {
                //if all currencies are same (only 1 store view)
                $balance = $fCardBalance;
            }
            //different websites with different baseCurrency
        } else {
            if($baseCurrency == $cardCurrency) {
                $baseBalance =  $fCardBalance;
                $balance = Mage::helper('giftcards')->currencyConvert(/*price*/ $baseBalance,/*from*/ $baseCurrency, /*to*/$currentCurrency);
            } elseif($currentCurrency == $cardCurrency) {
                $balance = $fCardBalance;
            } else {
                $baseBalance = Mage::helper('giftcards')->currencyConvert($fCardBalance, /*from*/ $cardCurrency, /*to*/$baseCurrency);
                $balance = Mage::helper('giftcards')->currencyConvert($baseBalance, /*from*/ $baseCurrency, /*to*/$currentCurrency); //from base to current?
            }
        }

        return $balance;
    }
}
