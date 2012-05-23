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

class MercadoPago_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{   
    
//    protected function _construct(){
//        
//        parent::_construct();
//        $this->setTemplate('mercadopago/standard/redirect.phtml');
//        
//    }

    
    protected function _toHtml(){
    
 //  protected function _construct(){

    
        $standard = new MercadoPago_Model_Standard();
       
          
        $html = '<div class="botao">';
        if($standard->getCountry() == 'MLB'):
        $html .= '<div class="left"/><h3 style="margin: 10px;">Continue pagando com MercadoPago</h3></div><div class="right" />';
        else:
        $html .= '<div class="left"/><h3 style="margin: 10px;">Continue pagando con MercadoPago</h3></div><div class="right" />';    
        endif;
        $html .= '<a href="' . $standard->getInitPoint() . '" name="MP-payButton" class="lightblue-ar-s-ov" mp-mode="modal" callback="execute_my_callback" id="btnPagar">Pagar</a>';
        $html .= '</div>';
        if($standard->getCountry() == 'MLB'):
        $html .= '<img src="' . $this->getSkinUrl('images/mercadopago/mercadopagobr.jpg') .'" alt="MercadoPago" title="MercadoPago" />';
        else:
        $html .= '<img src="' . $this->getSkinUrl('images/mercadopago/mercadopagoar.jpg') .'" alt="MercadoPago" title="MercadoPago" />';    
        endif;
        $html .= '  <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>';
        $html .= '  <script type="text/javascript">';
        $html .= '     function fireEvent(obj,evt){';
        $html .= '         var fireOnThis = obj;';
        $html .= '         if( document.createEvent ) {';
        $html .= '            var evObj = document.createEvent(\'MouseEvents\');';
        $html .= '            evObj.initEvent( evt, true, false );';
        $html .= '            fireOnThis.dispatchEvent( evObj );';
        $html .= '         } else if( document.createEventObject ) {';
        $html .= '            var evObj = document.createEventObject();';
        $html .= '            fireOnThis.fireEvent( \'on\' + evt, evObj );';
        $html .= '         }';
        $html .= '     }';
        $html .= '     fireEvent(document.getElementById("btnPagar"), \'click\')';
        $html .= '  </script>';
        $html .= '</div>';
        return utf8_decode($html);
        
    }
}