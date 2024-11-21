@extends('adminlte::page')

@section('title', 'Listagem de Notificações')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Notificações</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script src="//cdn.datatables.net/plug-ins/1.13.7/api/processing().js" type="application/javascript"></script>
    <script>
        let tableNotification;
        $(function () {
            tableNotification = getTableNotification(false);
        });

        const getTableNotification = (stateSave = true) => {
            const read = $('#read').val();

            return getTableList(
                '{{ route('ajax.notification.fetch') }}',
                { read },
                'tableNotifications',
                stateSave,
                [ 2, 'desc' ],
                'POST',
                () => {},
                () => {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            );
        }

        $('#read').on('change', function(){
            tableNotification.destroy();
            $("#tableNotifications tbody").empty();
            tableNotification = getTableNotification();
        });
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
                        <h4 class="card-title no-border">Notificações</h4>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 form-group">
                            <label>Situação</label>
                            <select class="form-control" id="read">
                                <option value="1">Lidos</option>
                                <option value="0" selected>Não lidos</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <table id="tableNotifications" class="table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Situação</th>
                                <th>Criado Em</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Título</th>
                                <th>Situação</th>
                                <th>Criado Em</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
