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

class MercadoPago_Model_IpnService extends Mage_Payment_Model_Method_Abstract
{
    /**
     *  
     * @param type $data 
     */
    protected $_order = null;
    
    /**
     *
     * @param type $data 
     */
    protected $_request = null;
    
    /**
     *
     * @param type $data 
     */
    protected $_return = null;
    
    public function notification_hash($data){
      
        $this->_request = $data;

        if ( $this->_request['topic'] == "payment" && (int)$this->_request['id'] > 0 ) {

            $standard = new MercadoPago_Model_Standard();
            $access_token = $standard->getAccessToken();
            $url = "https://api.mercadolibre.com/collections/notifications/" . $this->_request['id'] . "?access_token=$access_token";
            $return_code = 201;
            $this->_return = $standard->post($url, $return_code, "", "json");

            // TODO: write connection log
            //$this->_log();
            
            if ((int)$this->_return['collection']['id'] === (int)$this->_request['id']) {
                $this->_process_order();
            }
            
        }
        
    }
    
    private function _process_order()
    {   
        $standard = new MercadoPago_Model_Standard();
    
        $this->_get_order();
        
        switch ( $this->_return['collection']['status']) {
          case 'approved':
              $invoice = $this->_order->prepareInvoice();
              $invoice->register()->pay();
              Mage::getModel('core/resource_transaction')
                      ->addObject($invoice)
                      ->addObject($invoice->getOrder())
                      ->save();
           //   $status = "processing";
              $status = $standard->GetStatus('order_status_approved');
	      var_dump($status);
              $message = 'Payment '.$invoice->getIncrementId().' was created. MercadoPago automatically confirmed payment for this order.';
            break;
          case 'refunded':
           //   $status = "closed";
              $status = $standard->GetStatus('order_status_refunded');
              $message = 'Payment was refound. The vendor returned the values ​​of this operation.';
            break;
          case 'pending':
              $status = $standard->GetStatus('order_status_in_process');
              $message = 'The user has not completed the payment process yet.';
          case 'in_process':
            //  $status = "pending";
              $status = $standard->GetStatus('order_status_in_process');
              $message = 'The payment is been analysing.';
            break;
          case 'in_mediation':
           //   $status = "pending";
              $status = $standard->GetStatus('order_status_in_mediation');
              $message = 'It started a dispute for the payment.';
            break;
          case 'cancelled':              
              $status = $standard->GetStatus('order_status_cancelled');
              $message = 'Payment was canceled.';
             break;
          default:
            $status =  $this->_return['collection']['status'];
            $message = "";            
        }
          
        $this->_order->addStatusToHistory($status, $message);        
        $this->_order->save();
    }
    
    private function _get_order()
    {
        if ( empty ($this->_order) ) {
            $id = $this->_return['collection']['external_reference'];
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($id);
        }
    }

    private function _log()
    {
        $record = $this->parse_json($data);
        var_dump($record);
    }
    
    public function parse_json($array)
    {
      foreach ($array as $k => $v) {
        if (is_array($v)) {
          $line .= self::parse_json($v);
        }
        $line .= " \"$k\": $v, <br>";
      }
      return $line;
    }
}