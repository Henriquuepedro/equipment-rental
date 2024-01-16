@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/views/dashboard/daily.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/views/dashboard/manage.css') }}">
    <style>
        #tabDashboard .nav-link {
            padding: 15px;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('assets/vendors/justgage/raphael-2.1.4.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/justgage/justgage.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ asset('assets/js/views/dashboard/daily.js') }}"></script>
    <script src="{{ asset('assets/js/views/dashboard/manage.js') }}"></script>
    <script>
        $('#tabDashboard a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            initCharts();
        });
    </script>
@stop

@section('content')
    @lang(
        "If you're having trouble clicking the  button, copy and paste the URL below\n".
        'into your web browser:'
    )
    <div class="nav-scroller mb-3">
        <ul class="nav nav-tabs tickets-tab-switch d-flex justify-content-center" role="tablist" id="tabDashboard">
            <li class="nav-item col-md-3">
                <a class="nav-link d-flex justify-content-center active" id="daily-tab" data-toggle="tab" href="#daily" role="tab" aria-controls="daily" aria-selected="true">DIÁRIO</a>
            </li>
            <li class="nav-item col-md-3">
                <a class="nav-link d-flex justify-content-center" id="manage-tab" data-toggle="tab" href="#manage" role="tab" aria-controls="manage" aria-selected="false">GERENCIAL</a>
            </li>
        </ul>
    </div>
    <div class="tab-content tab-content-basic">
        <div class="tab-pane fade show active" id="daily" role="tabpanel" aria-labelledby="daily-tab">
            @include('dashboard.daily_dashboard')
        </div>
        <div class="tab-pane fade" id="manage" role="tabpanel" aria-labelledby="manage-tab">
            @include('dashboard.manage_dashboard')
        </div>
    </div>
@stop
