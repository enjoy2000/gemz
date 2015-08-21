<?php

/**
 * TaxJar Debug UI
 *
 * @author Taxjar (support@taxjar.com)
 */
class Taxjar_SalesTax_Model_Debug {

  /**
   * Display debug information
   *
   * @param void
   * @return $string
   */
  public function getCommentText() {
    $debug = Mage::getStoreConfig('taxjar/config/debug');

    if ( $debug ) {
      return "<p class='note'><span>If enabled, does not alter your tax rates or database and instead prints debug messages for use with TaxJar support.</span></p><br/>" . $this->getDebugHtmlString();
    }
    else {
      return "<p class='note'><span>If enabled, does not alter your tax rates or database and instead prints debug messages for use with TaxJar support.</span></p>";
    }
  }

  /**
   * Gather debug information
   *
   * @param void
   * @return $string
   */
  private function getDebugHtmlString() {
    $states         = implode( ',', unserialize( Mage::getStoreConfig('taxjar/config/states') ) );
    $apiUser        = Mage::getModel('api/user');
    $existingUserId = $apiUser->load('taxjar', 'username')->getUserId();
    $pluginVersion  = '1.2.2';
    $phpMemory      = @ini_get('memory_limit');
    $phpVersion     = @phpversion();
    $magentoVersion = Mage::getVersion();
    $lastUpdated    = Mage::getStoreConfig('taxjar/config/last_update');
    return "<ul> <li><strong>Additonal States:</strong> ". $states ."</li> <li><strong>API User ID:</strong> ". $existingUserId ."</li><li><strong>Memory:</strong> ". $phpMemory ."</li> <li><strong>TaxJar Version:</strong> ". $pluginVersion ."</li> <li><strong>PHP Version</strong> ". $phpVersion ."</li> <li><strong>Magento Version:</strong> ". $magentoVersion ."</li> <li><strong>Last Updated:</strong> ". $lastUpdated ."</li> </ul><br/><p><small><strong>Include the above information when emailing TaxJar support at support@taxjar.com</strong><small></p>";
  }

}
?>
