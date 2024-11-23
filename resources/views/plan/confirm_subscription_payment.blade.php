@extends('adminlte::page')

@section('title', 'Confirmar Plano')

@section('content_header')
    <h1 class="m-0 text-dark">Confirmar Plano</h1>
@stop

@section('css')
    <link href="{{ asset('assets/vendors/brincks/css/style.css') }}" rel="stylesheet">
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

        #statusScreenBrickPreApproval_container section:first-child {
            border-radius: 0 !important;
        }

        #statusScreenBrickPreApproval_container div[class^=banner-]:first-child {
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

        const bricksBuilder = mp.bricks();

        $(function (){
            renderPaymentBrick(bricksBuilder);
        });

        const renderPaymentBrick = async (bricksBuilder) => {
            const settings = {
                initialization: {
                    /*
                      "amount" é a quantia total a pagar por todos os meios de pagamento com exceção da Conta Mercado Pago e Parcelas sem cartão de crédito, que têm seus valores de processamento determinados no backend através do "preferenceId"
                    */
                    amount: $('[name="amount_plan"]').val(),
                    // preferenceId: "<PREFERENCE_ID>",
                    payer: {
                        firstName: '{{ $company_data->first_company_name }}',
                        lastName: '{{ $company_data->last_company_name }}',
                        identification: {
                            "type": "{{ $company_data->type_person === 'pf' ? "CPF" : "CNPJ" }}",
                            "number": "{{ $company_data->cpf_cnpj }}",
                        },
                        email: '{{ auth()->user()->__get('email') }}',
                        address: {
                            zipCode: '{{ $company_data->cep }}',
                            federalUnit: '{{ $company_data->state }}',
                            city: '{{ $company_data->city }}',
                            neighborhood: '{{ $company_data->neigh }}',
                            streetName: '{{ $company_data->address }}',
                            streetNumber: '{{ $company_data->number }}',
                            complement: '{{ $company_data->complement }}',
                        }
                    },
                },
                customization: {
                    visual: {
                        style: {
                            theme: parseInt($('[name="style_template"]').val()) === 3 ? "dark" : "bootstrap",
                        },
                    },
                    paymentMethods: {
                        creditCard: "all",
                        maxInstallments: 1
                    },
                },
                callbacks: {
                    onReady: () => {
                        /*
                         Callback chamado quando o Brick está pronto.
                         Aqui, você pode ocultar o seu site, por exemplo.
                        */
                    },
                    onSubmit: ({ selectedPaymentMethod, formData }) => {
                        const obj_token = {
                            token_plan: $('[name="token_plan"]').val(),
                            device_id: $('[name="device_id"]').val(),
                            card_client_name: $('[name="HOLDER_NAME"]').val() ?? null,
                            // idempotency_key: $('[name="idempotency_key"]').val(),
                            subscription_payment: true
                        }
                        let new_object = {...obj_token, ...formData};

                        // callback chamado quando há click no botão de envio de dados
                        return new Promise((resolve, reject) => {
                            fetch($('[name="route_send_payment"]').val(), {
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify(new_object),
                            })
                                .then((response) => {
                                    if (response.ok) {
                                        return response.json().then((response) => {
                                            if (typeof response.payment_id !== "undefined" && response.payment_id) {
                                                renderStatusScreenBrick(response.message, response.payment_method, response.init_point, response.status);
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Pagamento não realizado',
                                                    html: response.errors
                                                });
                                            }
                                        });
                                    }

                                    reject();
                                    return response.json().then(error => {
                                        if (typeof error.payment_id !== "undefined" && error.payment_id) {
                                            renderStatusScreenBrick(response.message, response.payment_method, response.init_point, response.status);
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Pagamento não realizado',
                                                html: error.errors
                                            });
                                        }
                                    });
                                })
                                .then((response) => {
                                    // receber o resultado do pagamento
                                    resolve();
                                })
                                .catch((error) => {
                                    // manejar a resposta de erro ao tentar criar um pagamento
                                    reject();

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Pagamento não realizado',
                                        html: error.errors
                                    });
                                });
                        });
                    },
                    onError: (error) => {
                        // callback chamado para todos os casos de erro do Brick
                        console.error(error);
                    },
                },
            };

            window.paymentBrickController = await bricksBuilder.create(
                "payment",
                "paymentBrick_container",
                settings
            );
        }

        const renderStatusScreenBrick = (message, payment_method, init_point, status) => {
            const color_status   = status !== 'pending' ? 'success' : 'warning';
            const icon_status    = status !== 'pending' ? 'fa-check' : 'fa-warning';
            const content_status = status !== 'pending' ? `
            <div class="row-2DV3l5 svelte-15powyh">
                 <div class="brick-row-title-1fPbjc svelte-15powyh">
                    <h1 aria-label="Seu pagamento foi aprovado" class="svelte-101ibq7 extra-extra-large-1eRDc5">Seu pagamento foi aprovado</h1>
                 </div>
                 <h2 class="svelte-12nsdgl secondary-style-RPgUa7 padding-top-CmYDHg hide-2kWVMb"></h2>
              </div>`
                : `
              <div class="row-2DV3l5 svelte-15powyh">
                <div class="brick-row-title-1fPbjc svelte-15powyh">
                  <h1 aria-label="${message}" class="svelte-101ibq7 extra-extra-large-1eRDc5">${message}</h1>
                </div>
            </div>`;

            let content = `<div class="fade-wrapper-3PVuVZ svelte-1yy4rvb" style="">
              <section class="svelte-1hgkimz">
                 <header>
                    <div class="svelte-ax97e0">
                       <div>
                          <div class="row-2DV3l5 svelte-15powyh">
                             <div class="banner-1slUhr svelte-15powyh ${color_status}-3C0O37"></div>
                             <div class="icon-2EaB9L svelte-15powyh ${color_status}-3C0O37">
                                <i class="fa-solid ${icon_status}"></i>
                             </div>
                          </div>
                          ${content_status}
                       </div>
                    </div>
                 </header>
                 <div class="status-body-1Ad2Op svelte-1hgkimz" style="position: relative;">
                    <section class="svelte-h9a98r">
                       <div class="wrapper-3IdSv7 svelte-1ica6gz">
                          <div class="icon-2-OwEK svelte-1ica6gz background-white-3mYVBZ">
                             <div role="img" data-testid="icon" class="wrapper-3WCSbE svelte-cqvb04" aria-hidden="true" style=""><img src="https://www.mercadopago.com/org-img/MP3/API/logos/${payment_method}.gif" alt="${payment_method}" aria-hidden="true" class="svelte-cqvb04"></div>
                          </div>
                          <div class="content-2u3w8A svelte-1ica6gz">
                             <p aria-label="2 reais" class="svelte-1ica6gz">R$ ${numberToReal($('[name="amount_plan"]').val())} <span class="svelte-1ica6gz"></span></p>
                             <span class="svelte-1ica6gz">Cartão de Crédito</span>
                          </div>
                       </div>
                    </section>
                    <div class="details-oy8snH svelte-1hgkimz">
                       <div class="wrapper-3IdSv7 svelte-1ica6gz">
                          <div class="icon-2-OwEK svelte-1ica6gz">
                             <i class="fa-solid fa-bag-shopping"></i>
                          </div>
                          <div class="content-2u3w8A svelte-1ica6gz">
                             <p class="svelte-1ica6gz">Descrição <span class="svelte-1ica6gz"></span></p>
                             <span class="svelte-1ica6gz">${$('[name="plan_name"]').val()}</span>
                          </div>
                       </div>
                    </div>`;

                    if (status === 'pending') {
                        content += `<div class="status-body-1Ad2Op svelte-1hgkimz" style="position: relative;">
                            <a href="${init_point}" target="_blank" aria-label="" type="submit"
                               class="svelte-15hy5j4 secondary-3Qj0ZF margin-1qSn3W"> <span class="svelte-15hy5j4">Realizar Pagamento</span></a>
                        </div>`;
                    }

                    content += `<div class="wrapper-2F0p0I svelte-j7wp1b"> <a href="${$('[name="route_request_payment"]').val()}" aria-label="" type="submit" class="svelte-15hy5j4 transparent-CXDHMO"> <span class="svelte-15hy5j4">Ver solicitações</span></a></div>
                 </div>
              </section>
            </div>`;

            $('#resultPayment #statusScreenBrickPreApproval_container')
                .prop('style', 'max-width: 100%;--font-size-extra-small: 12px;--font-size-small: 13px;--font-size-medium: 14px;--font-size-large: 16px;--font-size-extra-large: 18px;--font-size-extra-extra-large: 20px;--font-weight-normal: 400;--font-weight-semi-bold: 600;--form-inputs-text-transform: none;--input-vertical-padding: 8px;--input-horizontal-padding: 12px;--input-focused-box-shadow: 0px 0px 0px 3px transparent;--input-error-focused-box-shadow: 0px 0px 0px 3px transparent;--input-border-width: 1px;--input-focused-border-width: 2px;--border-radius-small: 4px;--border-radius-medium: 3px;--border-radius-large: 16px;--border-radius-full: 100%;--form-padding: 16px;--input-min-height: 38px;--label-spacing: 4px;--row-spacing: 18px;--button-padding: 14px 48px;--row-gap: 16px;--width-small: 16px;--height-small: 16px;--width-medium: 36px;--height-medium: 24px;')
                .empty()
                .append(content);
            $('#resultPayment').modal('show');
        }
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
                    <h4 class="card-title">Dados para assinar</h4>
                    <p class="card-description">O plano por assinatura é realizado através do pagamento com cartão de crédito, onde todo mês será realizado uma cobrança do valor abaixo, durante 12 meses, sendo possível realizar o cancelamento a qualquer momento.</p>
                    <div id="paymentBrick_container"></div>
                    <div class="row">
                        <div class="col-md-6 offset-md-3 mt-3 mb-3">
                            <img src="https://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_575X40.jpg?v=1"
                                 alt="Mercado Pago - Meios de pagamento" title="Mercado Pago - Meios de pagamento"
                                 style="width: 100%; height: 45px"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resultPayment" tabindex="-1" role="dialog" aria-labelledby="resultPaymentLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div id="statusScreenBrickPreApproval_container"></div>
            </div>
        </div>
    </div>

    <input type="hidden" name="route_send_payment" value="{{ route('plan.insert', array('plan' => $plan->id)) }}">
    <input type="hidden" name="route_request_payment" value="{{ route('plan.request') }}">
    <input type="hidden" name="amount_plan" value="{{ $plan->value }}">
    <input type="hidden" name="style_template" value="{{ $settings['style_template'] }}">
    <input type="hidden" name="plan_name" value="{{ $plan->name }}">
    <input type="hidden" name="token_plan" value="{{ $tokenStr }}">
    <input type="hidden" name="device_id" id="deviceId">
    <input type="hidden" name="idempotency_key" id="idempotency_key" value="{{ $idempotency_key }}">
@stop
