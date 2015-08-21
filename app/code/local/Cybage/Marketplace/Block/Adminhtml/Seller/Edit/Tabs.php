<?php
/**
 * Cybage Marketplace Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_Magento@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Marketplace Plugin
 * @package    Cybage_Marketplace
 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_Magento@cybage.com>
 */

class Cybage_Marketplace_Block_Adminhtml_Seller_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('seller_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('marketplace')->__('Seller Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('seller_form', array(
            'label'     => Mage::helper('marketplace')->__('Seller Information'),
            'title'     => Mage::helper('marketplace')->__('Seller Information'),
            'content'   => $this->getLayout()->createBlock('marketplace/adminhtml_seller_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
