@extends('adminlte::page')

@section('title', 'Listagem de Contas a Receber')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Contas a Receber</h1>
@stop

@section('css')
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link href="{{ asset('assets/css/views/bills_to_receive.css') }}" rel="stylesheet">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
    <script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script src="//cdn.datatables.net/plug-ins/1.13.7/api/processing().js" type="application/javascript"></script>
    <script src="{{ asset('assets/js/views/bill_to_receive/index.js') }}" type="application/javascript"></script>

    @include('includes.driver.modal-script')
    @include('includes.vehicle.modal-script')
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
            @include('includes.bill_to_receive.content', ['show_select_client' => true, 'card_title' => 'Contas a Receber'])
        </div>
    </div>
    @include('includes.bill_to_receive.confirm_payment')
    @include('includes.bill_to_receive.view_payment')
    @include('includes.bill_to_receive.reopen')
@stop
