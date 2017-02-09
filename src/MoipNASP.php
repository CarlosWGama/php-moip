<?php

namespace CWG\Moip;

require_once(dirname(__FILE__).'/lib/Moip.php');

/**
* @author Carlos W. Gama
* @version 1.0.0
* @license https://opensource.org/licenses/MIT MIT
* @see https://github.com/CarlosWGama/php-moip
*/
class MoipNASP {

    /**
    * Definição das possíveis formas de pagamento e seus códigos
    */
    const FORMAS_PAGAMENTO = array(
            1   => array('cod' => 1,  'descricao' => 'Saldo na Carteira MoIP'),
            3   => array('cod' => 3,  'descricao' => 'Bandeira de cartão de crédito Visa'),
            5   => array('cod' => 5,  'descricao' => 'Bandeira de cartão de crédito Mastercard'),
            6   => array('cod' => 6,  'descricao' => 'Bandeira de cartão de crédito Diners'),
            7   => array('cod' => 7,  'descricao' => 'Bandeira de cartão de crédito American Express'),
            8   => array('cod' => 8,  'descricao' => 'Débito em conta Banco do Brasil'),
            13  => array('cod' => 13, 'descricao' => 'Débito em conta banco Itau'),
            22  => array('cod' => 22, 'descricao' => 'Débito em conta banco Bradesco'),
            73  => array('cod' => 73, 'descricao' => 'Boleto bancário gerado pela instituição financeira Bradesco'),
            75  => array('cod' => 75, 'descricao' => 'Bandeira de cartão de crédito Hipercard'),
            76  => array('cod' => 76, 'descricao' => 'Cobrança em conta Oi Paggo'),
            88  => array('cod' => 88, 'descricao' => 'Bébito em conta banco Banrisul'),
            93  => array('cod' => 93, 'descricao' => 'Bandeira de cartão de crédito Elo'),
            94  => array('cod' => 94, 'descricao' => 'Bandeira de cartão de crédito Hiper')
        );

    /**
    * Definição dos possíveis status de pagamento e seus códigos
    */
    const STATUS_PAGAMENTO = array(
        1 => array(
                'cod'       => 1,
                'status'    => 'Autorizado',
                'descricao' => 'Pagamento autorizado pelo pagador, porém ainda não creditado para o recebedor em razão do floating'
            ),  
        2 => array(
                'cod'       => 2,
                'status'    => 'Iniciado',
                'descricao' => 'Pagamento foi iniciado, mas não existem garantias de que será finalizado'
            ),   
        3 => array(
                'cod'       => 3,
                'status'    => 'Boleto Impresso',
                'descricao' => 'Pagamento ainda não foi confirmado, porém boleto bancário foi impresso e pode ter sido pago (não existem garantias de que será pago)'
            ),   
        4 => array(
                'cod'       => 4,
                'status'    => 'Concluido',
                'descricao' => 'Pagamento foi concluído, dinheiro debitado do pagador e creditado para o recebedor'
            ),   
        5 => array(
                'cod'       => 5,
                'status'    => 'Cancelado',
                'descricao' => 'Pagamento foi cancelado por quem estava pagando'
            ),   
        6 => array(
                'cod'       => 6,
                'status'    => 'Em Analise',
                'descricao' => 'Pagamento autorizado pelo pagador, mas está em análise e não tem garantias de que será autorizado'
            ),   
        7 => array(
                'cod'       => 7,
                'status'    => 'Estornado',
                'descricao' => 'Pagamento foi concluído, dinheiro creditado para o recebedor, porém estornado para o cartão de crédito do pagador'
            ),
        9 => array(
                'cod'       => 9,
                'status'    => 'Reembolsado',
                'descricao' => 'Pagamento foi concluído, dinheiro creditado para o recebedor, porém houve o reembolso para a Carteira Moip do pagador'
            )   
    );

    /**
    * Informações extras da requisição
    */
    const CLASSIFICACAO = array(
        1   => array(
                'cod'           => 1,
                'ocorrido'      => 'Dados inválidos',
                'descricao'     => 'Os dados digitados pelo Comprador estão incorretos: Número do Cartão, CVV ou Data de Vencimento.'
            ),
        2   => array(
                'cod'           => 2,
                'ocorrido'      => 'Falha na comunicação com o Banco Emissor',
                'descricao'     => 'Houve uma falha na comunicação com o Banco, o Comprador deve fazer uma nova tentativa.'
            ),
        3  => array(
                'cod'           => 3,
                'ocorrido'      => 'Política do Banco Emissor',
                'descricao'     => 'Transação não autorizada pelas políticas do Banco Emissor. Dentre os possíveis motivos estão: análise de risco, comportamento de compra ou outro motivo semelhante. O Comprador deve entrar em contato com o Banco Emissor antes de tentar novamente.'
            ),
        4  => array(
                'cod'           => 4,
                'ocorrido'      => 'Cartão vencido',
                'descricao'     => 'A validade do Cartão foi excedida.'
            ),
        5  => array(
                'cod'           => 5,
                'ocorrido'      => 'Transação não autorizada',
                'descricao'     => 'Banco Emissor não autorizou a compra. Um dos motivos possíveis é a falta de limite do Cartão para concluir o pagamento.'
            ),
        6  => array(
                'cod'           => 6,
                'ocorrido'      => 'Transação duplicada',
                'descricao'     => 'O pagamento já foi realizado por outra transação.'
            ),
        7  => array(
                'cod'           => 7,
                'ocorrido'      => 'Política do Moip',
                'descricao'     => 'A transação possuía um risco muito elevado e, após os procedimentos de análise, ela foi negada.'
            ),
        8  => array(
                'cod'           => 8,
                'ocorrido'      => 'Solicitado pelo Comprador',
                'descricao'     => 'O Comprador solicitou o cancelamento da transação diretamente ao Moip.'
            ),
        9  => array(
                'cod'           => 9,
                'ocorrido'      => 'Solicitado pelo Vendedor',
                'descricao'     => 'O Vendedor solicitou o cancelamento da transação diretamente ao Moip'
            ),
        10  => array(
                'cod'           => 10,
                'ocorrido'      => 'Transação não processada',
                'descricao'     => 'Houve uma falha na comunicação do Moip.'
            ),
        11  => array(
                'cod'           => 11,
                'ocorrido'      => 'Desconhecido',
                'descricao'     => 'Houve uma falha desconhecida no Banco Emissor.'
            ),
        12  => array(
                'cod'           => 12,
                'ocorrido'      => 'Política de segurança do Banco Emissor',
                'descricao'     => 'O Cartão foi negado e não será possível concluir a compra com este Cartão.'
            ),
        13  => array(
                'cod'           => 13,
                'ocorrido'      => 'Valor inválido',
                'descricao'     => 'A transação possui um valor inválido para o Banco Emissor: valor total da transação está abaixo do mínimo (menor que 1 real); valor da parcela da transação está abaixo do mínimo (5 reais); Valor total da transação é muito alto (exemplo R$999.999,00).'
            ),
        14  => array(
                'cod'           => 14,
                'ocorrido'      => 'Política de segurança do Moip',
                'descricao'     => 'O Cartão foi negado e não será possível concluir a compra com este Cartão.'
            )
    );

    /**
    * Retorna uma descrição  da forma de pagamento
    * @param $id código forma_pagamento do NASP
    * @return string
    */
    public static  function getFormaPagamento($id) {
        return SELF::FORMAS_PAGAMENTO[$id]; 
    }

     /**
    * Retorna uma descrição do status do pagamento
    * @param $id código status_pagamento do NASP
    * @return string
    */
    public static function getStatusPagamento($id) {
        return SELF::STATUS_PAGAMENTO[$id];
    }

    /**
    * Retorna uma descrição da classificação da falha
    * @param $id código classificacao do NASP
    * @return string
    */
    public static  function getClassificacao($id) {
        return SELF::CLASSIFICACAO[$id]; 
    }

    /**
    * Retorna o NASP formatado com todas as informações e códigos
    * @param $nasp array
    * @return array
    */
    public static function formateNASP($nasp) {
        $nasp['forma_pagamento'] = SELF::getFormaPagamento($nasp['forma_pagamento']);

        $nasp['status_pagamento'] = SELF::getStatusPagamento($nasp['status_pagamento']);

        if ($nasp['classificacao']) $nasp['classificacao'] = SELF::CLASSIFICACAO($nasp['classificacao']);

        return $nasp;
    }
}