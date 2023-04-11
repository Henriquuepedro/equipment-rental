@extends('adminlte::page')

@section('title', 'Relatório de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Relatório de Locação</h1>
@stop

@section('css')
@stop

@section('js')
<script>
    $(function(){
        $('[name="state"], [name="city"]').select2();
        loadStates($('[name="state"]'), null, 'Todos');
        loadCities($('[name="city"]'), null, null, 'Selecione o Estado');
        getOptionsForm('clients', $('[name="client"]').select2(), null, '<option value="0">Todos</option>');

        $('#date_filter').trigger('change');
        moment.locale('pt-br');
        $('input[name="intervalDates"]').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY'
            }
        });
    });

    $('[name="state"]').on('change', function(){
        loadCities($('[name="city"]'), $(this).val(), 'Todos');
    });

    $('#date_filter').on('change', function(){
        const label = $(`#date_filter option[value="${$(this).val()}"]`).text();
        $('[for="intervalDates"] span.label_date_filter').text(label);
    });

</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if ($errors->any())
                    <div class="alert-animate alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route('print.report_rental') }}" method="POST" enctype="multipart/form-data" target="_blank">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_report" value="synthetic" checked> Sintético <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_report" value="analytic" > Analítico <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                @if (count($companies))
                                    <div class="row @if (count($companies) === 1) d-none @endif">
                                        <div class="form-group col-md-12">
                                            <label for="company">Empresas</label>
                                            <select class="form-control select2" id="company" name="company" required>
                                                @if (count($companies) !== 1)<option value="0">Selecione a empresa</option>@endif
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="form-group col-md-7">
                                        @include('includes.client.form', ['show_btn_create' => false, 'required' => false])
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="status">Situação da Locação</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="0">Todas</option>
                                            <option value="deliver">Para Entregar</option>
                                            <option value="withdraw">Para Retirar</option>
                                            <option value="finished">Finalizada</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="driver">Motorista</label>
                                        <select class="form-control select2" id="driver" name="driver">
                                            <option value="0">Todos</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="vehicle">Veículo</label>
                                        <select class="form-control select2" id="vehicle" name="vehicle">
                                            <option value="0">Todos</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="equipment">Equipamento</label>
                                        <select class="form-control select2" id="equipment" name="equipment">
                                            <option value="0">Todos</option>
                                            @foreach($equipments as $equipment)
                                                <option value="{{ $equipment->id }}">{{ $equipment->name ?? "Caçamba {$equipment->volume}m³" }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="state">Estado</label>
                                        <select class="form-control" id="state" name="state"></select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="city">Cidade</label>
                                        <select class="form-control" id="city" name="city"></select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="date_filter">Filtrar data por</label>
                                        <select class="form-control" id="date_filter" name="date_filter">
                                            <option value="created">Lançamento</option>
                                            <option value="delivered">Entregue</option>
                                            <option value="withdrawn">Retirado</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="intervalDates">Data de <span class="label_date_filter"></span></label>
                                        <input type="text" id="intervalDates" name="intervalDates" class="form-control" value="{{ $settings['intervalDates']['start'] . ' - ' . $settings['intervalDates']['finish'] }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Voltar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-file-pdf"></i> Gerar Relatório</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
