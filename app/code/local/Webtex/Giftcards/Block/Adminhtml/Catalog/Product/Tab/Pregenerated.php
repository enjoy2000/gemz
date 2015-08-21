<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Crossell products admin grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webtex_Giftcards_Block_Adminhtml_Catalog_Product_Tab_Pregenerated extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('pregenerated_giftcards_product_grid');
        $this->setDefaultSort('card_id');
        $this->setUseAjax(true);
    }

     /**
     * Retirve currently edited product model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('uploader',
            $this->getLayout()->createBlock('adminhtml/media_uploader')
        );

        $this->getUploader()->getConfig()
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/adminhtml_product/import/id/'.$this->_getProduct()->getId()))
            ->setFileField('file')
            ->setFilters(array(
                'csv' => array(
                    'label' => Mage::helper('adminhtml')->__('Gift Cards import'),
                    'files' => array('*.csv')
                )
            ));

        return parent::_prepareLayout();
    }

    public function getUploader()
    {
        return $this->getChild('uploader');
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {

        $collection = Mage::getModel('giftcards/pregenerated')->getCollection()
                      ->addFieldToFilter('product_id', $this->_getProduct()->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('card_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'card_id'
        ));

        $this->addColumn('card_code', array(
            'header'    => Mage::helper('catalog')->__('Card Code'),
            'index'     => 'card_code'
        ));

        $this->addColumn('card_status', array(
            'header'    => Mage::helper('catalog')->__('Card Status'),
            'width'     => 90,
            'index'     => 'card_status',
            'type'      => 'options',
            'options'   => array(
                '1' => Mage::helper('giftcards')->__('On Sale'),
                '0' => Mage::helper('giftcards')->__('Sold'),)
        ));

        $this->addColumn('card_delete', array(
            'header'    => Mage::helper('catalog')->__('Action'),
            'width'     => 60,
            'sortable'  => false,
            'filter'    => false,
            'getter'    => 'getId',
            'type'      => 'action',
            'actions'    => array(
                array(
                    'caption'   => Mage::helper('catalog')->__('Delete'),
                    'confirm'   => 'Realy Delete?',
                    'url'       => array('base' => 'giftcards/adminhtml_product/deletecard', 'params' => array('_current' => true)),
                    'field'     => 'card_id',
                ),
            ),
        ));

        return parent::_prepareColumns();
    }

    protected function _afterToHtml($html)
    {
         $_session = Mage::getSingleton('core/session');
         if(!$_session->getIsBlockInserted()){
             $addhtml = '<div class="entry-edit">
                    <div class="entry-edit-head"><h4 class="icon-head head-edit-form fieldset-legend">Generate Gift Card Codes</h4>
                        <div class="form-buttons">
                            <button id="create_giftcards" title="Generate" type="button" class="scalable add" onclick="generateGiftCards(\''.$this->getUrl('giftcards/adminhtml_product/generate').'\','.$this->_getProduct()->getId().');"><span><span><span>Generate</span></span></span></button>
                        </div>
                    </div>
                     <div class="grid">
                     <div class="fieldset fieldset-wide" id="group_fields_create"><div class="hor-scroll">
                        <table cellspacing="0" class="form-list">
                        <tbody>
                          <tr>
                             <td class="label"><label for="create_count">Amount of pre-generated codes</label></td>
                             <td class="value">
                                <input id="create_count" name="create_count" value="" class="input-text" type="text">
                            </td>
                             <td class="scope-label"><span class="nobr">Input amount and press "Generate"</span></td>
                          </tr>
                        </tbody>
                        <tfoot>
                          <tr>
                             <td class="last" style="padding-top: 8px;"><label>Import pre-generated codes</label></td>
                             <td colspan=2 class="last" style="padding-top: 8px;">'.$this->getChildHtml('uploader').'</td>
                          </tr>
                        </tfoot>
                        </table>
                    </div></div><div class="clearer"></div></div>';
            $_session->setIsBlockInserted(1);
        } else {
            $addhtml = '';
        }
        
        return $addhtml . $html;
    }
}
