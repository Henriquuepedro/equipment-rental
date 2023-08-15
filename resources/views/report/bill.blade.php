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
        $('#date_filter').trigger('change');
        moment.locale('pt-br');
        loadDaterangePickerInput($('input[name="intervalDates"]'), function () {});
        getOptionsForm('form-of-payment', $('[name="form_payment"]'), null, '<option value="0">Todos</option>');
        getOptionsForm('providers', $('[name="provider"]').select2(), null, '<option value="0">Todos</option>');
        getOptionsForm('clients', $('[name="client"]').select2(), null, '<option value="0">Todos</option>');
    });

    $('#bill_type').on('change', function(){
        const type = $(this).val();
        if (type === 'receive') {
            $('[name="client"]').closest('.form-group').show().find('select').val('0').select2();
            $('[name="provider"]').closest('.form-group').hide();
        } else if (type === 'pay') {
            $('[name="client"]').closest('.form-group').hide();
            $('[name="provider"]').closest('.form-group').show().find('select').val('0').select2();
        }
    });

    $('#date_filter').on('change', function() {
        const label = $(`#date_filter option[value="${$(this).val()}"]`).text();
        $('[for="intervalDates"] span.label_date_filter').text(label);
    });

    $('#bill_status').on('change', function() {
        if ($(this).val() === 'no_paid') {
            $('[name="form_payment"]').closest('.form-group').hide();
        } else {
            $('[name="form_payment"]').closest('.form-group').show().find('select').val('0').select2();
        }
    });

</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if ($errors->any())
                    <div class="alert alert-animate alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route('print.report_bill') }}" method="POST" enctype="multipart/form-data" target="_blank">
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
                                    <div class="form-group col-md-4">
                                        @include('includes.client.form', ['show_btn_create' => false, 'required' => false])
                                    </div>
                                    <div class="form-group col-md-4 display-none">
                                        @include('includes.provider.form', ['show_btn_create' => false, 'required' => false])
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="bill_type">Tipo de Relatório</label>
                                        <select class="form-control select2" id="bill_type" name="bill_type">
                                            <option value="receive">Receber</option>
                                            <option value="pay">Pagar</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="date_filter">Filtrar data por</label>
                                        <select class="form-control select2" id="date_filter" name="date_filter">
                                            <option value="created">Lançamento</option>
                                            <option value="due">Vencimento</option>
                                            <option value="pay">Pagamento</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="intervalDates">Data de <span class="label_date_filter"></span></label>
                                        <input type="text" id="intervalDates" name="intervalDates" class="form-control" value="{{ $settings['intervalDates']['start'] . ' - ' . $settings['intervalDates']['finish'] }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="bill_status">Situação do Lançamento</label>
                                        <select class="form-control select2" id="bill_status" name="bill_status">
                                            <option value="no_paid">Não Pago</option>
                                            <option value="paid">Pago</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 display-none">
                                        <label for="form_payment">Forma de Pagamento</label>
                                        <select class="form-control select2" id="form_payment" name="form_payment" required></select>
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
