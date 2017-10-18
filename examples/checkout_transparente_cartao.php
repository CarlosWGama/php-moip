<?php
require(dirname(__FILE__).'/_autoload.class.php');

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                    ->addProduto('Caderno', 20) //Produto do vendedor principal
                    ->addProduto('Storage 500GB', 300.00, 'informatica@gmail.com') //produto de 'informatica@gmail.com'
                    ->setComissaoVendedorPrincipal(10) //(Opcional) 10% dos outros vendedores será dado ao vendedor principal
                    ->addFormaPagamento(MoipPagamento::CHECKOUT_CARTAO) //Libera forma de pagamento por cartão
                    ->getCheckoutTransparente();

?>
<?php echo $scripts['default'] ?>

<!-- PAGAR CARTÃO -->
<h1>Cartão de Crédito</h1>
<p>Nome do Titular como Consta no Cartão</p>
<input type="text" id="moip_cartao_titular_nome" placeholder="CARLOS W GAMA"/>
 
<p>CPF do títular </p>
<input type="text" id="moip_cartao_titular_cpf" placeholder="000.000.000-00"/>
 
<p>Data de Nascimento do títular</p>
<input type="text" id="moip_cartao_titular_nascimento" placeholder="01/01/2017"/>
 
<p>Telefone do títular</p>
<input type="text" id="moip_cartao_titular_telefone" placeholder"(99)99999-9999"/>
 
<p>Número do Cartão</p>
<input type="text" id="moip_cartao_numero" placeholder="4012001037141112"/>
 
<p>Data de Expiração</p>
<input type="text" id="moip_cartao_validade" placeholder="05/2018"/>
 
<p>Código de Segurança do Cartão</p>
<input type="text" id="moip_cartao_codigo_seguranca" placeholder="123"/>
 
<p>Parcelas</p>
<select id="moip_cartao_parcelas">
    <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
</select>

<button onclick="MoipPagarCartao();">Pagar com Cartão de Crédito</button>
<br/>