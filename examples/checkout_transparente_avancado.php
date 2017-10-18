<?php
require(dirname(__FILE__).'/_autoload.class.php');

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$extraScriptSucesso = 'alert(data.Mensagem);';
$extraScriptFalha = 'alert(data.Mensagem);';

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->setPreco(50.00)   //Preço da compra
                        ->setDescricao('Descrição da Compra')
                        ->setVendedor('carloswgama@email.com.br') //Adiciona quem deverá receber o apagamento ao invés da conta vinculada a API
                        //->addVendedorSecundario('carloswgama2@gmail.com', 10) //Adiciona outro vendedor que irá receber 10 reais dessa venda
                        //->addVendedorSecundario('CarlosWGama', 10, TRUE) //Adiciona outro vendedor que irá receber 10% (5 reais) dessa venda
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_DEBITO_BANCARIO) //Libera forma de pagamento
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_BOLETO) //Libera forma de pagamento 
                        ->configurarBoleto('2017-03-01', 'http://site.com.br/logo.png', array('Linha 1', 'Linha 2')) //Informações do boleto (Opcional)
                        ->getCheckoutTransparente($extraScriptSucesso, $extraScriptFalha);

if (empty($scripts)) die($moipPag->getErro());

?>
<?php echo $scripts['scriptMoip'] ?>
<?php echo $scripts['scriptSucesso'] ?>
<?php echo $scripts['scriptFalha'] ?>

<!-- PAGAR BOLETO -->
<h1>Boleto chamando script separado</h1> 
<button onclick="MoipPagarBoleto();">Imprimir Boleto</button>
<br/>
<hr/>
<?php echo $scripts['scriptPagamentos']['boleto'] ?>

<!-- PAGAR DEBITO BANCARIO -->
<h1>Debito Bancário com Script Personalizado</h1> 

<button onclick="MoipPagarDebitoBancarioPersonalizado();">Pagar com Debito Bancário</button>
<br/>
<script type='text/javascript'>
    function MoipPagarDebitoBancarioPersonalizado() {
        var settings = { 
            "Forma": "DebitoBancario" , 
            "Instituicao": "BancoDoBrasil"
        } 
        MoipWidget(settings);
    }
</script>