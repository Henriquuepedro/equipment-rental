@extends('adminlte::page')

@section('title', 'Visualizar Log')

@section('content_header')
    <h1 class="m-0 text-dark">Visualizar Log</h1>
@stop

@section('css')
@stop

@section('js')
<script>
    $(function(){
        $('[name="value"], [name="from_value"]').maskMoney({thousands: '.', decimal: ',', allowZero: true});

        $("#tableLogs").DataTable({
            "scrollX": true,
            "autoWidth": false,
            "sortable": true,
            "searching": true
        });
    });
</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Visualizar Log</h4>
                                <p class="card-description"> Logs de auditoria </p>
                            </div>
                            <div class="col-md-12 no-padding">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Loja</label>
                                        <input type="text" class="form-control" value="{{ $log->company_name }}" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Módulo</label>
                                        <input type="text" class="form-control" value="{{ $log->auditable_type }}" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Evento</label>
                                        <input type="text" class="form-control" value="{{ $log->event }}" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Usuário</label>
                                        <input type="text" class="form-control" value="{{ $log->user_email }}" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Código do módulo</label>
                                        <input type="text" class="form-control" value="{{ $log->auditable_id }}" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Data do evento</label>
                                        <input type="text" class="form-control" value="{{ formatDateInternational($log->created_at, DATETIME_BRAZIL) }}" readonly>
                                    </div>
                                </div>

                                @if($log->event === 'updated')
                                    <table class="table mt-3">
                                        <thead>
                                            <tr>
                                                <th class="bg-primary text-white">Campo</th>
                                                <th class="bg-primary text-white">Antes</th>
                                                <th class="bg-primary text-white">Depois</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($log->details['new'] as $field_name => $_)
                                                <tr>
                                                    @php
                                                        $new = $log->details['new'][$field_name];
                                                        $old = $log->details['old'][$field_name];

                                                        if (strtotime($new) !== false) {
                                                            $new = dateInternationalToDateBrazil($new) ?? $new;
                                                        }
                                                        if (strtotime($old) !== false) {
                                                            $old = dateInternationalToDateBrazil($old) ?? $old;
                                                        }

                                                        if (likeText('%cpf%', $field_name) || likeText('%cnpj%', $field_name)) {
                                                            $new = formatCPF_CNPJ($new, '') ?? $new;
                                                        }
                                                        if (likeText('%cpf%', $field_name) || likeText('%cnpj%', $field_name)) {
                                                            $old = formatCPF_CNPJ($old, '') ?? $old;
                                                        }

                                                        if (likeText('%phone%', $field_name)) {
                                                            $new = formatPhone($new, '') ?? $new;
                                                        }
                                                        if (likeText('%phone%', $field_name)) {
                                                            $old = formatPhone($old, '') ?? $old;
                                                        }
                                                    @endphp
                                                    <td class="bg-white text-black">{{ Lang::get("field.$field_name") }}</td>
                                                    <td class="bg-danger text-white">{{ $old }}</td>
                                                    <td class="bg-success text-white">{{ $new }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @elseif($log->event === 'deleted')
                                    <table class="table mt-3">
                                        <thead>
                                        <tr>
                                            <th class="bg-primary text-white">Campo</th>
                                            <th class="bg-primary text-white">Antes</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($log->details as $field_name => $field_value)
                                            <tr>
                                                @php
                                                    if (strtotime($field_value) !== false) {
                                                        $field_value = dateInternationalToDateBrazil($field_value) ?? $field_value;
                                                    }

                                                    if (likeText('%cpf%', $field_name) || likeText('%cnpj%', $field_name)) {
                                                        $field_value = formatCPF_CNPJ($field_value, '') ?? $field_value;
                                                    }

                                                    if (likeText('%phone%', $field_name)) {
                                                        $field_value = formatPhone($field_value, '') ?? $field_value;
                                                    }
                                                @endphp
                                                <td class="bg-white text-black">{{ Lang::get("field.$field_name") }}</td>
                                                <td class="bg-danger text-white">{{ $field_value }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Registros relacionados</h4>
                                <p class="card-description"> Logs relacionados com o registro do módulo <b class="text-primary">{{ $log->auditable_type }}</b> e Código <b class="text-primary">{{ $log->auditable_id }}</b></p>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
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
                                            @foreach($relationship_logs as $relationship_log)
                                                <tr>
                                                    <td class="{{ $relationship_log->id == $log->id ? 'bg-primary text-white' : '' }}">{{ $relationship_log->auditable_type }}</td>
                                                    <td class="{{ $relationship_log->id == $log->id ? 'bg-primary text-white' : '' }}">{{ $relationship_log->event }}</td>
                                                    <td class="{{ $relationship_log->id == $log->id ? 'bg-primary text-white' : '' }}">{{ $relationship_log->user_email }}</td>
                                                    <td class="{{ $relationship_log->id == $log->id ? 'bg-primary text-white' : '' }}">{{ $relationship_log->auditable_id }}</td>
                                                    <td class="{{ $relationship_log->id == $log->id ? 'bg-primary text-white' : '' }}">{{  formatDateInternational($relationship_log->created_at, DATETIME_BRAZIL) }}</td>
                                                    <td class="{{ $relationship_log->id == $log->id ? 'bg-primary text-white' : '' }}">@if($relationship_log->id != $log->id) <a href='{{ route('master.audit_log.view', ['id' => $relationship_log->id]) }}' class="btn btn-primary"><i class='fas fa-eye'></i> Visualizar Log</a>@endif</td>
                                                </tr>
                                            @endforeach
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
                    </div>
                    <div class="card mt-2">
                        <div class="card-body">
                            <a href="{{ route('master.audit_log.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
