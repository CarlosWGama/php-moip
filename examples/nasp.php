<?php
require(dirname(__FILE__).'/_autoload.class.php');

use CWG\Moip\MoipNASP;

$nasp = $_POST;

//Em caso de falha
$classificacao = MoipNASP::getClassificacao($nasp['classificacao']);

//Descrição do status do pagamento
$statusPagamento = MoipNASP::getStatusPagamento($nasp['status_pagamento']);

//Descição da forma de pagamento
$formasPagamento = MoipNASP::getFormaPagamento($nasp['forma_pagamento']);

//Todos os itens acima, mas os dados restantes
$naspFormatado = MoipNASP::formateNASP($nasp);