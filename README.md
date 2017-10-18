# PHP - MoIP
Classe para realizar pagamentos normais no ambiente MoIP ou com checkout transparente

*Está biblioteca é baseada na Versão 1 do Moip (Uma vez que a Versão 2 ainda está em Beta e com alguns bugs) e usa por trás a SDK V1 do Moip*

-----
# Ambientes
Primeiro é necessário saber que o Moip possui dois ambientes: Produção e Sandbox (Ambiente de teste) e duas versões:

Moip V2 - Atual e ainda em Beta
Moip V1 - Antigo, funcional e o mais usado ainda nos projetos.

Os links para acessos são:

[Moip V2 - Produção](https://conta.moip.com.br/)
[Moip V2 - Sandbox](https://conta-sandbox.moip.com.br/)

[Moip V1 - Produção](https://www.moip.com.br/MainMenu.do?method=login)
[Moip V1 - Sandbox](https://desenvolvedor.moip.com.br/sandbox/MainMenu.do?method=home)

O ambiente V2 ainda estão em desenvolvimento, por isso alguns recursos podem apresentar falhas ou não estar disponíveis ainda, mas todos recursos que formos usar no V2, também está disponível e 100% funcional no V1.

-----
# Configurando
## Buscando o Token e a Key

O primeiro passo é logar na conta do MoiP. 

### MoIP V2
Após logar, ir na opção [Minha Conta >> Configurações >> Chaves de Acesso](https://conta-sandbox.moip.com.br/configurations/api_credentials) e buscar o Token e a Chave (key):

![Chaves de Acesso no V2](http://carloswgama.com.br/moip/tutorial/moip_v2_chaves.jpg)

### MoIP V1
Após logar, ir na opção [Ferramentas >> API MoIP >> Chaves de Acesso](https://desenvolvedor.moip.com.br/sandbox/AdmAPI.do?method=keys) e buscar o Token e a Chave (key):

![Chaves de Acesso no V1](http://carloswgama.com.br/moip/tutorial/moip_v1_chaves.jpg)


## Configurando URL de notificação
*a URL de notifcação é o link para onde será enviado todas as notificações de atualização do status da compra (Ex: Foi  iniciada, cancelada, aprovada...)*

### MoIP V2
Ir na opção [Minha Conta >> Configurações >> Notificações](https://conta-sandbox.moip.com.br/configurations/subscriptions_preferences) e inserir o link para onde as notificações serão enviadas

![Chaves de Acesso no V2](http://carloswgama.com.br/moip/tutorial/moip_v2_url_notificacao.jpg)

### MoIP V1
Ir na opção [Meus Dados >> Preferências >> Notificação de Transações](https://desenvolvedor.moip.com.br/sandbox/AdmMainMenuMyData.do?method=transactionnotification) e inserir o link para onde as notificações serão enviadas 

![Chaves de Acesso no V2](http://carloswgama.com.br/moip/tutorial/moip_v1_url_notificacao.jpg)

-----
# Instalando a biblioteca

Para usar a biblioteca em seu projeto, baixe esse repositório e importe as classes MoipPagamento.php e MoipNASP.php ou importe no seu projeto através do Composer (Mais indicado):

```
composer require carloswgama/php-moip:1.*
```

Caso seu projeto já possua um arquivo composer.json, você pode também adiciona-lo nas dependências require e rodar um composer install:
```
{
    "require": {
        "carloswgama/php-moip": "1.*"
    }
}
```

### Atualização 1.2.0
- Novo recurso do MoIP Marketplace

Permitir na mesma venda adicionar produtos de diferentes vendedores. Caso o produto náo seja do vendedor principal, basta informar no terceiro parametro o login do vendedor que receberá pela venda daquele produto
- addProduto($produto, $valor, $login = '')

Permitir adicionar uma porcentagem que será cobrada dos outros vendedores e dado ao vendedor principal
- setComissaoVendedorPrincipal($porcentagem)

# Usando a biblioteca
## Checkout no Ambiente MoIP
### Criando uma nova compra simples
``` php
<?php
require dirname(__FILE__).'/vendor/autoload.php';

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$urlPagamento = $moipPag->setID('CWG_001')   	//ID unico da compra
                        ->setPreco(10.00)   	//Preço da compra
                        ->setDescricao('Descrição da Compra')
                        ->pagar();				//Cria a compra e retorna o link para checkout

if (!$urlPagamento) die ($moipPag->getErro());	//Apresenta mensagem, caso tenha ocorrido algum erro

echo "URL para o checkout do moip: " . $urlPagamento;
```

### Criando uma nova compra informando Produtos
``` php
<?php
require dirname(__FILE__).'/vendor/autoload.php';

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$urlPagamento = $moipPag->setID(uniqid())   //ID unico para identificar a compra (OPCIONAL)
                        ->addProduto('Caderno', 23.99)   //Preço do Caderno
                        ->addProduto('Lápis', 2.00)   //Preço do Lápis
                        ->pagar();

if (!$urlPagamento) die ($moipPag->getErro());	//Apresenta mensagem, caso tenha ocorrido algum erro

echo "URL para o checkout do moip: " . $urlPagamento;
```


A biblioteca para realizar a compra através do ambiente do MoIP possuio os seguintes métodos:

| Método                       | Parametro                                                                                                  | Descrição                                                                                                        | Retorna                              | Obrigatório                      |
|------------------------------|------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------|--------------------------------------|----------------------------------|
| setID($id)                   | $id (string)                                                                                               | Seta um ID único para identificar a compra. Esse ID normalmente é usado para identificar a compra no seu sistema | Própria Classe MoipPagamento         | NÃO                              |
| setPreco($preco)             | $preco (float)                                                                                             | Informa o preço da compra                                                                                        | PrópriaClasse MoipPagamento          | NÃO                           |
| setDescricao($desc)          | $desc (string)                                                                                             | Informa ao vendedor a descrição do que está sendo comprado                                                       | Própria Classe MoipPagamento         | SIM (Não caso use o método addProduto())                             |
| pagar()                      | ---                                                                                                        | Processa o pedido e gera o link para acessar o ambiente de compra do MoIP                                        | URL para o checkout no ambiente MoIP | SIM para compra no ambiente MoIP |
| setCredenciais($token, $key) | $token (string) $key (string)                                                                              | Seta as credênciais do MoIP na classe, caso elas não tenham sido passadas no construtor.                         | PrópriaClasse MoipPagamento          | NÃO                              |
| setSandbox($sandbox)         | $sandbox (boolean)                                                                                         | Informa se é para usar o ambiente sandbox (true) ou de produção (false), caso não informado no construtor.       | PrópriaClasse MoipPagamento          | NÃO                              |
| setVendedor($login)                   | $login(string login ou email do vendedor principal)                                                                                               | Por padrão o vendedor principal é a conta vinculada a Token usado na API. Ao setar um vendedor, este vendedor é que receberá o dinheiro da venda e não mais o dono do token na API | Própria Classe MoipPagamento         | NÃO                              |
| addComissao($login, $valor, $forma = false, $taxaMoip = false)                   | $login (string com login ou senha do vendedor secundário)    $valor (Valor que o vendedor irá ganhar de comissão)    $forma (FALSE - Se o valor da comissão é um valor fixo ou TRUE caso o valor da comissão seja em porcentagem)    $taxaMoip (TRUE se o vendedor secundário também vai pagar a taxa do MoIP ou FALSE caso ele não pague a taxa do MoIP)                                                                                               | Este método adiciona vendedores secundários que irão dividir o recebimento do valor da venda com o vendedor principal | Própria Classe MoipPagamento         | NÃO                              |
| addProduto($produto, $valor, $login = '')                   | $produto (string)    $valor (float)    $login (string opcional, com o login de outro vendedor caso o produto pertença a outro vendedor, que irá receber pelo produto)  | Este método adiciona ao total da compra o preço de um produto. Caso o produto pertença a outro vendedor será creditado na conta informada. | Própria Classe MoipPagamento         | NÃO                              |
| setComissaoVendedorPrincipal($porcentagem)                   | $porcentagem (float) | Caso informado, será reduzido do valor que seria recebido pelos vendedores secundários uma porcentagem que será dada ao vendedor principal | Própria Classe MoipPagamento         | NÃO                              |
| addFormaPagamento($forma)    | $forma:MoipPagamento::CHECKOUT_BOLETO \|\| MoipPagamento::CHECKOUT_CARTAO \|\| MoipPagamento::CHECKOUT_DEBITO_BANCARIO | Caso não informado, libera todas as formas de pagamento, caso informado libera apenas os modos informados                                                                                                                 | PrópriaClasse MoipPagamento                                     | Não                                 |
|  configurarBoleto($data, $logo, $info)                            | $data (YYYY-MM-DD)  $logo (url para a logo ou null para não usar logo no boleto)  $info (array, onde cada valor do array é uma linha de informações no boleto)                                                                                                           | Adiciona informações extra ao boleto como data de expirar, uma logo própria ou informações extras                                                                                                                 | PrópriaClasse MoipPagamento                                     | NÃO                                |
| getErro()                   | ---                                                                                               | Retorna mensagem de erro, caso não tenha sido possivel realizar a compra | string         | NÃO                              |




### Criando uma nova compra avançada

Como vimos na tabela acima, podemos ainda definir quais tipos de formas de pagamentos estarão liberadas assim como adicionar informações ao boleto:
``` php
require dirname(__FILE__).'/vendor/autoload.php';

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$urlPagamento = $moipPag->setID(uniqid())   //ID unico para a compra
                        ->setPreco(50.00)   //Preço da compra
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_BOLETO) //Libera forma de pagamento via Boleto
                        ->configurarBoleto('2017-03-01', 'http://site.com.br/logo.png', array('Linha 1', 'Linha 2')) //Informações do boleto
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_CARTAO) //Libera forma de pagamento via cartão
                        ->setDescricao('Descrição da Compra')
                        ->setVendedor('carloswgama@gmail.com') //Adiciona quem deverá receber o apagamento ao invés da conta vinculada a API
                        ->addComissao('carloswgama2@gmail.com', 10) //Adiciona outro vendedor que irá receber 10 reais de comissão do vendedor principal dessa venda
                        ->addComissao('carloswgama3@gmail.com', 10, TRUE) //Adiciona outro vendedor que irá receber 10% (5 reais) de comissão do vendedor principal dessa venda
                        ->pagar();

if (!$urlPagamento) die ($moipPag->getErro());

echo "URL para o checkout do moip: " . $urlPagamento;
```
---
## Checkout com Marketplace

Também é possível setar os produtos da venda, onde poderá ter produtos de vendedores diferentes. Os produtos que forem de outros vendedores, deverá ser informado o login da conta MoIP do vendedor que irá receber por aquele produto. 

Também é possível informar uma comissão em porcentgaem que irá reduzir dos valores de recebimento dos outros vendedores e dado ao vendedor principal. 
``` php
require dirname(__FILE__).'/vendor/autoload.php';

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
                        ->setDescricao('Compra Marketplace')
                        ->pagar();

if (!$urlPagamento) die ($moipPag->getErro());

echo "URL para o checkout do moip: " . $urlPagamento;
```

---
## Checkout transparente

### Pagando com Boleto (Checkout Transparente)
O comando é bastante semelhante ao Checktou no ambiente MoIP, porém aqui iremos gerar alguns scripts (javascripts) para realizar o processo todo no ambiente do cliente:

``` php
require dirname(__FILE__).'/vendor/autoload.php';

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->setPreco(50.00)   //Preço da compra
                        ->setDescricao('Descrição da Compra')
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_BOLETO) //Gera apenas scripts para o Boleto
						->configurarBoleto('2017-03-01', 'http://site.com.br/logo.png', array('Linha 1', 'Linha 2')) 
                        ->addVendedorSecundario('carloswgama2@gmail.com', 10) //Adiciona outro vendedor que irá receber 10 reais dessa venda
                        ->addVendedorSecundario('carloswgama3@gmail.com', 10, TRUE) //Adiciona outro vendedor que irá receber 10% (5 reais) dessa venda
                        ->getCheckoutTransparente();

?>
<?php echo $scripts['default'] ?>

<!-- PAGAR BOLETO -->
<h1>Boleto</h1> 
<button onclick="MoipPagarBoleto();">Imprimir Boleto</button>
<br/>
```
O método getCheckoutTransparente(), irá retornar um array contendo todos os scripts necessários para rodar o checkout transparente. 
Na sua forma mais básica basta chamar o valor 'default' do array, que já contem todos os scripts prontos. 

Para chamar o boleto basta chamar a função javascript: MoipPagarBoleto() que uma nova aba irá abrir com o boleto

*No ambiente Sandbox, ao invés de abrir o banco ou o boleto sempre irá abrir o ambiente de teste do MoIP* 

Além dos métodos citados na tabela anterior, para usar o checkout transparente haverão os seguintes metodos a mais disponíveis:

| Método                                              | Parametros                                                                                                    | Descrição                                                                                     | Retorna                                                                                                                                                                                                                                                                                                                                                             | Obrigatório                               |
|-----------------------------------------------------|---------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------|
| getCheckoutTransparente($extraSucesso, $extraFalha) | $extraSucesso (string) - código javascript extra adicionado a função caso o pedido seja executado com sucesso | Método responsável por gerar todos os javascripts usados para realizar o checkout transparente | um array contendo:  **default** - todos os scripts (abaixo) necessários;**scriptMoip** - Script que inicia o chamado ao Moip;  **scriptSucesso** - Função javascript chamada quando a requisição é executada com sucesso;  **scriptFalha** - Função javascript que é chamada quando o pedido da compra falha;  **scriptPagamentos** - retorna os javascripts que inicial o pedido da compra | SIM para compra com checkout transparente |
| getInstituicoesDebito()                             | ----                                                                                                          | Retorna a lista de instituições permitidas para debito bancário                               | Array com instituições                                                                                                                                                                                                                                                                                                                                              | NÃO                                       |


Entre os javascripts gerados pelo método temos:
- **MoipFuncaoSucesso(data)**	-> Função chamada caso a requisição seja realizada com sucesso. Na variável data é enviado alguns informações pelo MoIP como Status da compra caso pago com o cartão
- **MoipFuncaoFalha(data)**		-> Função chamada caso houve alguma falha ao realizar o pedido como cartão inválido. Na variável data é enviado algumas informações pelo MoIP como Mensagem e código da falha 
- **MoipPagarBoleto()** 			-> Inicia o checkout transparente para gerar o boleto
- **MoipPagarDebitoBancario()**	-> Inicia o checkout transparente para compra por debito bancário
- **MoipPagarCartao()**			-> Inicia o checkout transparente para compra com cartão de crédito

### Pagando com Debito Bancário (Checkout Transparente)

Para realizar o pagamento por Debito Bancário com checkout transparente, é preciso informar qual será o banco que irá ser realizado o deposito. Os bancos disponíveis, podem ser pegos através do método getInstituicoesDebito(). 
O bancos disponíveis são:
- Banco do Brasil (BancoDoBrasil)
- Bradesco (Bradesco)
- Itau (Itau)
- Barinsul (Barinsul)

Para selecionar o banco, o campo input ou select deverá conter o id "**moip_debito_instituicao**" que será usado pela função javascript **MoipPagarDebitoBancario()**:

``` php
require dirname(__FILE__).'/vendor/autoload.php';

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->addProduto('Caderno', 20) //Produto do vendedor principal
                        ->addProduto('Storage 500GB', 300.00, 'informatica@gmail.com') //produto de 'informatica@gmail.com'
                        ->setComissaoVendedorPrincipal(10) //(Opcional) 10% do vendedor informatica@gmail.com será dado ao vendedor principal
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_DEBITO_BANCARIO) //Libera forma de pagamento
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
```

### Pagando com Cartão (Checkout Transparente)

Para realizar o pagamento por cartão de crédito com checkout transparente o processo é semelhante aos demais, porém para iniciar o pagamento usamos a função javascript **MoipPagarCartao()**

Essa função irá buscar por 8 campos que deverão estar com os seguintes id's:
- **moip_cartao_titular_nome** => Nome do títular do cartão igual a como está no cartão
- **moip_cartao_titular_nascimento** => Data de nascimento do títular do Cartão (DD/MM/YYYY)
- **moip_cartao_titular_telefone** => Telefone do Títular do Cartão. Ex: (99)99999-9999
- **moip_cartao_titular_cpf** => CPF do títular do cartão (999.999.999-99)
- **moip_cartao_parcelas** => Em quantas parcelas será a compra (1-12 acima de 3 há juros)
- **moip_cartao_numero** => Número do cartão
- **moip_cartao_validade** => Período que o cartão expira (MM/YYYY). Ex: 07/2018
- **moip_cartao_codigo_seguranca** => Código de segurança CVV que vem atrás do cartão 

Exemplo:
``` php
require dirname(__FILE__).'/vendor/autoload.php';
use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->setPreco(10.00)   //Preço da compra
                        ->setDescricao('Descrição da Compra')
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
```

### Mais de uma opção de Pagamento com checkout transparente

Para dar ao cliente mais de uma opção de compra usando o checkout transparente, basta seleciona a forma de pagamento (Caso nenhuma seja informada, todas as 3 estarão disponíveis) e chamar a função javascript relacionada ao tipo de pagamento:
- **MoipPagarBoleto()** 		-> Boleto
- **MoipPagarDebitoBancario()**	-> Debito Bancário
- **MoipPagarCartao()**			-> Cartão de Crédito

*OBS: Lembrar de ver quais campos os pagamentos por Debito Bancário e Cartão de Crédito necessitam*

### Personalizando scripts de pagamentos

As funções usadas pelo MoIP que são chamadas quando houve uma falha ou sucesso na requisição da compra, podem facilmente receber scripts extras simplesmente passando os scripts como parametros no método getCheckoutTransparente():

``` php
require dirname(__FILE__).'/vendor/autoload.php';

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$extraScriptSucesso = 'alert(data.Mensagem);';
$extraScriptFalha = 'alert(data.Mensagem);';

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->setPreco(10.00)   //Preço da compra
                        ->setDescricao('Descrição da Compra')
                        ->getCheckoutTransparente($extraScriptSucesso, $extraScriptFalha);
?>
<?php echo $scripts['default'] ?>
 
 [...]

<button onclick="MoipPagarBoletoPersonalizado();">Imprimir Boleto</button>
<button onclick="MoipPagarDebitoBancario();">Pagar com Debito Bancário</button>
<button onclick="MoipPagarCartao();">Pagar com Cartão de Crédito</button>
```

Caso deseje alterar os métodos MoipPagarBoletoPersonalizado(), MoipPagarDebitoBancario() e MoipPagarCartao(), ao invés de chamar o script default retornado através do método getCheckoutTransparente(), usar os scripts separadamente:
- **["scriptMoip"]**                      -> Obrigatório, é ele quem inica a comunicação com o MoIP
- **["scriptSucesso"]**                   -> Gera a função MoipFuncaoSucesso(data), caso não deseje criar sua própria, basta chamar esse campo
- **["scriptFalha"]**                     -> Gera a função MoipFuncaoFalha(data), caso não deseje criar sua própria, basta chamar esse campo
- **["scriptPagamentos"]["boleto"]**      -> Gera a função MoipPagarBoleto(), caso não deseje criar sua própria, basta chamar esse campo
- **["scriptPagamentos"]["debito_bancario"]**      -> Gera a função MoipPagarDebitoBancario(), caso não deseje criar sua própria, basta chamar esse campo
- **["scriptPagamentos"]["cartao"]**      -> Gera a função MoipPagarCartao(), caso não deseje criar sua própria, basta chamar esse campo

Exemplo:
``` php
require dirname(__FILE__).'/vendor/autoload.php';

use CWG\Moip\MoipPagamento;

$token =  'J27IIMSM0MWSQJIXT1MDUTHZFBWMV4W2';
$key = 'IEVEAUWW0E4GX6FPYIEUHC7YTJEGOFNYXCEPKAER';
$sandbox = true;

$moipPag = new MoipPagamento($token, $key, $sandbox);

$scripts = $moipPag->setID(uniqid())   //ID unico para identificar a compra
                        ->setPreco(10.00)   //Preço da compra
                        ->setDescricao('Descrição da Compra')
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_DEBITO_BANCARIO) //Libera forma de pagamento 
                        ->addFormaPagamento(MoipPagamento::CHECKOUT_BOLETO) //Libera forma de pagamento 
                        ->getCheckoutTransparente();

?>
<!-- OBRIGATORIO scriptMoip -->
<?php echo $scripts['scriptMoip'] ?>
<!-- CHAMANDO MoipFuncaoSucesso(data) PADRÃO-->
<?php echo $scripts['scriptSucesso'] ?>
<!-- CHAMANDO MoipFuncaoFalha(data) PADRÃO-->
<?php echo $scripts['scriptFalha'] ?>
<!-- CHAMANDO MoipPagarBoleto() PADRÃO-->
<?php echo $scripts['scriptPagamentos']['boleto'] ?> 
<!-- CRIANDO MEU PRÓPRIO MÉTODO MoipPagarDebitoBancarioPersonalizado() com o banco já definido -->
<script type='text/javascript'>
    function MoipPagarDebitoBancarioPersonalizado() {
        var settings = { 
            "Forma": "DebitoBancario" , 
            "Instituicao": "BancoDoBrasil"
        } 
        MoipWidget(settings);
    }
</script>

<!-- PAGAR BOLETO -->
<h1>Boleto chamando script separado</h1> 
<button onclick="MoipPagarBoletoPersonalizado();">Imprimir Boleto</button>
<br/>

<!-- PAGAR DEBITO BANCARIO -->
<h1>Debito Bancário com Script Personalizado</h1> 
<button onclick="MoipPagarDebitoBancarioPersonalizado();">Pagar com Debito Bancário</button>
```
*Para mais informações de como criar seu próprio javascript, pode olhar a documentação do MoIP de Pagamentos via JavaScripts*
[Javascript de Pagamento MOIP](https://labs.moip.com.br/referencia/javascript_de_pagamento/)

---
# NASP

O NASP (Notificação de Alteração de Status de Pagamento) é a notificação enviada para o link configurado na sua conta Moip. A requisição é enviada POST.

A classe MoipNASP ajuda a traduzir alguns códigos enviados pelo MoIP como:

Exemplo:
``` php
require dirname(__FILE__).'/vendor/autoload.php';
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
```

### MoipNASP::getClassificacao($_POST['classificacao'])
Retorna uma descrição do que o código 'classificacao' enviado significa. Normalmente é enviado em compras cancelas.

### MoipNASP::getStatusPagamento($_POST['status_pagamento'])
Retorna uma descrição do que o código 'status_pagamento' enviado significa. As opções podem ser:
- Autorizado
- Iniciado
- Boleto Impresso
- Concluído
- Cancelado
- Em Análise
- Estornado
- Reembolsado

### MoipNASP::getFormaPagamento($_POST['forma_pagamento'])
Retorna uma descrição do que o código 'forma_pagamento' enviado significa, podendo ser o saldo da carteira moip (checkout no ambient moip), cartões, boleto ou debitos.


### MoipNASP::formateNASP($_POST)
Formata todo o post enviado documentando os códigos enviados como nos método acima.

---
*Links extras:*

[Documentação Oficial do Moip V1](https://labs.moip.com.br/integracao/visao-geral/)
[Conta do Moip V1](https://www.moip.com.br/MainMenu.do?method=login)
[Conta Sandbox do Moip V1](https://desenvolvedor.moip.com.br/sandbox/MainMenu.do?method=home)

---
**Autor:**  Carlos W. Gama *(carloswgama@gmail.com)*
Licença: MIT
Livre para usar, modificar como desejar e destribuir como quiser