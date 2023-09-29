@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('css')
@stop

@section('js')
    <script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ asset('assets/vendors/justgage/raphael-2.1.4.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/justgage/justgage.js') }}"></script>
    <script src="{{ asset('assets/vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('assets/js/demo_1/dashboard.js') }}"></script>
@stop

@section('content')
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card card-statistics">
            <div class="row">
                <div class="card-col col-xl-3 col-lg-3 col-md-3 col-6">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center flex-column flex-sm-row">
                            <i class="mdi mdi-account-multiple-outline text-primary mr-0 mr-sm-4 icon-lg"></i>
                            <div class="wrapper text-center text-sm-left">
                                <p class="card-text mb-0">Total clientes ativos</p>
                                <div class="fluid-container">
                                    <h3 class="mb-0 font-weight-medium">{{ $indicator['clients'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-col col-xl-3 col-lg-3 col-md-3 col-6">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center flex-column flex-sm-row">
                            <i class="mdi mdi-checkbox-marked-circle-outline text-primary mr-0 mr-sm-4 icon-lg"></i>
                            <div class="wrapper text-center text-sm-left">
                                <p class="card-text mb-0">Total equipamentos</p>
                                <div class="fluid-container">
                                    <h3 class="mb-0 font-weight-medium">{{ $indicator['equipments'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-col col-xl-3 col-lg-3 col-md-3 col-6">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center flex-column flex-sm-row">
                            <i class="mdi mdi-trophy-outline text-primary mr-0 mr-sm-4 icon-lg"></i>
                            <div class="wrapper text-center text-sm-left">
                                <p class="card-text mb-0">Total veículos</p>
                                <div class="fluid-container">
                                    <h3 class="mb-0 font-weight-medium">{{ $indicator['vehicles'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-col col-xl-3 col-lg-3 col-md-3 col-6">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center flex-column flex-sm-row">
                            <i class="mdi mdi-target text-primary mr-0 mr-sm-4 icon-lg"></i>
                            <div class="wrapper text-center text-sm-left">
                                <p class="card-text mb-0">Total locações realizadas</p>
                                <div class="fluid-container">
                                    <h3 class="mb-0 font-weight-medium">{{ $indicator['rentals'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Clientes novos</h4>
                    <div id="line-traffic-legend"></div>
                </div>

                <canvas id="lineChart" style="height:250px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Locações realizadas</h4>
                    <div id="area-traffic-legend"></div>
                </div>

                <canvas id="areaChart" style="height:250px"></canvas>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Faturamento</h4>
                    <div id="bar-traffic-legend"></div>
                </div>

                <canvas id="barChart" style="height:250px"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <p>Clientes que mais locaram</p>
                <ul class="bullet-line-list pb-3" id="top_clients_rental"></ul>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="route_new_clients_for_month" value="{{ route('ajax.client.get-new-client-for-month', array('months' => 9)) }}">
<input type="hidden" id="route_rentals_for_month" value="{{ route('ajax.rental.get-rentals-for-month', array('months' => 9)) }}">
<input type="hidden" id="route_bills_for_month" value="{{ route('ajax.bills_to_receive.get-bills-for-month', array('months' => 9)) }}">
<input type="hidden" id="route_clients_top_rentals" value="{{ route('ajax.client.get-clients-top-rentals', array('count' => 8)) }}">
@stop
