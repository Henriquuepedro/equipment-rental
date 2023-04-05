@extends('adminlte::page')

@section('title', 'Relatório de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Relatório de Locação</h1>
@stop

@section('css')
    <link href="{{ asset('vendor/bootstrap4-duallistbox/bootstrap-duallistbox.css') }}" rel="stylesheet"/>
@stop

@section('js')
<script src="{{ asset('vendor/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.js') }}"></script>
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

        $('.select-listbox').bootstrapDualListbox({
            // default text
            filterTextClear: 'Mostrar todos',
            filterPlaceHolder: 'Filtro',
            moveSelectedLabel: 'Mover selecionado',
            moveAllLabel: 'Mover todos',
            removeSelectedLabel: 'Remover selecionado',
            removeAllLabel: 'Remover todos',

            // true/false (forced true on androids, see the comment later)
            moveOnSelect: true,

            // 'all' / 'moved' / false
            preserveSelectionOnMove: 'all',

            // 'string', false
            selectedListLabel: false,

            // 'string', false
            nonSelectedListLabel: false,

            // 'string_of_postfix' / false
            helperSelectNamePostfix: '_helper',

            // minimal height in pixels
            selectorMinimalHeight: 100,

            // whether to show filter inputs
            showFilterInputs: true,

            // string, filter the non selected options
            nonSelectedFilter: '',

            // string, filter the selected options
            selectedFilter: '',

            // text when all options are visible / false for no info text
            infoText: 'Mostrando todos {0}',

            // when not all of the options are visible due to the filter
            infoTextFiltered: '<span class="badge badge-warning">Filtrado</span> {0} de {1}',

            // when there are no options present in the list
            infoTextEmpty: 'Lista vazia',

            // sort by input order
            sortByInputOrder: false,

            // filter by selector's values, boolean
            filterOnValues: false,

            // boolean, allows user to unbind default event behaviour and run their own instead
            eventMoveOverride: true,

            // boolean, allows user to unbind default event behaviour and run their own instead
            eventMoveAllOverride: false,

            // boolean, allows user to unbind default event behaviour and run their own instead
            eventRemoveOverride: false,

            // boolean, allows user to unbind default event behaviour and run their own instead
            eventRemoveAllOverride: false,

            // sets the button style class for all the buttons
            btnClass: 'btn-outline-secondary',

            // string, sets the text for the "Move" button
            btnMoveText: '&gt;',

            // string, sets the text for the "Remove" button
            btnRemoveText: '&lt;',

            // string, sets the text for the "Move All" button
            btnMoveAllText: '&gt;&gt;',

            // string, sets the text for the "Remove All" button
            btnRemoveAllText: '&lt;&lt;'

        });

    });

    $('[name="state"]').on('change', function(){
        loadCities($('[name="city"]'), $(this).val(), 'Todos');
    });

    $('#date_filter').on('change', function(){
        const label = $(`#date_filter option[value="${$(this).val()}"]`).text();
        $('[for="intervalDates"] span.label_date_filter').text(label);
    });

    $('[name="type"]').on('change', async function (){

        const type = $(this).val();

        const base_uri  = $('[name="base_url"]').val() + '/ajax';
        const response  = await fetch(`${base_uri}/exportar/fields/${type}`);
        let results     = await response.json();
        let options     = '';

        await $(results).each(await function (key, value) {
            options += `<option value="${value}">${value}</option>`;
        });

        $('.select-listbox').empty().append(options);
        $(".select-listbox").bootstrapDualListbox('refresh');
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
                    <form action="{{ route('export.register') }}" method="POST" enctype="multipart/form-data" target="_blank">
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


                                    <section class="section">
                                        <div class="container">
                                            <h2 class="subtitle">
                                                Selecione os campos que deseja incluir no relatório.
                                            </h2>
                                            <select class="select-listbox" name="fields-selected[]" multiple></select>
                                        </div>
                                    </section>



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
