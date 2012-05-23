<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   	Payment Gateway
 * @package    	MercadoPago
 * @author      Carlos Corrêa (cadu.rcorrea@gmail.com)
 * @copyright  	Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license    	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MercadoPago_Model_AuthService extends Mage_Payment_Model_Method_Abstract
{
        
    public function getAccessData($client_id, $client_secret){
      
      $standard = new MercadoPago_Model_Standard();

      $url = "https://api.mercadolibre.com/oauth/token";
      $options = array(
          'grant_type' => 'client_credentials',
          'client_id' => $client_id,
          'client_secret' => $client_secret
      );
      $return_code = 200;

      $response = $standard->post($url, $return_code, $options);

      return $response;
    }
  
}