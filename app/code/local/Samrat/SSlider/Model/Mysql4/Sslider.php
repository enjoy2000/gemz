<?php
class Samrat_SSlider_Model_Mysql4_Sslider extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("sslider/sslider", "id");
    }
}