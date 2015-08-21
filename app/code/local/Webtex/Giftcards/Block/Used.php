<?php

class Webtex_Giftcards_Block_Used extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        $cards = Mage::getModel('giftcards/giftcards')->getCollection();

        $cards->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('main_table.card_code')
            ->columns('main_table.card_balance')
            ->columns('main_table.card_status')
            ->join(array('go' => Mage::getSingleton('core/resource')->getTableName('giftcards/order')), 'main_table.card_id = go.id_giftcard', array())
            ->join( array('so'=>Mage::getSingleton('core/resource')->getTableName('sales/order')), 'go.id_order = so.entity_id', array())
            ->where('so.customer_id = '.Mage::helper('customer')->getCustomer()->getId())
            ->group('go.id_giftcard');

        $this->setCards($cards);

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('Purchased Gift Cards'));
    }


    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}