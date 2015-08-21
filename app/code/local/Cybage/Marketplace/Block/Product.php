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

class Cybage_Marketplace_Block_Product extends Mage_Core_Block_Template
{
    public function __construct() {
        parent::__construct();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(array('marketplace_state', 'name', 'price', 'status'))
                ->addAttributeToFilter('seller_id', $customerId)
                ->addAttributeToFilter('marketplace_state', array('neq' => Mage::helper('marketplace')->getDeletedOptionValue()))
                ->setOrder('entity_id', 'desc');
        $this->setCollection($collection);
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    public function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5 => 5, 15 => 15, 30 => 30, 50 => 50, 100 => 100, 200 => 200));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();

        return $this;
    }

    public function getFormData() {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $data = new Varien_Object(Mage::getSingleton('marketplace/session')->getMarketplaceFormData(true));
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Get image delete url
     *
     * @return string
     */
    public function getImageDeleteUrl($imageType) {
        if ($this->hasDeleteUrl()) {
            return $this->getData('delete_url');
        }

        return $this->getUrl(
                        'marketplace/product/productImageDelete', array(
                    'product_id' => $this->getRequest()->getParam('id'),
                    'image_type' => $imageType,
                    Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
                        )
        );
    }

    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        } else {
            return $this->getUrl('marketplace/product');
        }
    }
}
