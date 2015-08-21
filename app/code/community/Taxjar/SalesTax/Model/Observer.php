<?php

/**
 * TaxJar Observer.
 *
 * @author Taxjar (support@taxjar.com)
 */
class Taxjar_SalesTax_Model_Observer {

  /**
   * TaxJar observer
   *
   * @param Varien_Event_Observer $observer
   * @return void
   */
  public function execute( $observer ) {
    $session = Mage::getSingleton( 'adminhtml/session' );
    $storeId = Mage::getModel('core/store')->load( $observer->getEvent()->getStore() )->getStoreId();
    $apiKey = Mage::getStoreConfig('taxjar/config/apikey', $storeId);
    $apiKey = preg_replace( '/\s+/', '', $apiKey );

    if ( $apiKey ) {
      $this->version     = 'v1';
      $client            = Mage::getModel('taxjar/client');
      $configuration     = Mage::getModel('taxjar/configuration');
      $regionId          = Mage::getStoreConfig('shipping/origin/region_id', $storeId);
      $this->storeZip    = Mage::getStoreConfig('shipping/origin/postcode', $storeId);
      $this->regionCode  = Mage::getModel('directory/region')->load( $regionId )->getCode();
      $validZip          = preg_match( "/(\d{5}-\d{4})|(\d{5})/", $this->storeZip );
      $debug             = Mage::getStoreConfig('taxjar/config/debug');

      if( isset( $this->regionCode ) ) {
        $configJson = $client->getResource( $apiKey, $this->apiUrl( 'config' ) );
      }
      else {
        throw new Exception( "Please check that you have set a Region/State in Shipping Settings." );
      }

      if ( $debug ) {
        Mage::getSingleton('core/session')->addNotice("Debug mode enabled. Tax rates have not been altered.");
        return;
      }

      if( ! $configJson['allow_update'] ) {
        $dateUpdated = Mage::getStoreConfig('taxjar/config/last_update');
        Mage::getSingleton('core/session')->addNotice("Your rates are already up to date. Date of last update: " . $dateUpdated);
        return;
      }

      if( $validZip === 1 && isset( $this->storeZip ) && trim( $this->storeZip ) !== '' ) {
        $ratesJson = $client->getResource( $apiKey, $this->apiUrl( 'rates' ));
      }
      else {
        throw new Exception("Please check that your zip code is a valid US zip code in Shipping Settings.");
      }

      Mage::getModel('core/config')
        ->saveConfig('taxjar/config/states', serialize( explode( ',', $configJson['states'] ) ));
      $configuration->setTaxBasis($configJson);
      $configuration->setShippingTaxability($configJson);
      $configuration->setDisplaySettings();
      $configuration->setApiSettings($apiKey);
      Mage::getModel('core/config')
        ->saveConfig( 'taxjar/config/freight_taxable', $configJson['freight_taxable'] );
      $this->purgeExisting();

      if ( false !== file_put_contents( $this->getTempFileName(), serialize( $ratesJson ) ) ) {
        Mage::dispatchEvent('taxjar_salestax_import_rates'); 
      }
      else {
        // We need to be able to store the file...
        throw new Exception("Could not write to your Magento temp directory. Please check permissions for " . Mage::getBaseDir('tmp') . ".");
      }

    }
    else {
      Mage::getSingleton('core/session')->addNotice("TaxJar has been uninstalled. All tax rates have been removed.");
      $this->purgeExisting();
      $this->setLastUpdateDate(NULL);
    }
    // Clearing the cache to avoid UI elements not loading
    Mage::app()->getCacheInstance()->flush();
  }

  /**
   * Read our file and import all the rates, triggered via taxjar_salestax_import_rates
   *
   * @param void
   * @return void
   */
  public function importRates() {
    // This process can take a while
    @set_time_limit( 0 );
    @ignore_user_abort( true );

    $this->newRates            = array();
    $this->freightTaxableRates = array();
    $rate                      = Mage::getModel('taxjar/rate');
    $filename                  = $this->getTempFileName();
    $rule                      = Mage::getModel('taxjar/rule');
    $shippingTaxable           = Mage::getStoreConfig('taxjar/config/freight_taxable');
    $ratesJson                 = unserialize( file_get_contents( $filename ) );

    foreach( $ratesJson as $rateJson ) {
      $rateIdWithShippingId = $rate->create( $rateJson );

      if ( $rateIdWithShippingId[1] ) {
        $this->freightTaxableRates[] = $rateIdWithShippingId[1];
      }

      $this->newRates[] = $rateIdWithShippingId[0];
    }

    $this->setLastUpdateDate( date( 'm-d-Y' ) );
    $rule->create( 'Retail Customer-Taxable Goods-Rate 1', 2, 1, $this->newRates );

    if ( $shippingTaxable ) {
      $rule->create( 'Retail Customer-Shipping-Rate 1', 4, 2, $this->freightTaxableRates ); 
    }

    @unlink( $filename );
    Mage::getSingleton('core/session')->addSuccess("TaxJar has added new rates to your database! Thanks for using TaxJar!");
  }

  /**
   * Build URL string
   *
   * @param $string
   * @return $string
   */
  private function apiUrl( $type ) {
    $apiHost = 'https://api.taxjar.com/';
    $prefix  = $apiHost . 'magento/' . $this->version . '/';

    if ( $type == 'config' ) {
      return $prefix . 'get_configuration/' . $this->regionCode;
    }
    elseif ( $type == 'rates' ) {
      return $prefix . 'get_rates/' . $this->regionCode . '/' . $this->storeZip;
    }
    
  }

  /**
   * Purges the rates and rules
   *
   * @param void
   * @return void
   */
  private function purgeExisting() {
    $paths = array('tax/calculation', 'tax/calculation_rate', 'tax/calculation_rule');

    foreach( $paths as $path ) {
      $existingRecords = Mage::getModel($path)->getCollection();

      foreach( $existingRecords as $record ) {

        try {
          $record->delete();
        }
        catch (Exception $e) {
          Mage::getSingleton('core/session')->addError("There was an error deleting from Magento model " . $path);
        }

      }

    }

  }

  /**
   * Set the last updated date
   *
   * @param $string || NULL
   * @return void
   */
  private function setLastUpdateDate( $date ) {
    Mage::getModel('core/config')->saveConfig('taxjar/config/last_update', $date);
  }

  /**
   * Set the filename
   *
   * @param void
   * @return $string
   */
  private function getTempFileName() {
    return Mage::getBaseDir('tmp') . DS . "tj_tmp.dat";
  }

}
?>