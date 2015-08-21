<?php

class Webtex_Giftcards_Block_Adminhtml_Card_Grid_Renderer_Balance extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        return Mage::helper('core')->currency($row->getCardBalance());
    }
}