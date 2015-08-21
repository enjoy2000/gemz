<?php

/**
 * Create tax rules
 *
 * @author Taxjar (support@taxjar.com)
 */
class Taxjar_SalesTax_Model_Rule {

  /**
   * Display Nexus states loaded and API Key setting
   *
   * @param $string, $integer, $integer, $array
   * @return void
   */
  public function create( $code, $productClass, $position, $newRates ) {
    $ruleModel  = Mage::getSingleton('tax/calculation_rule');

    $attributes = array(
      'code' => $code,        
      'tax_customer_class' => array( 3 ), 
      'tax_product_class' => array( $productClass ), 
      'tax_rate' => $newRates,
      'priority' => 1,
      'position' => $position
    );

    $ruleModel->setData( $attributes );
    $ruleModel->setCalculateSubtotal( 0 );
    $ruleModel->save();
    $ruleModel->saveCalculationData();
  }  

}
?>