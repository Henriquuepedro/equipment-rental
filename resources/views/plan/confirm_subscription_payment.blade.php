@extends('adminlte::page')

@section('title', 'Confirmar Plano')

@section('content_header')
    <h1 class="m-0 text-dark">Confirmar Plano</h1>
@stop

@section('css')
    <style>
        #paymentBrick_container [type="submit"] {
            display: inline-block;
            font-weight: 400;
            color: #fff;
            text-align: center;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background: #19d895;
            border: 1px solid #19d895;
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            line-height: 1;
            border-radius: 0.1875rem;
            -webkit-transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
        }
        #paymentBrick_container [type="submit"] svg {
            fill: #fff
        }

        @media (min-width: 576px) {
            #paymentBrick_container [type="submit"] {
                width: 100%;
            }
        }
        @media (min-width: 768px) {
            #paymentBrick_container [type="submit"] {
                width: 100%;
            }
        }
        @media (min-width: 992px) {
            #paymentBrick_container [type="submit"] {
                width: 20em;
            }
        }
        @media (min-width: 1200px) {
            #paymentBrick_container [type="submit"] {
                width: 20em;
            }
        }

        #statusScreenBrick_container section:first-child {
            border-radius: 0 !important;
        }

        #statusScreenBrick_container div[class^=banner-]:first-child {
            border-radius: 0 !important;
        }
    </style>
@stop

@section('js')
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script src="https://www.mercadopago.com/v2/security.js" view="checkout"></script>
    <script>

        const mp = new MercadoPago('{{ env('MP_PUBLIC_KEY') }}', {
            locale: 'pt-BR'
        });

        let isFormMounted = false;

        const cardForm = mp.cardForm({
            amount: $('[name="amount_plan"]').val(),
            autoMount: true,
            form: {
                id: "subscriptionForm",
                cardholderName: { id: "cardholderName" },
                cardNumber: { id: "cardNumber" },
                cardExpirationMonth: { id: "cardExpirationMonth" },
                cardExpirationYear: { id: "cardExpirationYear" },
                securityCode: { id: "securityCode" },
                identificationNumber: { id: "identificationNumber" },
                issuer: { id: "issuer" },
                installments: { id: "installments" },
            },
            callbacks: {
                onFormMounted: error => {
                    if (error) {
                        console.warn("Erro ao montar o formulário:", error);
                        return;
                    }
                    console.log("Formulário montado com sucesso.");
                    isFormMounted = true;
                },
                onSubmit: async event => {
                    event.preventDefault();

                    // Verificar se o formulário foi montado antes de prosseguir
                    if (!isFormMounted) {
                        alert("O formulário de pagamento ainda não foi montado corretamente.");
                        return;
                    }

                    // Obter dados do cartão e outros campos
                    const cardData = cardForm.getCardFormData();
                    const { paymentMethodId, cardholderEmail, token, installments, issuer } = cardData;

                    // Validar se o valor de 'paymentMethodId' está presente
                    if (!paymentMethodId) {
                        alert('Método de pagamento não selecionado.');
                        return;
                    }

                    // Validar o campo de número do cartão
                    const cardNumber = document.getElementById('cardNumber').value;
                    if (!cardNumber || cardNumber.length < 13) {
                        alert('Número de cartão inválido.');
                        return;
                    }

                    // Obter o BIN dos primeiros 6 dígitos do número do cartão
                    const bin = cardNumber.slice(0, 6);

                    // Validar o campo identificationNumber (CPF ou CNPJ)
                    let identificationNumber = document.getElementById('identificationNumber').value;
                    identificationNumber = identificationNumber.replace(/\D/g, ''); // Remove qualquer caracter não numérico

                    // Verificar se o CPF ou CNPJ tem o tamanho correto
                    if (identificationNumber.length !== 11 && identificationNumber.length !== 14) {
                        alert("Por favor, insira um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.");
                        return;
                    }

                    // Buscar o issuer (emissor) usando o bin e o método de pagamento
                    let issuerId = cardData.issuerId;
                    if (!issuerId) {
                        const issuers = await mp.getIssuers(paymentMethodId, bin);
                        issuerId = issuers && issuers.length > 0 ? issuers[0].id : null;
                    }

                    // Definir installments (parcelas)
                    const selectedInstallments = document.getElementById('installments').value;

                    // Atualizar o campo oculto com o issuerId
                    document.getElementById('issuer').value = issuerId;

                    const formData = {
                        email: cardholderEmail,
                        token,
                        payment_method_id: paymentMethodId,
                        issuer_id: issuerId,
                        installments: selectedInstallments,
                        transactionAmount: $('[name="amount_plan"]').val(),
                        identification: {
                            type: document.getElementById('identificationType').value,
                            number: document.getElementById('identificationNumber').value,
                        }
                    }
                    const obj_token = {
                        token_plan: $('[name="token_plan"]').val(),
                        device_id: $('[name="device_id"]').val(),
                        idempotency_key: $('[name="idempotency_key"]').val(),
                        subscription_payment: true
                    }
                    let new_object = {...obj_token, ...formData};

                    // Enviar dados para o backend
                    fetch($('[name="route_send_payment"]').val(), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(new_object)
                    })
                        .then(response => response.json())
                        .then(result => {
                            if (result.errors) {
                                Swal.fire({
                                    title: 'Ocorreu um problema para efeturar o pagamento',
                                    html: result.errors,
                                    icon: "error",
                                    showCancelButton: false,
                                    confirmButtonText: "Concluir",
                                    reverseButtons: true,
                                    allowOutsideClick: false
                                });
                            } else {
                                Swal.fire({
                                    title: 'Pagamento aceito',
                                    html: result.message,
                                    icon: "success",
                                    showCancelButton: false,
                                    confirmButtonText: "Concluir",
                                    reverseButtons: true,
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = $('[name="route_request_payment"]').val()
                                    }
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);

                            Swal.fire({
                                title: 'Ocorreu um problema para efeturar o pagamento',
                                html: err.toString(),
                                icon: "error",
                                showCancelButton: false,
                                confirmButtonText: "Concluir",
                                reverseButtons: true,
                                allowOutsideClick: false
                            });
                        });
                },
                onFetching: resource => {
                    console.log("fetching resource:", resource);
                }
            }
        });
    </script>
@stop

@section('content')
    <div class="row profile-page">
        <div class="col-md-12 grid-margin">
            @if(session('success'))
                <div class="alert alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Planos disponíveis</h4>
                    <div class="col-md-12 d-flex justify-content-center" id="payment_container">
                        <form class="col-md-6" method="POST" id="subscriptionForm">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="email">E-mail da solicitação</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->__get('email') }}" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="amount">Valor do plano</label>
                                    <input type="text" class="form-control" value="{{ formatMoney($plan->value, 2, 'R$ ') }} recorrentes por 12 meses" readonly>
                                    <small>Cancele a qualquer momento.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="cardholderName">Nome do Titular</label>
                                    <input type="text" class="form-control" id="cardholderName" data-checkout="cardholderName" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="identificationType">Tipo de Identificação</label>
                                    <select class="form-control" id="identificationType" data-checkout="identificationType" required>
                                        <option value="CPF">CPF</option>
                                        <option value="CNPJ">CNPJ</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-8">
                                    <label for="identificationNumber">Identificação</label>
                                    <input type="text" class="form-control" id="identificationNumber" data-checkout="identificationNumber" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="cardNumber">Número do Cartão</label>
                                    <input type="text" class="form-control" id="cardNumber" data-checkout="cardNumber" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="cardExpirationMonth">Mês de Expiração (MM)</label>
                                    <input type="text" class="form-control" id="cardExpirationMonth" data-checkout="cardExpirationMonth" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="cardExpirationYear">Ano de Expiração (AAAA)</label>
                                    <input type="text" class="form-control" id="cardExpirationYear" data-checkout="cardExpirationYear" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="securityCode">Código de Segurança (CVV)</label>
                                    <input type="text" class="form-control" id="securityCode" data-checkout="securityCode" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12 mt-3">
                                    <button type="submit" class="btn btn-primary w-100">Assinar por recorrência agora</button>
                                </div>
                            </div>
                            <select id="issuer" name="issuer" class="d-none"></select>
                            <select id="installments" name="installments" class="d-none"></select>
                        </form>
                    </div>
                    <div class="row">
                        <div class="col-md-6 offset-md-3 mt-3 mb-3">
                            <img src="https://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_575X40.jpg?v=1"
                                 alt="Mercado Pago - Meios de pagamento" title="Mercado Pago - Meios de pagamento"
                                 style="width: 100%; height: 45px"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 offset-md-3 mt-3 mb-3 text-center">
                            <a href="https://www.mercadopago.com.br/ajuda/322" target="_blank">Veja os juros de parcelamentos!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resultPayment" tabindex="-1" role="dialog" aria-labelledby="resultPaymentLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div id="statusScreenBrick_container"></div>
            </div>
        </div>
    </div>

    <input type="hidden" name="route_send_payment" value="{{ route('plan.insert', array('plan' => $plan->id)) }}">
    <input type="hidden" name="route_request_payment" value="{{ route('plan.request') }}">
    <input type="hidden" name="amount_plan" value="{{ $plan->value }}">
    <input type="hidden" name="style_template" value="{{ $settings['style_template'] }}">
    <input type="hidden" name="amount_plan" value="{{ $plan->value }}">
    <input type="hidden" name="token_plan" value="{{ $tokenStr }}">
    <input type="hidden" name="device_id" id="deviceId">
    <input type="hidden" name="idempotency_key" id="idempotency_key" value="{{ $idempotency_key }}">
@stop
