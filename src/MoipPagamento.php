<?php

namespace CWG\Moip;

require_once(dirname(__FILE__).'/lib/Moip.php');

/**
* @author Carlos W. Gama
* @version 1.1.0
* @license https://opensource.org/licenses/MIT MIT
* @see https://github.com/CarlosWGama/php-moip
*/
class MoipPagamento {

    /**
    * Token da conta do Moip. Pode ser acessado através do menu Ferramentas >> API MoiP >> Chaves de Acesso
    * @var string
    * @access private
    */
    private $token = '';
    
    /**
    * Chave da conta do Moip. Pode ser acessado através do menu Ferramentas >> API MoiP >> Chaves de Acesso
    * @var string
    * @access private
    */
    private $key = '';

    /**
    * ID unico da compra caso exista
    * @var mix (string ou inteiro ou false para não ter)
    * @access private
    */
    private $ID = false;

    /**
    * Descrição da compra
    * @var string
    * @access private
    */
    private $descricao;

    /**
    * Preço total da compra
    * @var float
    * @access private
    */
    private $precoTotal;

    /**
    * Classe Moip
    * @var Moip
    * @access private
    */
    private $moip = null;

    /**
    * Array com as formas de pagamentos disponíveis
    * @var SELF::CHECKOUT_BOLETO|SELF::CHECKOUT_CARTAO|SELF::CHECKOUT_DEBITO_BANCARIO
    */
    private $pagamentosDisponiveis = array();

    /**
    * Usa o modo Sandbox ou em produção
    * @var boolean
    * @access private
    */
    private $isSandbox = false;

    /**
    * Configurações do Boleto
    * @var array('data', 'logo', 'informacaoes')
    * @access private
    */
    private $configBoleto = array();


    /**
    * Login da pessoa que irá receber o valor, caso não envie para o dono da API
    * @var string
    * @access private
    */
    private $contaPrincipal = '';

    /**
    * Outras contas que irão receber também parte do pagamento
    * @var array
    * @access private
    */
    private $contasSecundarias = array();

    /**
    * Comissão que será retirado dos vendedores secundários para o principal
    * @var float
    * @access private
    */
    private $comissaoVendedorPrincipal = 0;

    /** Receber secundário deve receber um valor fixo **/
    const VALOR_FIXO = false;
    /** Receber secundário deve receber um valor da porcentagem **/
    const VALOR_PORCENTAGEM = true;

    /**
    * Array com o nome dos javascripts rodados no checkout getCheckoutTransparente
    * @var array
    * @access private
    */
    private $scriptsCheckoutTransparente = array(
        'sucesso'           => 'funcaoSucesso',
        'falha'             => 'funcaoFalha',
        'boleto'            => 'pagarBoleto',
        'cartao'            => 'pagarCartao',
        'debito_bancario'   => 'pagarDebitoBancario'
    );

    /**
    * Mensagem de erro caso não consiga completar a compra
    * @var string
    * @access private
    */
    private $erro = '';

    /** GERA O CHECKOUT TRANSPARENTE APENAS PARA BOLETO  **/
    const CHECKOUT_BOLETO           = 1;
    /** GERA O CHECKOUT TRANSPARENTE APENAS PARA CARTÃO  **/
    const CHECKOUT_CARTAO           = 2;
    /** GERA O CHECKOUT TRANSPARENTE APENAS PARA DEBITO BANCÁRIO  **/
    const CHECKOUT_DEBITO_BANCARIO  = 3;

    public function __construct($token = '', $key = '', $isSandbox = false) {
        $this->moip = new \Moip();
        
        if (!empty($token) || !empty($key))
            $this->setCredenciais($token, $key);
        
        $this->setSandbox($isSandbox);
    }

    /**
    * Define um ID unico para a compra. Não pode repetir
    * @uses $moipPag->setID(false);
    * @uses $moipPag->setID('CWG00001');
    * @uses $moipPag->setID(1);
    * @param $id mix
    * @return MoipPagamento
    */
    public function setID($id) {
        $this->ID = $id;
        return $this;
    }

    /**
    * Seta a credêncial de acesso ao MoiP
    * @uses $moipPag->setCredenciais('12345678', '1234567');
    * @uses $moipPag->setCredenciais(array('token' => '12345678', 'key' => '1234567'));
    * @param $token string|array
    * @param $key string
    * @return MoipPagamento
    */
    public function setCredenciais($token, $key = '') {

        //Encaminhar o array
        if (is_array($token) && isset($token['token']) && isset($token['key'])) 
            $this->setCredencais($token['token'], $token['key']);
        else { //Seta a credenial
           $this->token = $token;
           $this->key = $key;
        }

        return $this;
    }


    /**
    * Seta o ambiente aonde a compra será realizada
    * @param $isSandbox boolean
    * @return MoipPagamento
    */
    public function setSandbox($isSandbox) {
        $this->isSandbox = $isSandbox;
        return $this;
    }

    /**
    * Adiciona um produto a compra
    * @param $produto string
    * @param $valor float
    * @param $login string
    * @since 1.2.0
    * @return MoipPagamento
    */
    public function addProduto($produto, $valor, $login = '') {

        if (!empty($login)) {
            $valor = (isset($this->contasSecundarias[$login]) ? $this->contasSecundarias[$login]['valor'] + $valor : $valor);
            $razao = (isset($this->contasSecundarias[$login]) ? $this->contasSecundarias[$login]['razao'] .', '. $produto : $produto); 
            $this->contasSecundarias[$login] = [
                'razao'     => $razao,
                'login'     => $login,
                'valor'     => $valor,
                'forma'     => false,
                'taxaMoip'  => true
            ];
        }
        $this->descricao = (empty($this->descricao) ? $produto : $this->descricao . ', ' . $produto); 
        $this->precoTotal += $valor;
        return $this->setPreco($this->precoTotal);
    }

    /**
    * Adiciona a comissão do vendedor principal, reduzindo dos vendedores secundários
    * @param $valor float
    * @since 1.2.0
    * @return MoipPagamento
    */
    public function setComissaoVendedorPrincipal($porcentagem) {
        if ($porcentagem > 100) $porcentagem = 100;
        elseif ($porcentagem < 0) $porcentagem = 0;
        $this->comissaoVendedorPrincipal = $porcentagem;
        return $this;
    }

    /**
    * Define o preço da compra
    * @param $precoTotal float
    * @return MoipPagamento
    */
    public function setPreco($precoTotal) {
        $this->precoTotal = number_format($precoTotal, 2, '.', '');
        return $this;
    }

    /**
    * Seta a descrição da compra
    * @param $descricao string
    * @return MoipPagamento
    */
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
        return $this;
    }
    
    /**
    * Seta o vendedor que irá receber a compra
    * @param $login string
    * @return MoipPagamento
    * @since 1.1.0
    */
    public function setVendedor($login) {
        $this->contaPrincipal = $login;
        return $this;
    }

    /**
    * Adiciona vendedores secundários que irão ganhar comissões.
    * @param $login string (Login do segundo vendedor)
    * @param $valor string (Valor que será dado ao vendedor)
    * @param $forma FALSE = Valor fixo | TRUE = Porcentagem
    * @param $taxaMoip TRUE = Paga taxa do MoIP | false = Não paga taxa do MoIP
    * @return MoIPPagamento
    * @since 1.1.0
    */
    public function addComissao($login, $valor, $forma = false, $taxaMoip = false) {
        $this->contasSecundarias[] = [
            'razao'     => 'Comissão de Venda',
            'login'     => $login,
            'valor'     => $valor,
            'forma'     => $forma,
            'taxaMoip'  => $taxaMoip
        ];
        return $this;
    }

    /**
    * Adiciona informações ao boleto
    * @uses $moipPag->configurarBoleto('2017-01-01');
    * @uses $moipPag->configurarBoleto('2017-01-01', 'http://site.com.br/logo.png', array('Linha 1', 'Linha 2', 'Linha 3'));
    * @param $data (YYYY-MM-DD) Data que irá expirar o boleto
    * @param $logo (url) é recomendado que a imagem tem uma dimensão de (75x40)
    * @param $infos (array) um array, onde cada valor do array representa uma linha
    */
    public function configurarBoleto($data, $logo = null, $infos = array()) {
        $this->configBoleto = array(
            'data'          => $data,
            'logo'          => $logo,
            'informacoes'   => $infos
        );
        return $this;
    }
 
    /**
    * Seta o ambiente aonde a compra será realizada
    * @uses $moipPag->addFormaPagamento(MoipPagamento::CHECKOUT_BOLETO);
    * @uses $moipPag->addFormaPagamento(MoipPagamento::CHECKOUT_CARTAO);
    * @uses $moipPag->addFormaPagamento(MoipPagamento::CHECKOUT_DEBITO_BANCARIO);
    * @uses $moipPag->addFormaPagamento(MoipPagamento::CHECKOUT_BOLETO)->addFormaPagamento(MoipPagamento::CHECKOUT_CARTAO);;
    * @param $forma CHECKBOX
    * @return MoipPagamento
    */
    public function addFormaPagamento($forma) {
        $this->pagamentosDisponiveis[] = $forma;
        return $this;
    }

    private function prepararCompra() {
        $this->moip->setCredential(array('token' => $this->token, 'key'   => $this->key));
        $this->moip->setEnvironment($this->isSandbox);
        $this->moip->setUniqueID($this->ID);
        $this->moip->setValue($this->precoTotal);
        $this->moip->setReason($this->descricao);

        //Troca a pessoa que irá receber o pagamento
        if (!empty($this->contaPrincipal)) 
            $this->moip->setReceiver($this->contaPrincipal);

        //Adiciona vendedores secundários
        if (!empty($this->contasSecundarias)) {
            foreach ($this->contasSecundarias as $conta) {
                //reduzir     
                $comissaoVendedorPrincipal = ($conta['valor'] * $this->comissaoVendedorPrincipal) / 100;
                $conta['valor'] -= $comissaoVendedorPrincipal;
                $conta['valor'] = number_format($conta['valor'], 2, '.', '');
                $this->moip->addComission($conta['razao'], $conta['login'], $conta['valor'], $conta['forma'], $conta['taxaMoip']);
            }
        }

        //Configuração de formas de pagamento
        foreach ($this->pagamentosDisponiveis as $forma) {
            switch($forma) {
                case SELF::CHECKOUT_BOLETO: $this->moip->addPaymentWay('billet'); break;
                case SELF::CHECKOUT_CARTAO: $this->moip->addPaymentWay('creditCard'); break;
                case SELF::CHECKOUT_DEBITO_BANCARIO: $this->moip->addPaymentWay('debitCard'); break;
            }
        }

        //Configuração de Boleto
        if (!empty($this->configBoleto))
            $this->moip->setBilletConf($this->configBoleto['data'], true, $this->configBoleto['informacoes'], $this->configBoleto['logo']);
      
        $this->moip->validate('Basic');
        $this->moip->send();

        @$error = $this->moip->getAnswer()->error;
        if (is_object($this->moip->getAnswer()) && empty($error)) 
            return $this->moip->getAnswer();
        
        $this->erro = $this->moip->getAnswer(); 
        
        if (!empty($error)) $this->erro = $error;

        return false;
        
    }

    /**
    * Através do Checkout do Moip
    *
    */
    public function pagar() {
        try {
            $answer = $this->prepararCompra();
            if (!$answer) return false;

            return $answer->payment_url;
        } catch (exception $e) {
            $this->erro = $this->moip->getAnswer(); 
            return false;
        }
    }

    /**
    * Retorna as instituições que aceitam debito em conta Deposito
    * @return array
    */
    public function getInstituicoesDebito() {
        return array(
            'BancoDoBrasil'     => 'Banco do Brasil',
            'Bradesco'          => 'Bradesco',
            'Itau'              => 'Itau',
            'Banrisul'          => 'Banrisul'
        );
    }

    /**
    * Pagamento através do Checkout transparente (retorna arrais com link)
    */
    public function getCheckoutTransparente($extraScriptSucesso = '', $extraScriptFalha = '') {
        $tokenTransacao = null;
        
        //================== CRIA O PEDIDO DE COMPRA ==================//
        try {
            $answer = $this->prepararCompra();
            if (!$answer) return false;

            $tokenTransacao = $answer->token;
        } catch(Exception $e) {
            $this->erro = $this->moip->getAnswer(); 
            return false;
        }

        //================== GERA O SCRIPT DE COMUNICAÇÂO COM O MOIP ==================//
        $scriptMoip = '<div id="MoipWidget" data-token="' . $tokenTransacao . '" callback-method-success="MoipFuncaoSucesso" callback-method-error="MoipFuncaoFalha"></div>';
        if ($this->isSandbox)   
            $scriptMoip .= '<script type="text/javascript" src="https://desenvolvedor.moip.com.br/sandbox/transparente/MoipWidget-v2.js" charset="ISO-8859-1"></script>';
        else
            $scriptMoip .= '<script type="text/javascript" src="https://www.moip.com.br/transparente/MoipWidget-v2.js" charset="ISO-8859-1"></script>';
        
        //================== GERA O SCRIPT QUE É CHAMADO CASO O PEDIDO SEJA REALIZADO COM SUCESSO ==================//
        $scriptSucesso = "<script type='text/javascript'>\n" .
                                "function MoipFuncaoSucesso(data){ \n" .
                                    "if (data.url && !data.Status) { window.open(data.url); } \n" .    
                                    "console.log(\"Sucesso:\" + JSON.stringify(data)); \n" .
                                    $extraScriptSucesso .
                                "}" .
                        "</script>";
         //================== GERA O SCRIPT QUE É CHAMADO CASO O PEDIDO SEJA REALIZADO COM FALHA ==================//
        $scriptFalha = "<script type='text/javascript'>\n" .
                                "function MoipFuncaoFalha(data){ \n" .   
                                    "console.log(\"Falha:\" + JSON.stringify(data)); \n" .
                                    $extraScriptFalha .
                                "}" .
                        "</script>";
         //================== GERA AS FUNÇõES DE CHAMADA DE PAGAMENTO ==================//           
        $scriptsPagamentos = array();

        //Boleto
        if (empty($this->pagamentosDisponiveis) || in_array(SELF::CHECKOUT_BOLETO, $this->pagamentosDisponiveis))
            $scriptsPagamentos['boleto'] = "<script type='text/javascript'>\n" .
                                 "function MoipPagarBoleto() { \n" . 
                                    "var settings = { \"Forma\": \"BoletoBancario\" } \n".
                                    "MoipWidget(settings); \n" .
                                  "}" .
                                "</script>";

        //Debito
        if (empty($this->pagamentosDisponiveis) || in_array(SELF::CHECKOUT_DEBITO_BANCARIO, $this->pagamentosDisponiveis))
            $scriptsPagamentos['debito_bancario'] = "<script type='text/javascript'>\n" .
                                 "function MoipPagarDebitoBancario() { \n" . 
                                    "var settings = { \"Forma\": \"DebitoBancario\" , \"Instituicao\": document.getElementById('moip_debito_instituicao').value} \n".
                                    "MoipWidget(settings); \n" .
                                  "}" .
                                "</script>";
        //Cartão
        if (empty($this->pagamentosDisponiveis) || in_array(SELF::CHECKOUT_CARTAO, $this->pagamentosDisponiveis))
            $scriptsPagamentos['cartao'] =  "<script type='text/javascript'>\n" .
                                "function MoipPagarCartao() { \n" . 
                                    "var settings = { \n" .
                                        "\"Forma\": \"CartaoCredito\", \n" .
                                        "\"Instituicao\": \"\", \n" .
                                        "\"Parcelas\":  document.getElementById('moip_cartao_parcelas').value, \n" .
                                        "\"Recebimento\": \"AVista\", \n" .
                                        "\"CartaoCredito\": { \n" .
                                            "\"Numero\": document.getElementById('moip_cartao_numero').value, \n" .
                                            "\"Expiracao\": document.getElementById('moip_cartao_validade').value, \n" .
                                            "\"CodigoSeguranca\": document.getElementById('moip_cartao_codigo_seguranca').value, \n" .
                                            "\"Portador\": { \n" .
                                                "\"Nome\":document.getElementById('moip_cartao_titular_nome').value, \n" .
                                                "\"DataNascimento\": document.getElementById('moip_cartao_titular_nascimento').value, \n" .
                                                "\"Telefone\": document.getElementById('moip_cartao_titular_telefone').value, \n" .
                                                "\"Identidade\": document.getElementById('moip_cartao_titular_cpf').value \n" .
                                            "} \n" .
                                        "} \n" .
                                    "} \n" .
                                    "MoipWidget(settings); \n" .
                                  "}" .
                                "</script>";


        $scripts = $scriptMoip."\n".$scriptSucesso."\n".$scriptFalha.implode("\n", $scriptsPagamentos);

        return array(
            'default'           => $scripts,
            'scriptMoip'        => $scriptMoip,
            'scriptSucesso'     => $scriptSucesso,
            'scriptFalha'       => $scriptFalha,
            'scriptPagamentos'  => $scriptsPagamentos
        );
    }

    /**
    * Retonra mensagem do erro ocorrido
    * @return string
    */
    public function getErro() {
        return $this->erro;
    }

}