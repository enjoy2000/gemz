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

class Cybage_Marketplace_Model_Customatributestatus
{
    //Source Model for displaying status option
    public function toOptionArray($multiSelect = false)
    {
        //Load state attribute values by id
        $attributeId      = Mage::getResourceModel('eav/entity_attribute')
                            ->getIdByCode('customer', 'status');
        $attribute        = Mage::getModel('catalog/resource_eav_attribute')
                            ->load($attributeId);
        $attributeOptions = $attribute->getSource()->getAllOptions(false);

        //Create option array for drop down
        $stateName           = array();
        $additional['value'] = 'value';
        $additional['label'] = 'label';

        foreach ($attributeOptions as $item) {
            foreach ($item as $code => $field) {
                $data[$item[$additional['value']]] = $item[$additional['label']];
            }
            $stateName = $data;
        }
        return $stateName;
    }
}
