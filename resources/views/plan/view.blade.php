@extends('adminlte::page')

@section('title', 'Informações do Pagamento')

@section('content_header')
    <h1 class="m-0 text-dark">Informações do Pagamento</h1>
@stop

@section('css')
    <style>
        .hide-last-event {
            height: 100px;
            width: 3px;
            background: #fff;
            position: relative;
            left: 250px;
            margin-top: -98px;
        }

        .shadow-sm .card-header:not(.bg-primary) {
            background-color: #dde;
            border: 1px solid #dde;
        }

        .pricing-card-title {
            font-size: 2.5rem !important;
            width: 100%;
            margin-bottom: 1rem;
        }

        ul.list-unstyled li {
            font-size: 1.2rem !important;
        }

        .timeline::before {
            display: none;
        }

        .timeline {
            border-left: 3px solid #727cf5;
            border-bottom-right-radius: 4px;
            border-top-right-radius: 4px;
            background: rgba(114, 124, 245, 0.09);
            margin: 0 auto;
            letter-spacing: 0.2px;
            position: relative;
            line-height: 1.4em;
            font-size: 1.03em;
            padding: 50px;
            list-style: none;
            text-align: left;
            max-width: 40%;
        }

        @media (max-width: 992px) {
            .hide-last-event {
                height: 98px;
                left: 135px;
                margin-top: -98px;
            }
        }

        @media (max-width: 767px) {
            .timeline {
                max-width: 98%;
                padding: 25px;
            }
            .hide-last-event {
                height: 103px;
                left: 125px;
                margin-top: -103px;
            }
        }

        @media (max-width: 580px) {
            .hide-last-event {
                display: none;
            }
        }

        .timeline h1 {
            font-weight: 300;
            font-size: 1.4em;
        }

        .timeline h2,
        .timeline h3 {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .timeline .event {
            border-bottom: 1px dashed #e8ebf1;
            padding-bottom: 25px;
            margin-bottom: 25px;
            position: relative;
        }

        @media (max-width: 767px) {
            .timeline .event {
                padding-top: 30px;
            }
        }

        .timeline .event:last-of-type {
            padding-bottom: 0;
            margin-bottom: 0;
            border: none;
        }

        .timeline .event:before,
        .timeline .event:after {
            position: absolute;
            display: block;
            top: 0;
        }

        .timeline .event:before {
            left: -207px;
            content: attr(data-date);
            text-align: right;
            font-weight: bold;
            font-size: 1em;
            min-width: 120px;
        }

        @media (max-width: 767px) {
            .timeline .event:before {
                left: 0px;
                text-align: left;
            }
        }

        .timeline .event:after {
            -webkit-box-shadow: 0 0 0 3px #727cf5;
            box-shadow: 0 0 0 3px #727cf5;
            left: -55.8px;
            background: #fff;
            border-radius: 50%;
            height: 9px;
            width: 9px;
            content: "";
            top: 5px;
        }

        .timeline .event:after:last-child {

        }

        @media (max-width: 767px) {
            .timeline .event:after {
                left: -31.8px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        $(function(){
            checkLabelAnimate();
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex flex-wrap">
                            <div class="header-card-body">
                                <h4 class="card-title">Informações do Pagamento</h4>
                                <p class="card-description"> Visualize todos os dados sobre o pagamento do plano.</p>
                            </div>
                            <div class="col-md-12 no-padding">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Forma de Pagamento</label>
                                        <input type="text" class="form-control" name="type_payment" value="" disabled />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Plano</label>
                                        <input type="text" class="form-control" name="plan" value="{{ $payment->name }}" disabled />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Data da Solicitação</label>
                                        <input type="text" class="form-control" name="date_requested" value="{{ dateInternationalToDateBrazil($payment->created_at) }}" disabled />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Valor</label>
                                        <input type="text" class="form-control" name="amount" value="{{ $payment->gross_amount }}" disabled />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-5">
                                        <label>Usuário Solicitante</label>
                                        <input type="text" class="form-control" name="user" value="{{ $user->email }}" disabled />
                                    </div>
                                    <div class="form-group col-md-7">
                                        <label>Empresa</label>
                                        <input type="text" class="form-control" name="company" value="{{ $company->name }}" disabled />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Situação Atual</label>
                                        <input type="text" class="form-control" name="status" value="{{ __('mercadopago.' . $payment->status) }}" disabled />
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Situação Atual Detalhada</label>
                                        <input type="text" class="form-control" name="status_detail" value="{{ __('mercadopago.' . $payment->status_detail) }}" disabled />
                                    </div>
                                </div>
                                @if ($payment->payment_method_id === 'pix' && $payment->payment_type_id === 'bank_transfer')
                                <div class="row justify-content-center flex-wrap">
                                    <div class='col-md-12 d-flex justify-content-center'>
                                        <img width="250px" class="mt-2" src="data:image/jpeg;base64,{{ $payment->base64_key_pix }}" alt="QR Code"/>
                                    </div>
                                    <div class='input-group col-md-8 mt-2'>
                                        <input type='text' class='form-control' name="pix_copy_paste" value="{{ $payment->key_pix }}" readonly>
                                        <span class='input-group-btn'>
                                            <button type='button' class='btn btn-primary btn-flat copy-input'>
                                                <i class='fas fa-copy'></i>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-center col-md-12">
                                        <div class="form-group col-md-4">
                                            <label>Pague até</label>
                                            <input type="text" class="form-control" name="pix_date_of_expiration" value="{{ dateInternationalToDateBrazil($payment->date_of_expiration, 'd/m H:i') }}" disabled/>
                                        </div>
                                    </div>
                                    <br/>
                                    <span class='status_copy'></span>
                                </div>
                                @elseif (in_array($payment->payment_type_id, array('credit_card', 'debit_card', 'prepaid_card')))
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Parcelas</label>
                                        <input type="text" class="form-control" name="card_installments" value="{{ $payment->installments }}" disabled />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Bandeira</label>
                                        <input type="text" class="form-control" name="card_payment_method_id" value="{{ $payment->payment_method_id }}" disabled />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Valor Total Pago</label>
                                        <input type="text" class="form-control" name="card_client_amount" value="{{ $payment->gross_amount }}" disabled />
                                    </div>
                                </div>
                                @elseif ($payment->payment_method_id === 'bolbradesco' && $payment->payment_type_id === 'ticket')
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <a href="{{ $payment->link_billet }}" class="billet_link_billet btn btn-primary col-md-12 mt-4" target="_blank" >Visualizar PDF</a>
                                    </div>
                                    <div class="form-group col-md-4 label-animate">
                                        <label>Chave Boleto</label>
                                        <div class="input-group label-animate">
                                            <input type='text' class='form-control' name="billet_barcode" value="{{ $payment->barcode_billet }}" readonly>
                                            <button type='button' class='btn btn-primary btn-flat copy-input'>
                                                <i class='fas fa-copy'></i>
                                            </button>
                                        </div>
                                        <br/>
                                        <span class='status_copy'></span>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Pague até</label>
                                        <input type="text" class="form-control" name="billet_date_of_expiration" value="{{ dateInternationalToDateBrazil($payment->date_of_expiration, 'd/m') }}" disabled />
                                    </div>
                                </div>
                                @endif
                                <div class="row histories-division">
                                    <div class="col-md-12"><hr/></div>
                                </div>
                                <div class="row histories-title">
                                    <div class="col-md-12 text-center">
                                        <h4>Histórico da Transação</h4>
                                    </div>
                                </div>
                                <div class="vertical-timeline mt-3">
                                    @foreach($plan_histories as $key => $plan_history)
                                        <div class="timeline-wrapper timeline-wrapper-{{ getColorStatus($plan_history->status) }} {{ $key % 2 == 0 ? 'timeline-inverted' : '' }}">
                                            <div class="timeline-badge"></div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">{{ __('mercadopago.' . $plan_history->status) }}</h6>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>{{ __('mercadopago.' . $plan_history->status_detail) }}</p>
                                                </div>
                                                <div class="timeline-footer d-flex align-items-center">
                                                    <span class="ml-auto font-weight-bold">{{ dateInternationalToDateBrazil($plan_history->created_at, DATETIME_INTERNATIONAL_NO_SECONDS) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex justify-content-between">
                            <a href="{{ route('plan.request') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
