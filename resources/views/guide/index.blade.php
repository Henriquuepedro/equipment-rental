@extends('adminlte::page')

@section('title', 'Listagem de Manuais')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Manuais</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        var tableGuide;
        $(function () {
            tableGuide = getTable(false);
        });

        const getTable = (stateSave = true) => {
            return $("#tableGuides").DataTable({
                "scrollX": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 0, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.guide.fetch') }}',
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
                    "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
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
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Manuais Cadastrados</h4>
                    </div>
                    <table id="tableGuides" class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Título</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
