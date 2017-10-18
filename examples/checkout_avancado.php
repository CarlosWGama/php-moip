<?php
require(dirname(__FILE__).'/_autoload.class.php');

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$urlPagamento = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->setPreco(50.00)   //Preço da compra
                        ->setVendedor('carloswgama@gmail.com') //Adiciona quem deverá receber o apagamento ao invés da conta vinculada a API
                        //->addComissao('carloswgama@gmail.com', 10) //Adiciona outro vendedor que irá receber 10 reais de comissão  do vendedor principal dessa venda
                        //->addComissao('carloswgama3@gmail.com', 10, TRUE) //Adiciona outro vendedor que irá receber 10% (5 reais) de comissão  do vendedor principal dessa venda
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_BOLETO) //Libera forma de pagamento Boleto
                        ->configurarBoleto('2017-03-01', 'http://site.com.br/logo.png', array('Linha 1', 'Linha 2')) //Informações do boleto
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_CARTAO) //Libera forma de pagamento por cartão
                        ->setDescricao('Descrição da Compra')
                        ->pagar();
                    
if (!$urlPagamento) die ($moipPag->getErro());

echo "URL para o checkout do moip: " . $urlPagamento;
