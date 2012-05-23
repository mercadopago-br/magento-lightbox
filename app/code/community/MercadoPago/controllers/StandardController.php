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

class MercadoPago_StandardController extends Mage_Core_Controller_Front_Action
{
    
    
    
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');

        
        
    $this->loadLayout();
    
    //Creating a new block
    $block = $this->getLayout()->createBlock('mercadopago/standard_redirect');

    $this->getLayout()->getBlock('content')->append($block);

    //Now showing it with rendering of layout
    $this->renderLayout();    
        
        
        
        
        
     $session->unsQuoteId(); 
        
        
        
        
    }
    
    public function returnAction()
    {
        try {
            $data = $_REQUEST;
            $ipnservice = new MercadoPago_Model_IpnService();
            $ipnservice->notification_hash($data);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

}
