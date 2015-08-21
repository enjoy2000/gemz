<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Saa
 * Date: 25.06.13
 * Time: 14:52
 * To change this template use File | Settings | File Templates.
 */
class Webtex_Giftcards_Model_Sales_Order extends Mage_Sales_Model_Order
{
    /**
     * Retrieve order credit memo (refund) availability
     *
     * @return bool
     */
    public function canCreditmemo()
    {
        if ($this->hasForcedCanCreditmemo()) {
            return $this->getForcedCanCreditmemo();
        }

        if ($this->canUnhold() || $this->isPaymentReview()) {
            return false;
        }

        if ($this->isCanceled() || $this->getState() === self::STATE_CLOSED) {
            return false;
        }

        /**
         * We can have problem with float in php (on some server $a=762.73;$b=762.73; $a-$b!=0)
         * for this we have additional diapason for 0
         * TotalPaid - contains amount, that were not rounded.
         */
        if (abs($this->getStore()->roundPrice($this->getTotalPaid()) - $this->getTotalRefunded()) < .0001) {
            //webtex giftcards condition for refund
            //if whole part of order grand total (grand total = 0) was discounted by gift cards
            if((int) $this->getBaseGrandTotal() !== 0) {
                return false;
            }

        }

        if ($this->getActionFlag(self::ACTION_FLAG_EDIT) === false) {
            return false;
        }
        return true;
    }
}