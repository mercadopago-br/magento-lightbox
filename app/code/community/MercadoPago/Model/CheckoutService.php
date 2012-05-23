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

class MercadoPago_Model_CheckoutService extends Mage_Payment_Model_Method_Abstract
{
    public function create_checkout_preference($data = array(), $access_token){
      
      $standard = new MercadoPago_Model_Standard();

      $url = "https://api.mercadolibre.com/checkout/preferences?access_token=$access_token";
      $options = $data;
      $return_code = 201;

      $response = $standard->post($url, $return_code, $options, "json");

      return $response;
    }
  
}