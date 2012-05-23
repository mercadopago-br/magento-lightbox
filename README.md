magento-lightbox
================

magento-lightbox

Installation instructions

Compatible with Magento 1.4,1.5,1.6 with Mercado Pago 2.0 with Lightbox

Copy the folds "APP" and "Skin" to the Magento root installation, make sure to keep the Magento folders structure intact.

In your admin go to System->Cache Management and clear all caches, go to System->IndexManagement and Select all fields and do the action Reindex Data.

Now go to System->Configuration on Sales/Payment Methods you gonna see now MercadoPago.

Setup MercadoPago configurations.

Get your Client_id and Client_Secret in

Brasil:: https://www.mercadopago.com/mlb/ferramentas/aplicacoes
Argentina::https://www.mercadopago.com/mla/ferramentas/aplicacoes


The standart URL for successuful payment or pending payment is
[yourstoreaddrees.com]/index.php/checkout/onepage/success/
but you can use any page as you want


Important:: To receive Payment Status Update

On MercadoPago, setup your Return Url (IPN) on https://www.mercadopago.com/mlb/ferramentas/notificacoes case Brasil or https://www.mercadopago.com/mla/herramientas/notificaciones case Argentina

Enter the URL as follow 

[yourstoreaddrees.com]/index.php/MercadoPago/standard/return/

