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
                        email: '{{ auth()->user()->email }}',
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
                        //debitCard: "all",
                        ticket: "all",
                        bankTransfer: "all",
                        //atm: "all",
                        //onboarding_credits: "all",
                        //wallet_purchase: "all",
                        maxInstallments: 12
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
                        const obj_token = {'token_plan': $('[name="token_plan"]').val(), 'device_id': $('[name="device_id"]').val()}
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
                                            renderStatusScreenBrick(bricksBuilder, response.payment_id);
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
                                        renderStatusScreenBrick(bricksBuilder, error.payment_id);
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

        const renderStatusScreenBrick = async (bricksBuilder, paymentId) => {
            const settings = {
                initialization: {
                    paymentId, // Payment identifier, from which the status will be checked
                },
                customization: {
                    visual: {
                        hideStatusDetails: true,
                        hideTransactionDate: true,
                        style: {
                            theme: parseInt($('[name="style_template"]').val()) === 3 ? "dark" : "default"
                        },
                        texts: {
                            //ctaGeneralErrorLabel: "",
                            //ctaCardErrorLabel: "",
                            ctaReturnLabel: "Ver solicitações",
                        }
                    },
                    backUrls: {
                        'error': window.location.href,
                        'return': $('[name="route_request_payment"]').val()
                    }
                },
                callbacks: {
                    onReady: () => {
                        $('#resultPayment').modal('show')
                    },
                    onError: (error) => {
                        // Callback called for all Brick error cases
                    },
                },
            };
            window.statusScreenBrickController = await bricksBuilder.create('statusScreen', 'statusScreenBrick_container', settings);
        };

        $(document).on('click', '.copy-input', function() {
            // Seleciona o conteúdo do input
            $(this).closest('.input-group').find('input').select();
            // Copia o conteúdo selecionado
            const copy = document.execCommand('copy');
            if (copy) {
                $('.status_copy').addClass('text-success font-weight-bold').html("Código copiado com sucesso!")
            } else {
                $('.status_copy').addClass('text-success font-weight-bold').html("Não foi possível copiar o conteúdo!")
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
                    <h4 class="card-title">Solicitações</h4>
                    <p class="card-description">Solicitações realizadas.</p>
                    <div id="paymentBrick_container"></div>
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
