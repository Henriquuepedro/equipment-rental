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

];
