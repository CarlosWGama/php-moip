<?php
require(dirname(__FILE__).'/_autoload.class.php');

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

//Adicionando produtos de diferentes vendedores 
$urlPagamento = $moipPag->setID(uniqid())   //ID unico para identificar a compra (OPCIONAL)
                        ->addProduto('Caderno', 20) //Produto do vendedor principal
                        ->addProduto('Tenis', 140.50, 'carloswgama@gmail.com') //produto de carloswgama@gmail.com
                        ->addProduto('Camiseta', 40.00, 'carloswgama@gmail.com') //produto de carloswgama@gmail.com
                        ->addProduto('Storage 500GB', 300.00, 'informatica@gmail.com') //produto de 'informatica@gmail.com'
                        ->setComissaoVendedorPrincipal(10) //(Opcional) 10% dos outros vendedores será dado ao vendedor principal
                        ->setDescricao('Descrição da Compra')
                        ->pagar();

if (!$urlPagamento) die ($moipPag->getErro());

echo "URL para o checkout do moip: " . $urlPagamento;
