@extends('adminlte::page')

@section('title', 'Listagem de Usuários')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Usuários</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        let tableUser;
        $(function () {
            tableUser = getTable(false);
        });

        const getTable = (stateSave = true) => {

            const active = $('#active').val();

            return $("#tableUsers").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 6, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.master.user.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content'), active },
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

        $('#active').on('change', function(){
            tableUser.destroy();
            $("#tableUsers tbody").empty();
            tableUser = getTable();
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
                        <h4 class="card-title no-border">Usuários Cadastrados</h4>
                        <a href="{{ route('master.user.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 form-group">
                            <label>Situação</label>
                            <select class="form-control" id="active">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <table id="tableUsers" class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Situação</th>
                                <th>Tipo de usuário</th>
                                <th>Último acesso em</th>
                                <th>Criado em</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Situação</th>
                                <th>Tipo de usuário</th>
                                <th>Último acesso em</th>
                                <th>Criado em</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
