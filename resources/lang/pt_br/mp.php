<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'pending'       => 'Pendente', // The user has not concluded the payment process (for example, by generating a payment by boleto, it will be concluded at the moment in which the user makes the payment in the selected place).
    'approved'      => 'Aprovado', // The payment has been approved and credited.
    'authorized'    => 'Autorizado', // The payment has been authorized but not captured yet.
    'inprocess'     => 'Em processamento', // The payment is in analysis.
    'inmediation'   => 'Na mediação', // The user started a dispute.
    'rejected'      => 'Rejeitado', // The payment was rejected (the user can try to pay again).
    'cancelled'     => 'Cancelado', // Either the payment was canceled by one of the parties or expired.
    'refunded'      => 'Devolvido', // The payment was returned to the user.
    'chargedback'   => 'Estornado', // A chargeback was placed on the buyer's credit card.
    'in_process'    => 'Em processamento',

    'accredited'                            => 'Creditado', // credited payment.
    'pending_contingency'                   => 'Em processamento', // the payment is being processed.
    'pending_review_manual'                 => 'Revisão Manual', // the payment is under review to determine its approval or rejection.
    'rejected_insufficient_data'            => 'Dados insuficientes',
    'cc_rejected_bad_filled_date'           => 'Data de expiração incorreta', // incorrect expiration date.
    'cc_rejected_bad_filled_other'          => 'Preenchimento incorreto', // incorrect card details.
    'cc_rejected_bad_filled_security_code'  => 'Código de segurança rejeitado preenchido incorretamente', // incorrect CVV.
    'cc_rejected_blacklist'                 => 'Blacklist', // the card is on a black list for theft/complaints/fraud.
    'cc_rejected_call_for_authorize'        => 'Autorização solicitada ao banco', // the means of payment requires prior authorization of the amount of the operation.
    'cc_rejected_card_disabled'             => 'Cartão desativado', // the card is inactive.
    'cc_rejected_duplicated_payment'        => 'Pagamento duplicado', // transacción duplicada.
    'cc_rejected_high_risk'                 => 'Alto risco', // rechazo por Prevención de Fraude.
    'cc_rejected_insufficient_amount'       => 'Valor insuficiente', // insufficient amount.
    'cc_rejected_invalid_installments'      => 'Parcelamento não válido', // invalid number of installments.
    'cc_rejected_max_attempts'              => 'Tentativas excedida', // exceeded maximum number of attempts.
    'cc_rejected_other_reason'              => 'Rejeitado outro motivo', // generic error.
    'cc_rejected_card_error'                => 'Cartão com erro',
    'cc_rejected_card_type_not_allowed'     => 'Tipo de cartão não permitido',
    'pending_waiting_transfer'              => 'Aguardando transferência',
    'pending_waiting_payment'               => 'Aguardando pagamento',
    'expired'                               => 'Pagamento vencido',

    'account_money'     => 'Saldo Mercado Pago', // Money in the Mercado Pago account.
    'ticket'            => 'Boleto', // Boletos, Caixa Electronica Payment, PayCash, Efecty, Oxxo, etc.
    'bank_transfer'     => 'Transferência', // Pix and PSE (Pagos Seguros en Línea).
    'atm'               => 'ATM', // ATM payment (widely used in Mexico through BBVA Bancomer).
    'credit_card'       => 'Cartão de Crédito', // Payment by credit card.
    'debit_card'        => 'Cartão de Débito', // Payment by debit card.
    'prepaid_card'      => 'Cartão Pré-Pago', // Payment by prepaid card.
    'digital_currency'  => 'Mercado Crédito', // Purchases with Mercado Crédito.
    'digital_wallet'    => 'Paypal', // Paypal.
    'voucher_card'      => 'Cartão de Benefício', // Alelo benefits, Sodexo.
    'crypto_transfer'   => 'Criptomoeda', // Payment with cryptocurrencies such as Ethereum and Bitcoin.

    'bolbradesco'       => '',

    'preapproval'       => 'Recorrência',

];
