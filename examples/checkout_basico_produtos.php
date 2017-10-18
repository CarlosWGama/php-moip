<?php
require(dirname(__FILE__).'/_autoload.class.php');

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$urlPagamento = $moipPag->setID(uniqid())   //ID unico para identificar a compra (OPCIONAL)
                        ->addProduto('Caderno', 23.99)   //Preço do Caderno
                        ->addProduto('Lápis', 2.00)   //Preço do Lápis
                        ->pagar();

if (!$urlPagamento) die ($moipPag->getErro());

echo "URL para o checkout do moip: " . $urlPagamento;
