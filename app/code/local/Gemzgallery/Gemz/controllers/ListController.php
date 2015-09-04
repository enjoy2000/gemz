<?php
/**
 * Created by Hat Dao.
 * User: hatdao
 * Date: 9/2/15
 * Time: 11:17 PM
 */

class Gemzgallery_Gemz_ListController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $attributeCode = $this->getRequest()->getParam('attribute');
        //var_dump($attributeCode);die;
        /** @var Mage_Eav_Model_Entity_Attribute $attribute */
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
        $optionValue = $attribute->getSource()->getOptionId($this->getRequest()->get('value'));

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter($attributeCode, ['finset' => $optionValue])
            ->addAttributeToFilter('status', 1)
        ;
        Mage::register('collection', $collection);
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Fifty shades of Sapphire'));
        $this->renderLayout();
    }
}