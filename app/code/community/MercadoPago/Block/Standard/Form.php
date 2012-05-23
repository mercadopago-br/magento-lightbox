<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */



class MercadoPago_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mercadopago/standard/form.phtml');
        
    }
}




?>
