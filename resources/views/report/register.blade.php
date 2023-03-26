@extends('adminlte::page')

@section('title', 'Relatório de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Relatório de Locação</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendors/dragula/dragula.min.css') }}">
@stop

@section('js')
<script src="{{ asset('assets/vendors/dragula/dragula.min.js') }}"></script>
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

        let iconTochange;
        dragula([document.getElementById("dragula-event-left"), document.getElementById("dragula-event-right")])
        .on('drop', function(el) {
            iconTochange = $(el).find('.mdi');
            if ($(el).closest('#dragula-event-right').length) {
                iconTochange.removeClass('mdi-close text-danger').addClass('mdi-check text-success');
            } else {
                iconTochange.removeClass('mdi-check text-success').addClass('mdi-close text-danger');
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
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="company">Empresas</label>
                                            <select class="form-control select2" id="company" name="company" required>
                                                <option value="0">Selecione a empresa</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="type">Tipo de Cadastro</label>
                                        <select class="form-control select2" id="type" name="type">
                                            <option value="0">Seleciona o tipo</option>
                                            <option value="client">Cliente</option>
                                            <option value="provider">Fornecedor</option>
                                            <option value="equipment">Equipamento</option>
                                            <option value="driver">Motorista</option>
                                            <option value="vehicle">Veiculo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="card-title">Não selecionados</h6>
                                        <div id="dragula-event-left" class="py-2">
                                            <div class="card rounded border mb-2">
                                                <div class="card-body p-2">
                                                    <div class="media d-flex justify-content-start flex-nowrap align-items-center">
                                                        <i class="mdi mdi-close icon-sm text-danger align-self-center mr-2"></i>
                                                        <div class="media-body">
                                                            <h6 class="mb-0">Build wireframe</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="card-title">Selecionados</h6>
                                        <div id="dragula-event-right" class="py-2">

                                        </div>
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
