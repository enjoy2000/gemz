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

class Cybage_Marketplace_Model_Source_Option extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
        return $this->getOptionFromTable();
    }

    private function getOptionFromTable(){
        // Get the seller name by seller id
        $sellerCollection = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect('company_name')
                ->addFieldToFilter('seller_subscriber', 1);

        //Create option array for drop down
        $sellerData      = array();
        $temp['value'] = 'entity_id';
        $temp['label'] = 'company_name';

        $sellerData[]['label'] = 'Please Select';
        foreach ($sellerCollection as $item) {
            //$sellerData[$item[$temp['value']]] = $item[$temp['label']];
            $sellerData[] = array (
                                'label' => $item[$temp['label']],
                                'value' => $item[$temp['value']]
                            );
        }

        return $sellerData;
    }
}
