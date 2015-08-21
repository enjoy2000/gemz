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

class Cybage_Marketplace_Adminhtml_OrderbyController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action breadcrumbs and active menu
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('marketplace/orderby')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Orders By Each Seller'), Mage::helper('adminhtml')->__('Orders By Each Seller'));
        return $this;
    }

    /**
     * Reports of Order by each seller
     */
    public function indexAction() {
        $this->_title($this->__('Reports'))->_title($this->__('Marketplace'))->_title($this->__('Orders By Each Seller'));
        $this->_initAction()
                ->_setActiveMenu('marketplace/orderby')
                ->_addBreadcrumb(Mage::helper('marketplace')->__('Orders By Each Seller'), Mage::helper('marketplace')->__('Orders By Each Seller'));
        $gridBlock = $this->getLayout()->getBlock('adminhtml_orderby.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));
        $this->renderLayout();
    }

    /**
     *  init the report action for Reports of Order by each seller
     */
    public function _initReportAction($blocks) {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $params = new Varien_Object();
        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }
        return $this;
    }

    public function exportOrderCsvAction() {
        $fileName = 'order.csv';
        $grid = $this->getLayout()->createBlock('marketplace/adminhtml_orderby_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile($fileName));
    }

    public function exportOrderExcelAction() {
        $fileName = 'order.xml';
        $grid = $this->getLayout()->createBlock('marketplace/adminhtml_orderby_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
