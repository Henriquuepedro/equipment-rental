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
    </style>
@stop

@section('js')
    <script src="https://www.mercadopago.com/v2/security.js" view="search"></script>
    <script>
        $(function(){
            listPlans($('[data-toggle="tab"].active').data('month-time'));
        });


        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            listPlans($(e.target).data('month-time'));
        });

        const listPlans = type =>  {
            $(`[data-month-time="${type}"].tab-pane`).find(`.pricing-table`).empty().append(getHtmlLoading());
            $.get(`{{ route('ajax.plan.get-plans') }}/${type}`, response => {
                $(`[data-month-time="${type}"].tab-pane`).find(`.pricing-table`).empty();

                const col_lg = response.length === 1 ? 12 : (response.length === 2 ? 6 : (response.length === 3 ? 4 : 3));

                let price_from, description;

                $(response).each(function(key, value){
                    console.log(value);
                    price_from = value.from_value === null || parseFloat(value.from_value) === 0 ? '' : `<h4 class="fw-normal mb-0 text-primary" style="text-decoration:line-through;">R$ ${numberToReal(value.from_value)}</h4>`;
                    description = value.description === '<p><br></p>' ? '' : value.description;
                    $(`[data-month-time="${type}"].tab-pane`).find(`.pricing-table`).append(
                        `<div class="col-lg-${col_lg} col-sm-12  grid-margin stretch-card pricing-card">
                        <div class="card border-${value.highlight ? 'success' : 'primary'} border pricing-card-body">
                            <div class="text-center pricing-card-head">
                                <h3>${value.name}</h3>

                                <p class="mb-0 mt-2 text-left"><i class="fa fa-check text-success"></i>&nbsp;&nbsp;Equipamentos disponíveis: <b>${value.quantity_equipment}</b></p>
                                <p class="text-left"><i class="fa fa-check text-success"></i>&nbsp;&nbsp;Suporte on-line</p>

                                ${price_from}
                                <h1 class="fw-normal mb-0">R$ ${numberToReal(value.value)}<small>/mês</small></h1>
                            </div>
                            <div class="plan-features">
                                ${description}
                            </div>
                            <div class="wrapper">
                                <a href="${window.location.href}/confirmar/${value.id}" class="btn ${value.highlight ? 'btn-success' : 'btn-outline-primary'} btn-block">Assinar</a>
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
                        <div class="col-md-12 col-sm-12 col-lg-10 mx-auto">
                            <ul class="nav nav-tabs tab-solid tab-solid-primary d-flex justify-content-around border-primary" role="tablist">
                                <li class="col-md-2 nav-item">
                                    <a class="d-flex justify-content-center nav-link active" id="monthly" data-toggle="tab" href="#monthly-plan" data-month-time="1" role="tab" aria-controls="monthly-plan" aria-selected="true">
                                        Mensal
                                    </a>
                                </li>
                                <li class="col-md-2 nav-item">
                                    <a class="d-flex justify-content-center nav-link" id="quarterly" data-toggle="tab" href="#quarterly-plan" data-month-time="3" role="tab" aria-controls="quarterly-plan" aria-selected="false">
                                        Trimestral
                                    </a>
                                </li>
                                <li class="col-md-2 nav-item">
                                    <a class="d-flex justify-content-center nav-link" id="semiannual" data-toggle="tab" href="#semiannual-plan" data-month-time="6" role="tab" aria-controls="semiannual-plan" aria-selected="false">
                                        Semestral
                                    </a>
                                </li>
                                <li class="col-md-2 nav-item">
                                    <a class="d-flex justify-content-center nav-link" id="annual" data-toggle="tab" href="#annual-plan" data-month-time="12" role="tab" aria-controls="annual-plan" aria-selected="false">
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
