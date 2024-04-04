@extends('adminlte::page')

@section('title', 'Logs de auditoria')

@section('content_header')
    <h1 class="m-0 text-dark">Logs de auditoria</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('js')
    <script type="application/javascript" src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="application/javascript" src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js"></script>
    <script>
        var tablePlan;
        $(function () {
            tablePlan = getTable(false);
            loadDaterangePickerInput($('input[name="intervalDates"]'), function () {}, FORMAT_DATETIME_BRAZIL, true);
        });

        const getTable = (stateSave = true) => {

            return $("#tableLogs").DataTable({
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
                    url: '{{ route('ajax.master.audit_log.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content') ,
                        "auditable_type" : $('[name="auditable_type"]').val(),
                        "event" : $('[name="event"]').val(),
                        "user" : $('[name="user"]').val(),
                        "auditable_id" : $('[name="auditable_id"]').val(),
                        "company" : $('[name="company"]').val(),
                        "intervalDates" : $('[name="intervalDates"]').val(),
                    },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "initComplete": function( settings, json ) {
                    $(json.data).each(function(k, v){
                        if (v[2]) {
                            $(`#tableLogs tbody tr:eq(${k}) td:eq(2)`).text($(`[name="user"] option[value="${v[2]}"]`).text());
                        }
                    });
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                }
            });
        }

        $('[name="auditable_type"], [name="event"], [name="user"], [name="auditable_id"], [name="company"], [name="intervalDates"]').on('change', function(){
            tablePlan.destroy();
            $("#tableLogs tbody").empty();
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
                    <div class="header-card-body">
                        <h4 class="card-title no-border">Logs de auditoria</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Loja</label>
                            <select class="form-control select2" name="company">
                                <option value="0">Todas</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Módulo</label>
                            <select class="form-control select2" name="auditable_type">
                                <option value="0">Todos</option>
                                @foreach($auditable_types as $auditable_type)
                                    <option value="{{ $auditable_type }}">{{ $auditable_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Evento</label>
                            <select class="form-control select2" name="event">
                                <option value="0">Todos</option>
                                @foreach($events as $event)
                                    <option value="{{ $event }}">{{ $event }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 form-group">
                            <label>Usuário</label>
                            <select class="form-control select2" name="user">
                                <option value="0">Todos</option>
                                @foreach($users as $user)
                                    <option value="{{ $user['id'] }}">{{ $user['email'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Código do módulo</label>
                            <input type="number" class="form-control" name="auditable_id">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="label-date-btns" for="date_filter_by">Data do evento</label>
                            <input type="text" name="intervalDates" class="form-control"
                                   value="{{ dateNowInternational(null, DATE_BRAZIL) . ' 00:00:00' . ' - ' . dateNowInternational(null, DATE_BRAZIL) . ' 23:59:59' }}"
                                   data-can-enable="true"
                                   style="text-decoration: unset; color: unset"/>
                        </div>
                    </div>
                    <table id="tableLogs" class="table">
                        <thead>
                            <tr>
                                <th>Módulo</th>
                                <th>Evento</th>
                                <th>Usuário</th>
                                <th>Código</th>
                                <th>Data do evento</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Módulo</th>
                                <th>Evento</th>
                                <th>Usuário</th>
                                <th>Código</th>
                                <th>Data do evento</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
