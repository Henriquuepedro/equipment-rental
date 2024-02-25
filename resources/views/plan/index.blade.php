@extends('adminlte::page')

@section('title', 'Planos')

@section('content_header')
    <h1 class="m-0 text-dark">Planos</h1>
@stop

@section('css')
    <style>
        .nav.nav-tabs {
            border: 1px solid;
            padding: 5px 0;
            border-radius: 10px;
            background: rgba(0,0,0,.1);
        }

        .pricing-card-head h1.fw-normal {
            font-size: 1.75rem
        }

        .pricing-table .pricing-card .pricing-card-body {
            padding: 35px 15px 353px
        }

        @media (max-width: 1200px) and (min-width: 992px) {
            .pricing-table .pricing-card .pricing-card-body {
                padding: 25px 12px 21px !important;
            }

            .pricing-table h1.fw-normal {
                font-size: 20px;
            }

            .pricing-table h4.fw-normal {
                font-size: 12px;
            }

            .pricing-table .pricing-card-head h3 {
                font-size: 15px;
                font-weight: bold;
            }

            .pricing-table .pricing-card-head p {
                font-size: 10px;
            }
        }

        .tooltip >.tooltip-inner {
            background-color: #2196f3;
            color: #fff;
            border: 2px solid #0c83e2;
        }

        .tooltip.show {
            top: -10px !important;
        }

        .bs-tooltip-top .arrow::before {
            border-top-color: #0c83e2
        }
    </style>
@stop

@section('js')
    <script src="https://www.mercadopago.com/v2/security.js" view="search"></script>
    <script>
        $(function(){
            listPlans($('[data-toggle="tab"].active').data('month-time'));
            $('[data-toggle-second="tooltip"]').tooltip('show');
        });


        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            listPlans($(e.target).data('month-time'));
        });

        const listPlans = type =>  {
            const tag_plan = type === 1 ? 'mês' : (type !== 12 ? 'meses' : 'ano');

            $(`[data-month-time="${type}"].tab-pane`).find(`.pricing-table`).empty().append(getHtmlLoading());
            $.get(`{{ route('ajax.plan.get-plans') }}/${type}`, response => {
                $(`[data-month-time="${type}"].tab-pane`).find(`.pricing-table`).empty();

                let price_from, description, alert_user, message_equipment_mmanager, percentage_off, discount_months;

                $(response).each(function(key, value){
                    percentage_off = type === 6 ? '10' : (type === 12 ? '20' : '');
                    discount_months = type !== 1 ? `<p class="fw-normal mb-0">R$ ${numberToReal(value.value)} por ${type} ${tag_plan}</p><p class="fw-normal mb-0">valor equivalente mensal de</p>` : '';
                    price_from = value.from_value === null || parseFloat(value.from_value) === 0 ? '' : `<div class="d-flex flex-wrap justify-content-center align-items-center mb-1"><p class="fw-normal mb-0 text-primary" style="text-decoration:line-through;">R$ ${numberToReal(value.from_value)}</p>&nbsp;<div class="badge badge-pill badge-lg badge-success">${percentage_off}% OFF</div></div>`;
                    description = value.description === '<p><br></p>' ? '' : value.description;
                    alert_user = value.allowed_users ? `Até <b>${value.allowed_users}</b> usuários` : 'Usuários ilimitados';
                    message_equipment_mmanager = value.quantity_equipment ? `Até <b>${value.quantity_equipment}</b> equipamentos` : 'Equipamentos ilimitados'
                    $(`[data-month-time="${type}"].tab-pane`).find(`.pricing-table`).append(
                        `<div class="col-lg-3 col-sm-12 grid-margin stretch-card pricing-card">
                        <div class="card border-${value.highlight ? 'success' : 'primary'} border pricing-card-body pl-0 pr-0">
                            <div class="text-center pricing-card-head">
                                <h3>${value.name}</h3>
                                <p class="pr-2 pl-4 mb-0 text-left"><i class="fa fa-check text-success"></i>&nbsp;&nbsp;${message_equipment_mmanager}</p>
                                <p class="pr-2 pl-4 mb-0 text-left"><i class="fa fa-check text-success"></i>&nbsp;&nbsp;${alert_user}</p>
                                <p class="pr-2 pl-4 text-left"><i class="fa fa-check text-success"></i>&nbsp;&nbsp;Suporte via chamado</p>

                                ${price_from}
                                ${discount_months}
                                <h1 class="fw-normal mb-0 text-primary font-weight-bold">R$ ${numberToReal(value.value / type)}/mês*</h1>
                            </div>
                            <div class="plan-features">
                                ${description}
                            </div>
                            <div class="wrapper d-flex justify-content-center pl-4 pr-4">
                                <a href="${window.location.href}/confirmar/${value.id}" class="btn ${value.highlight ? 'btn-success' : 'btn-outline-primary'} btn-block col-md-10">Assinar</a>
                            </div>
                        </div>
                    </div>`
                    );
                });

                if (!response.length) {
                    $(`[data-month-time="${type}"].tab-pane`).find(`.pricing-table`).append('<div class="col-md-12"><div class="alert alert-fill-warning" role="alert"><i class="mdi mdi-alert-circle"></i> Nenhum plano encontrado para essa categoria.</div></div>');
                }
            });
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
                    <h4 class="card-title">Planos</h4>
                    <p class="card-description">Escolha um plano que melhor se adapte a você.</p>
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <ul class="nav nav-tabs tab-solid tab-solid-primary d-flex justify-content-around border-primary" role="tablist">
                                <li class="col-md-2 nav-item">
                                    <a class="d-flex justify-content-center nav-link active" id="monthly" data-toggle="tab" href="#monthly-plan" data-month-time="1" role="tab" aria-controls="monthly-plan" aria-selected="true">
                                        Mensal
                                    </a>
                                </li>
{{--                                <li class="col-md-2 nav-item">--}}
{{--                                    <a class="d-flex justify-content-center nav-link" id="quarterly" data-toggle="tab" href="#quarterly-plan" data-month-time="3" role="tab" aria-controls="quarterly-plan" aria-selected="false">--}}
{{--                                        Trimestral--}}
{{--                                    </a>--}}
{{--                                </li>--}}
                                <li class="col-md-2 nav-item">
                                    <a class="d-flex justify-content-center nav-link" id="semiannual" data-toggle="tab" href="#semiannual-plan" data-month-time="6" role="tab" aria-controls="semiannual-plan" aria-selected="false" data-toggle-second="tooltip" data-placement="top" title="10% OFF" data-trigger="manual">
                                        Semestral
                                    </a>
                                </li>
                                <li class="col-md-2 nav-item">
                                    <a class="d-flex justify-content-center nav-link" id="annual" data-toggle="tab" href="#annual-plan" data-month-time="12" role="tab" aria-controls="annual-plan" aria-selected="false" data-toggle-second="tooltip" data-placement="top" title="20% OFF" data-trigger="manual">
                                        Anual
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content tab-content-solid">
                                <div class="tab-pane fade show active" id="monthly-plan" role="tabpanel" aria-labelledby="monthly-plan" data-month-time="1">
                                    <div class="row pricing-table"></div>
                                </div>
                                <div class="tab-pane fade" id="quarterly-plan" role="tabpanel" aria-labelledby="quarterly-plan" data-month-time="3">
                                    <div class="row pricing-table"></div>
                                </div>
                                <div class="tab-pane fade" id="semiannual-plan" role="tabpanel" aria-labelledby="semiannual-plan" data-month-time="6">
                                    <div class="row pricing-table"></div>
                                </div>
                                <div class="tab-pane fade" id="annual-plan" role="tabpanel" aria-labelledby="annual-plan" data-month-time="12">
                                    <div class="row pricing-table"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card-body">
                    <div class="text-center pt-5">
                        <h4 class="mb-3 mt-5"></h4>
                        <p class="w-75 mx-auto mb-5"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="route_get_plans" value="{{ route('ajax.plan.get-plans') }}">
@stop
