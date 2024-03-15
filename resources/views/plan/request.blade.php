@extends('adminlte::page')

@section('title', 'Solicitações')

@section('content_header')
    <h1 class="m-0 text-dark">Solicitações</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        let plans_table;

        $(function () {
            plans_table = getTable(false);
        });

        const getTable = (stateSave = true) => {
            return $("#plans_table").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 4, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.plan.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content') },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "initComplete": function( settings, json ) {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                }
            });
        }
    </script>
@stop

@section('content')
    <div class="row">
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
                    <table id="plans_table" class="table">
                        <thead>
                            <tr>
                                <th>Plano</th>
                                <th>Forma de Pagamento</th>
                                <th>Valor</th>
                                <th>Situação</th>
                                <th>Realizado em</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Plano</th>
                                <th>Forma de Pagamento</th>
                                <th>Valor</th>
                                <th>Situação</th>
                                <th>Realizado em</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
