@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('css')
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/views/dashboard/daily.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/views/dashboard/manage.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css">
    <link rel="stylesheet" type="text/css" href="https://leaflet.github.io/Leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" type="text/css" href="https://leaflet.github.io/Leaflet.markercluster/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" type="text/css" href="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css" />
    <link rel="stylesheet" type="text/css" href="//unpkg.com/leaflet-gesture-handling/dist/leaflet-gesture-handling.min.css" type="text/css">
    <style>
        #tabDashboard .nav-link {
            padding: 15px;
        }
        #mapRentals {
            height: 500px;
        }
        .equipments .card .card-header a:not([disabled="disabled"]){
            background: #2196f3 !important;
        }
        div[id^="headingEquipmentToExchange-"] a[disabled="disabled"] {
            background: #fb9678 !important;
        }
        div[id^="headingEquipmentToExchange-"][disabled="disabled"] {
            cursor: not-allowed;
        }
        .equipments .card .card-header a:last-child {
            padding-left: 1rem;
            padding-right: 1rem;
            overflow: unset;
        }
        .dataTables_wrapper .dataTable thead .sorting_asc:before,
        .dataTables_wrapper .dataTable thead .sorting_desc:before,
        .dataTables_wrapper .dataTable thead .sorting:before {
            right: 1.2em;
        }
    </style>
@stop

@section('js')
    <script src="https://leaflet.github.io/Leaflet.markercluster/dist/leaflet.markercluster-src.js"></script>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
    <script src="{{ asset('assets/vendors/justgage/raphael-2.1.4.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
    <script src="{{ asset('assets/vendors/justgage/justgage.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script src="//unpkg.com/leaflet-gesture-handling"></script>
    <script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ asset('assets/js/views/dashboard/daily.js') }}"></script>
    <script src="{{ asset('assets/js/views/dashboard/manage.js') }}"></script>
    <script src="{{ asset('assets/js/views/dashboard/index.js') }}"></script>
    @include('includes.rental.modal-script')
    <script src="https://www.mercadopago.com/v2/security.js" view="home"></script>
@stop

@section('content')
    <div class="row mb-2">
        <div class="col-md-3">
            <a href="{{ route('rental.create') }}" class="btn btn-primary col-md-12"> <i class="fa fa-plus"></i> Nova Locação</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('budget.create') }}" class="btn btn-primary col-md-12"> <i class="fa fa-plus"></i> Novo Orçamento</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('client.create') }}" class="btn btn-primary col-md-12"> <i class="fa fa-plus"></i> Novo Cliente</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('equipment.create') }}" class="btn btn-primary col-md-12"> <i class="fa fa-plus"></i> Novo Equipamento</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="nav-scroller">
                        <ul class="nav nav-pills nav-pills-success d-flex justify-content-center border-bottom-0 pb-0" role="tablist" id="tabDashboard">
                            <li class="nav-item col-md-3">
                                <a class="nav-link d-flex justify-content-center active" id="daily-tab" data-bs-toggle="tab" href="#daily" role="tab" aria-controls="daily" aria-selected="true">DIÁRIO</a>
                            </li>
                            <li class="nav-item col-md-3">
                                <a class="nav-link d-flex justify-content-center" id="manage-tab" data-bs-toggle="tab" href="#manage" role="tab" aria-controls="manage" aria-selected="false">GERENCIAL</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
{{--            <div class="card mt-2">--}}
{{--                <div class="card-body">--}}
                    <div class="tab-content border-0 p-0 mt-3">
                        <div class="tab-pane fade show active" id="daily" role="tabpanel" aria-labelledby="daily-tab">
                            @include('dashboard.daily_dashboard')
                        </div>
                        <div class="tab-pane fade" id="manage" role="tabpanel" aria-labelledby="manage-tab">
                            @include('dashboard.manage_dashboard')
                        </div>
{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>
    </div>
    @include('includes.rental.modal-view')
@stop
