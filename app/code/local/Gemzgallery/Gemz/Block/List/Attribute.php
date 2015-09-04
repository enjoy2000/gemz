<?php

/**
 * Created by Hat Dao.
 * User: hatdao
 * Date: 9/2/15
 * Time: 11:58 PM
 */
class Gemzgallery_Gemz_Block_List_Attribute extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setCollection(Mage::registry('collection'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit([8 => 8, 16 => 16, 32 => 32, 'all' => 'all']);
        $pager->setCollection($this->getCollection());
        if ($page = (int) $this->getRequest()->getParam('p')) {
            $pager->setCurrentPage($page);
        } else {
            $pager->setCurrentPage(1);
        }
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

}
