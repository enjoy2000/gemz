<?php

/**
 * TaxJar HTTP Client
 *
 * @author Taxjar (support@taxjar.com)
 */
class Taxjar_SalesTax_Model_Client {

  /**
   * Connect to the API
   *
   * @param $string, $string
   * @return JSON $string
   */
  public function getResource( $apiKey, $url ) {
    $response = $this->getClient( $apiKey, $url )->request();

    if ( $response->isSuccessful() ) {
      $json = $response->getBody();

      return json_decode($json, true);
    }
    else {
      throw new Exception('Could not connect to TaxJar.');
    }

  }  

  /**
   * Client GET call
   *
   * @param $string, $string
   * @return Varien_Http_Client $response
   */
  private function getClient( $apiKey, $url ) {
    $client = new Varien_Http_Client( $url );
    $client->setMethod( Varien_Http_Client::GET );
    $client->setHeaders( 'Authorization', 'Token token="' . $apiKey .  '"' );

    return $client;
  }

}

?>