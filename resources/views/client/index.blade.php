@extends('adminlte::page')

@section('title', 'Listagem de Clientes')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Clientes</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@stop

@section('js')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script>
        $(function () {
            $("#tableClients").DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                },
                "order": [[ 0, 'desc' ]],
                "initComplete": function( settings, json ) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        });
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @if(session('success'))
                <div class="alert alert-pri mt-2">{{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
{{--                <div class="card-header d-flex justify-content-between align-items-center">--}}
{{--                    <h3 class="card-title">Clientes Cadastrados</h3>--}}
{{--                    <a href="{{ route('client.create') }}" class="btn btn-primary col-md-3"><i class="fas fa-plus"></i> Novo Cadastro</a>--}}
{{--                </div>--}}
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Clientes Cadastrados</h4>
                        <a href="{{ route('client.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                    </div>
                    <table id="tableClients" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Ação</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($dataClients as $client)
                            <tr client-id="{{ $client['id'] }}">
                                <td data-order="{{ $client['id'] }}" class="text-center">{{ $client['id'] }}</td>
                                <td>{{ $client['name'] }}</td>
                                <td>{{ $client['email'] }}</td>
                                <td>{{ $client['phone_1'] }}</td>
                                <td class="text-center d-md-flex justify-content-center margin-btn">
                                    <a href="{{ route('client.edit', ['id' => $client['id']]) }}" class="btn btn-primary btn-sm btn-rounded btn-action" data-toggle="tooltip" title="Editar" ><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-danger btnRemoveClient btn-sm btn-rounded btn-action ml-md-1" data-toggle="tooltip"  title="Excluir"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
