<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Prices extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_Giftcards_Block_Adminhtml_Catalog_Product_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $_session = Mage::getSingleton('core/session');
        $_session->setIsBlockInserted(false);
        $_product = Mage::registry('product');
        if($_product->getId() && $_product->getTypeId() == 'giftcards' && ($_product->getAttributeText('wts_gc_pregenerate') == 'Yes')) {
            $this->addTab('pregeneratedcards', array(
                'label'     => Mage::helper('catalog')->__('Pre-Generated Codes'),
                'url'       => $this->getUrl('giftcards/adminhtml_product/pregenerated', array('_current' => true)),
                'class'     => 'ajax',
            ));
        
            
        }

    }

}
