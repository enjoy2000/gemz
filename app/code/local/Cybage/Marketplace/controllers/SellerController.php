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

class Cybage_Marketplace_SellerController extends Mage_Core_Controller_Front_Action
{
    /*
    * display seller info
    * @created at 08-Oct-2013
    * @modified at 09-Oct-2013
    * @author Srinidhi Damle <srinidhid@cybage.com>
    */
    public function sellerinfoAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function ratingAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
