<?php
require(dirname(__FILE__).'/_autoload.class.php');

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->setPreco(10.00)   //Preço da compra
                        ->setDescricao('Descrição da Compra')
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_DEBITO_BANCARIO) //Libera forma de pagamento apenas para Debito Bancário
                        ->getCheckoutTransparente();

$instituicoesDebito = $moipPag->getInstituicoesDebito();

?>
<?php echo $scripts['default'] ?>

<!-- PAGAR DEBITO BANCARIO -->
<h1>Debito Bancário</h1> 
<select id="moip_debito_instituicao">
<?php foreach($instituicoesDebito as $key => $value): ?>
    <option value="<?php echo $key ?>"> <?php echo $value?></option>
<?php endforeach; ?>
</select>

<button onclick="MoipPagarDebitoBancario();">Pagar com Debito Bancário</button>
<br/>
