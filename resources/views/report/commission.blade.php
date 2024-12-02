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
        moment.locale('pt-br');
        loadDaterangePickerInput($('input[name="intervalDates"]'), function () {});
    });
    $('#company').on('change', function() {
        const company_id = $(this).val();
        getOptionsForm('drivers', $('[name="drivers"]'), null, null, company_id);
    })

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
                    <form action="{{ route('export.driver_commission') }}" method="POST" enctype="multipart/form-data" target="_blank">
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
                                    <div class="form-group col-md-9">
                                        <label for="type">Motorista</label>
                                        <select class="form-control select2" id="drivers" name="drivers"></select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="intervalDates">Data de entrega/retirada</label>
                                        <input type="text" id="intervalDates" name="intervalDates" class="form-control" value="{{ $settings['intervalDates']['start'] . ' - ' . $settings['intervalDates']['finish'] }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Voltar</a>
                                <button type="submit" name="print_a4_v" class="btn btn-info col-md-3"><i class="fa fa-file-pdf"></i> Gerar Relatório</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
