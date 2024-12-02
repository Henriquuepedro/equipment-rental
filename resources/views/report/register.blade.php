@extends('adminlte::page')

@section('title', 'Relatório de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Relatório de Locação</h1>
@stop

@section('css')
    <link href="{{ asset('vendor/bootstrap4-duallistbox/bootstrap-duallistbox.css') }}" rel="stylesheet"/>
    <style>
        .bootstrap-duallistbox-container .box1,
        .bootstrap-duallistbox-container .box2 {
            width: 350px;
        }
    </style>
@stop

@section('js')
<script src="{{ asset('vendor/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.js') }}"></script>
<script>
    $(function(){
        $('.select-listbox').bootstrapDualListbox({
            // default text
            filterTextClear: 'Mostrar todos',
            filterPlaceHolder: 'Filtro',
            moveSelectedLabel: 'Mover selecionado',
            moveAllLabel: 'Mover todos',
            removeSelectedLabel: 'Remover selecionado',
            removeAllLabel: 'Remover todos',

            // minimal height in pixels
            selectorMinimalHeight: 200,

            selectorMinimalWidth: 300,

            // whether to show filter inputs
            showFilterInputs: true,

            infoText: 'Mostrando todos {0}',

            // when not all of the options are visible due to the filter
            infoTextFiltered: '<span class="badge badge-warning">Filtrado</span> {0} de {1}',

            // when there are no options present in the list
            infoTextEmpty: 'Lista vazia',

            // sets the button style class for all the buttons
            btnClass: 'btn-outline-secondary',
        });

    });

    $('[name="type"]').on('change', async function (){

        const type = $(this).val();

        const base_uri  = $('[name="base_url"]').val() + '/ajax';
        const response  = await fetch(`${base_uri}/exportar/fields/${type}`);
        let results     = await response.json();
        let options     = '';

        await Object.entries(results).forEach(([key, value]) => {
            options += `<option value="${key}">${value}</option>`;
        });

        $('.select-listbox').empty().append(options);
        $(".select-listbox").bootstrapDualListbox('refresh');
    });

</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
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
                    <form action="{{ route('export.register') }}" method="POST" enctype="multipart/form-data" target="_blank">
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
                                    <div class="col-md-12">
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
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Voltar</a>
                                <button type="submit" name="export_csv" class="btn btn-success col-md-3"><i class="fa fa-file-pdf"></i> Exportar em Excel</button>
                                <button type="submit" name="print_a4_h" class="btn btn-primary col-md-3"><i class="fa fa-file-pdf"></i> Gerar Relatório Horizontal</button>
                                <button type="submit" name="print_a4_v" class="btn btn-info col-md-3"><i class="fa fa-file-pdf"></i> Gerar Relatório Vertical</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
