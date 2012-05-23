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
class MercadoPago_Model_Standard extends Mage_Payment_Model_Method_Abstract
implements Mage_Payment_Model_Recurring_Profile_MethodInterface

// implements Mage_Payment_Model_Recurring_Profile_MethodInterface
{
    //changing the payment to different from cc payment type and mercadopago payment type
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';
    protected $_formBlockType = 'mercadopago/standard_form';
    protected $_code = 'mercadopago_standard';
    
    protected $_isGateway                   = false;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canReviewPayment            = true;

    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('mercadopago/standard/redirect', array('_secure' => true));
    }

    /*
     * TODO : set comments better :)
     */

    public function getAccessToken() {
        /*
         * Get data from admin config / mercadopago
         */
        $client_id = trim($this->getConfigData('client_id'));
        $client_secret = trim($this->getConfigData('client_secret'));
        /*
         * Instance AuthService from SDK-PHP / MercadoPago
         */
        $authService = new MercadoPago_Model_AuthService();
        /*
         * Set data to create a access data
         */
        $access_data = $authService->getAccessData($client_id, $client_secret);
        /*
         * Return string with access_token
         */
        return $access_data['access_token'];
    }

    public function setPreference() {
        $access_token = $this->getAccessToken();

        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
      //  var_dump($order);die;
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $name = ''; 
        foreach ($order->getAllVisibleItems() as $item) {
            $name .= $item->getName();
         //   $client .=  $item->getName();
        }

        $item_price = $order->getBaseGrandTotal();

        if (!$item_price) {
            $item_price = $order->getBasePrice() + $order->getBaseShippingAmount();
        }

        $item_price = number_format($item_price, 2, '.', '');


        
        $methods_excludes = preg_split("/[\s,]+/",$this->getConfigData('excluded_payment_methods')); 
                 foreach ($methods_excludes as $exclude ){
                 $excluded_payment_methods[] = array('id' => $exclude);     
        }

        

        $checkoutPreferenceData = array(
            "external_reference" => $orderIncrementId,
            "expires" => false,
            "items" => array(
                array(
                    "id" => $orderIncrementId,
                    "title" => utf8_encode($name),
                    "description" => utf8_encode($name),
                    "quantity" => (int) 1,
                    "unit_price" => (float) $item_price,
                    "currency_id" => $this->getConfigData('currency'),
                    "picture_url" => ""
                )
            ),
            "payer" => array(
                "name" => htmlentities($customer->getFirstname()),
                "surname" => htmlentities($customer->getLastname()),
                "email" => htmlentities($customer->getEmail())
            ),
            "back_urls" => array(
                "success" => $this->getConfigData('url_success'),
                "pending" => $this->getConfigData('url_process')
            ),
            "payment_methods" => array(
                "excluded_payment_methods" => $excluded_payment_methods,
                "installments" => (int) $this->getConfigData('installments')
            ),
        );

      //  var_dump($checkoutPreferenceData);die;
        $checkoutService = new MercadoPago_Model_CheckoutService();
        $checkoutPreference = $checkoutService->create_checkout_preference($checkoutPreferenceData, $access_token);
     //   print_r($checkoutPreference);die;
        return $checkoutPreference;
    }

    public function getInitPoint() {
       
    $preference = $this->setPreference();
    return $preference['init_point'] ? $preference['init_point'] : $preference;
        
        
    }

    public function createPlan($data) {
        $access_token = $this->getAccessToken();


        $post['title'] = $data['name'];
        $post['description'] = $data['description'];
        $post['quota_quantity'] = $data['recurring_profile']['quota_quantity'];
        $post['amount'] = $data['price'];
        $post['currency_id'] = $this->getConfigData('currency');
        $post['setup_amount'] = $data['recurring_profile']['init_amount'];
        $post['setup_amount'] = $data['recurring_profile']['init_amount'];
        $post['frequency_type'] = $data['recurring_profile']['period_unit'];
        $post['frequency'] = (int)$data['recurring_profile']['period_frequency'];
        $post['allow_subscription_update'] = $data['recurring_profile']['allow_subscription_update'];

        if ($data['recurring_profile']['has_trial_period'] == '1') {
            $post['discount']['period'] = $data['recurring_profile']['discount_period'];
            $post['discount']['percentage'] = $data['recurring_profile']['discount_percentage'];
            $post['discount']['apply_addons'] = 'no';
        }

//        if ($limit != '') {
//            $date = new DateTime();
//            $date->setDate(2012, 12, 3);
//            $encer = $date->format($limit) . 'T00:00:00-0000';
//            $datelimit = array(
//                'due_date' => $encer
//            );
//            $post = array_merge($post, $datelimit);
//        }
        $url = 'https://api.mercadolibre.com/subscription_plans?access_token=' . $access_token;

        $data = $this->post($url,'201',$post,'json','POST');

        return $data['id'];
    }
    
    public function updatePlan($data,$id)
    {
       
        $access_token = $this->getAccessToken();
        
        $post['title'] = $data['name'];
        $post['description'] = $data['description'];
        $post['quota_quantity'] = $data['recurring_profile']['quota_quantity'];
        $post['amount'] = $data['price'];
        $post['currency_id'] = $this->getConfigData('currency');
        $post['setup_amount'] = $data['recurring_profile']['init_amount'];
        $post['setup_amount'] = $data['recurring_profile']['init_amount'];
        $post['frequency_type'] = $data['recurring_profile']['period_unit'];
        $post['frequency'] = (int)$data['recurring_profile']['period_frequency'];
        $post['allow_subscription_update'] = $data['recurring_profile']['allow_subscription_update'];

        if ($data['recurring_profile']['has_trial_period'] == '1') {
            $post['discount']['period'] = $data['recurring_profile']['discount_period'];
            $post['discount']['percentage'] = $data['recurring_profile']['discount_percentage'];
            $post['discount']['apply_addons'] = 'no';
        }            
              
        $url = 'https://api.mercadolibre.com/subscription_plans/'.$id.'/?access_token=' . $access_token;

        $data = $this->post($url,'201',$post,'json','PUT');

        return $data['id'];
    }
    public function post($url, $return_code, $options = array(), $type = "data", $method = "POST") {
        if ($type === "json") {
            $header = array('Accept: application/json', 'Content-Type: application/json');
            $postData = json_encode($options);
            }else{
            $header = array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded');
            foreach ($options as $key => $value) {
                $opt[] = "{$key}=" . urlencode($value);
            }
            if(isset($opt)){
            $postData = implode("&", $opt);
            } else {
            $postData = '';
            }
        }   
        $handler = curl_init();
        if ($method != "POST") {
            curl_setopt($handler, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_HTTPHEADER, $header);
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, FALSE);

        $content = curl_exec($handler);

        $httpcode = curl_getinfo($handler, CURLINFO_HTTP_CODE);

        if ($httpcode == $return_code) {
            $response = json_decode($content, true);
        } else {
            $message = curl_errno($handler) ? curl_error($handler) : "ERROR";
            $httpcode = $httpcode == 0 ? 500 : $httpcode;
            $response = json_decode($content, true);
        }
        curl_close($handler);

        return $response;
    }
    
    public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile){
        
            echo 'ulala';
    }

    /**
     * Submit to the gateway
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info $paymentInfo
     */
    public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile, Mage_Payment_Model_Info $paymentInfo){
        
        
        $profile->setReferenceId(11);
        $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
       // $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_PENDING);
     
        
    }

    /**
     * Fetch details
     *
     * @param string $referenceId
     * @param Varien_Object $result
     */
    public function getRecurringProfileDetails($referenceId, Varien_Object $result){
        
         echo 'ss3';die;
    }

    /**
     * Check whether can get recurring profile details
     *
     * @return bool
     */
    public function canGetRecurringProfileDetails(){
         echo 'ss4';
    }

    /**
     * Update data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile){
        
         echo 'ss5';die;
        
    }

    /**
     * Manage status
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile){
         echo 'ss6';die;
        
    }

}