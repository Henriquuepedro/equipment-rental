<?php

namespace App\Exceptions;

use Exception;

class MercadoPagoException extends Exception
{
    private object $payment;

    #Setters and Getters
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    public function getPayment(): object
    {
        return $this->payment;
    }

    #Verify transaction
    public function verifyTransaction(): array
    {
        if ($this->getPayment()->status == 'approved' || $this->getPayment()->status == 'in_process' || $this->getPayment()->status == 'pending') {
            return [
                'class' => 'success',
                'message'=>$this->getStatus()
            ];
        }
        elseif ($this->getPayment()->status == "rejected") {
            return [
                'class' => 'error',
                'message'=>$this->getStatus()
            ];
        } else {
            return [
                'class' => 'error',
                'message'=>$this->getStatus()
            ];
        }
    }

    #Get Status
    public function getStatus(): string
    {
        $qr_code_base64 = $this->getPayment()->point_of_interaction->transaction_data->qr_code_base64 ?? '';
        $qr_code = $this->getPayment()->point_of_interaction->transaction_data->qr_code ?? '';
        $external_resource_url = $this->getPayment()->transaction_details->external_resource_url ?? '';
        $statement_descriptor = $this->getPayment()->statement_descriptor ?? '';
        $dateOfExpiration = formatDateInternational($this->getPayment()->date_of_expiration, 'd/m H:i');

        $status=[
            'accredited'                            => "Seu pagamento foi aprovado! Você verá o nome $statement_descriptor na sua fatura de cartão de crédito.",
            'pending_contingency'                   => 'Estamos processando o pagamento. Em até 2 dias úteis informaremos por e-mail o resultado.',
            'pending_waiting_payment'               => "Boleto gerado! Assim que for compensado, avisaremos por e-mail.<br/><a class='btn btn-primary mt-3' href='$external_resource_url' target='_blank'><i class='fas fa-print'></i> Imprimir Boleto</a>",
            'pending_review_manual'                 => 'Estamos processando o pagamento. Em até 2 dias úteis informaremos por e-mail se foi aprovado ou se precisamos de mais informações. Fique atento no e-mail cadastrado',
            'cc_rejected_bad_filled_card_number'    => 'Confira o número do cartão.',
            'cc_rejected_bad_filled_date'           => 'Confira a data de validade.',
            'cc_rejected_bad_filled_other'          => 'Confira os dados.',
            'cc_rejected_bad_filled_security_code'  => 'Confira o código de segurança.',
            'cc_rejected_blacklist'                 => 'Não conseguimos processar seu pagamento.',
            'cc_rejected_call_for_authorize'        => 'Você deve autorizar o pagamento do valor ao Mercado Pago.',
            'cc_rejected_card_error'                => 'Não conseguimos processar seu pagamento.',
            'cc_rejected_duplicated_payment'        => 'Você já efetuou um pagamento com esse valor. Caso precise pagar novamente, utilize outro cartão ou outra forma de pagamento.',
            'cc_rejected_high_risk'                 => 'Seu pagamento foi recusado. Escolha outra forma de pagamento. Recomendamos meios de pagamento em dinheiro.',
            'cc_rejected_insufficient_amount'       => 'O cartão possui saldo insuficiente.',
            'cc_rejected_invalid_installments'      => 'O cartão não processa pagamentos parcelados.',
            'cc_rejected_max_attempts'              => 'Você atingiu o limite de tentativas permitido. Escolha outro cartão ou outra forma de pagamento.',
            'cc_rejected_other_reason'              => 'O cartão não processou seu pagamento',
            'pending_waiting_transfer'              => "<p class='mt-2'>Escaneie o QR Code ou copie a chave para efetuar o pagamento.</p><h4>Page até <b>$dateOfExpiration</b>.</h4><img width='200px' src='data:image/jpeg;base64,$qr_code_base64'/><br/><div class='input-group mt-1'><input type='text' class='form-control' value='$qr_code' readonly><span class='input-group-btn'><button type='button' class='btn btn-primary btn-flat copy-input'><i class='fas fa-copy'></i></button></span></div><br/><span class='status_copy'></span>"
        ];

        if (array_key_exists($this->getPayment()->status_detail, $status)) {
            return $status[$this->getPayment()->status_detail];
        } else {
            return "Houve um problema no pagamento. Acesse seu e-mail e siga as orientações!";
        }
    }

    #Get Error
    public function getErrors(): string
    {
        $error = array(
            '205'   => 'Digite o número do seu cartão.',
            '208'   => 'Escolha um mês.',
            '209'   => 'Escolha um ano.',
            '212'   => 'Informe seu documento.',
            '213'   => 'Informe seu documento.',
            '214'   => 'Informe seu documento.',
            '220'   => 'Informe seu banco emissor.',
            '221'   => 'Informe seu sobrenome.',
            '224'   => 'Digite o código de segurança.',
            'E301'  => 'Há algo de errado com o número do cartão. Digite novamente.',
            'E302'  => 'Confira o código de segurança.',
            '316'   => 'Por favor, digite um nome válido.',
            '322'   => 'Confira seu documento.',
            '323'   => 'Confira seu documento.',
            '324'   => 'Confira seu documento.',
            '325'   => 'Confira a data.',
            '326'   => 'Confira a data.'
        );

        if (array_key_exists($this->getPayment()->error->causes[0]->code,$error)) {
            return $error[$this->getPayment()->error->causes[0]->code];
        } else {
            return "Houve um problema no pagamento. Acesse seu e-mail e siga as orientações!";
        }
    }
}
