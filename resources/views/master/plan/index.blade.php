@extends('adminlte::page')

@section('title', 'Listagem de Planos')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Planos</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        var tablePlan;
        $(function () {
            tablePlan = getTable(false);
        });

        const getTable = (stateSave = true) => {

            return $("#tablePlans").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 3, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.master.plan.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content') },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "initComplete": function( settings, json ) {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                }
            });
        }

        $('#active').on('change', function(){
            tablePlan.destroy();
            $("#tablePlans tbody").empty();
            tablePlan = getTable();
        })
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
                        <h4 class="card-title no-border">Planos Cadastrados</h4>
                        <a href="{{ route('master.plan.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Plano</a>
                    </div>
                    <table id="tablePlans" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Valor</th>
                                <th>Quantidade de Equipamentos</th>
                                <th>Quantidade de Meses</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Nome</th>
                                <th>Valor</th>
                                <th>Quantidade de Equipamentos</th>
                                <th>Quantidade de Meses</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
