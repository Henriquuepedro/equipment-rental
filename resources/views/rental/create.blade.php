@extends('adminlte::page')

@section('title', 'Cadastro de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Locação</h1>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <style>
        .wizard > .actions > ul {
            display: flex;
            justify-content: space-between;
        }
        .wizard > .content {
            background: #FFF;
            border: 1px solid #eee;
        }
        .wizard > .content > .body {
            height: unset;
        }
        .show-address {
            display: none;
        }
        .flatpickr a.input-button,
        .flatpickr button.input-button{
            height: calc(1.5em + 0.75rem + 3px);
            width: 50%;
            /*text-align: center;*/
            /*padding-top: 13%;*/
            cursor: pointer;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .flatpickr a.input-button:last-child,
        .flatpickr button.input-button:last-child{
            border-bottom-right-radius: 5px;
            border-top-right-radius: 5px;
        }
        [name^="date_withdrawal"], [name^="date_delivery"], [name="stock_equipment"] {
            border-bottom-right-radius: 0 !important;
            border-top-right-radius: 0 !important;;
        }
        .input-group-append.btn-primary{
            background: #2196f3;
            cursor: pointer;
        }
        .input-group-append.btn-success{
            background: #19d895;
            cursor: pointer;
        }
        .input-group-append.btn-danger{
            background: #ff6258;
            cursor: pointer;
        }
        .list-equipment .equipment {
            cursor: pointer;
        }
        .list-equipment .equipment:hover {
            background: #f5f5f5;
        }
        .wizard > .content > .body {
            position: unset;
        }
        .accordion .card .card-header a{
            background: #2196f3 !important;
        }
        .remove-equipment i {
            cursor: pointer;
        }
        .accordion.accordion-multiple-filled .card .card-header a:last-child {
            padding-left: 1rem;
            padding-right: 1rem;
            overflow: unset;
        }
        i.fa.input-group-text.text-white {
            font-size: 15px;
        }
        .stepRental.body {
            width: 100% !important;
        }
        .content-equipment {
            max-height: 300px;
        }
        .content-equipment::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px #2196f3;
            border-radius: 10px;
            background-color: #F5F5F5;
        }
        .content-equipment::-webkit-scrollbar {
            width: 12px;
            background-color: #F5F5F5;
        }
        .content-equipment::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px #2196f3;
            background-color: #52a4e5;
        }
        .calendar_equipment a {
            height: calc(1.5em + 0.75rem + 4px) !important;
        }
        .calendar_equipment i {
            font-size: 14px !important;
        }
        a[disabled] {
            pointer-events: none;
            cursor: no-drop;
        }
        .list-equipments-payment li.one-li-list-equipments-payment:after{
            display: none;
        }
        .btn-view-price-period-equipment {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 5px;
            height: calc(1.5em + 0.75rem + 3px);
        }
        .btn-view-price-period-equipment i {
            font-size: 18px !important;
            color: #fff;
        }
        #payment input:disabled {
            background-color: #eee;
        }
        #payment .input-group-text {
            background-color: #eee;
        }
        #payment.payment-no .payment-hidden {
            display: none !important;
        }
        #payment.payment-no .payment-hidden-invert-stock {
            justify-content: flex-end !important;
            display: flex !important;;
        }
        .wizard > .content > .body input.select2-search__field {
            border: 0;
        }
        .wizard > .content > .body ul {
            padding-top: 5px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #2196f3 !important;
        }
        .container-residues .select2.select2-container {
            position: relative;
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            width: 1%;
            margin-bottom: 0;
        }
        #observationDiv {
            border-top-right-radius: 0;
            border-top-left-radius: 0;
        }
        .ql-tooltip {
            left: 5% !important;
        }
        ul[id^="select2-residues"] li[aria-selected="true"] {
            display: none;
        }
    </style>
@stop

@section('js')
<script src="{{ asset('assets/vendors/jquery-steps/jquery.steps.min.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/js/views/rental/form.js') }}" type="application/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
<script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>

@include('includes.client.modal-script')
@include('includes.address.modal-script')
@include('includes.equipment.modal-script')
@include('includes.driver.modal-script')
@include('includes.vehicle.modal-script')
@include('includes.residue.modal-script')

    @if(in_array('ClientCreatePost', $permissions))    <script src="{{ asset('assets/js/views/client/form.js') }}" type="application/javascript"></script>    @endif
    @if(in_array('EquipmentCreatePost', $permissions)) <script src="{{ asset('assets/js/views/equipment/form.js') }}" type="application/javascript"></script> @endif

@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-md-12">
                @if ($errors->any())
                    <div class="alert-animate alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route(('ajax.rental.new-rental')) }}" method="POST" enctype="multipart/form-data" id="formCreateRental" class="pb-2">
                                <h3>Tipo de Locação</h3>
                                <div class="stepRental">
                                    <h6>Tipo de Locação <i class="fa fa-info-circle" data-toggle="tooltip" title="Defina se haverá ou não cobrança para essa locação."></i></h6>
                                    <div class="row">
                                        <div class="d-flex justify-content-around col-md-12">
                                            <div class="">
                                                <input type="radio" name="type_rental" id="have-payment" value="0" @if(old('type_person') === '0') checked @endif>
                                                <label for="have-payment">Com Cobrança</label>
                                            </div>
                                            <div class="">
                                                <input type="radio" name="type_rental" id="no-have-payment" value="1" @if(old('type_person') === '1') checked @endif>
                                                <label for="no-have-payment">Sem Cobrança</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Cliente e Endereço</h3>
                                <div class="stepRental">
                                    <h6>Cliente e Endereço</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 label-animate">
                                            @include('includes.client.form')
                                        </div>
                                    </div>
                                    @include('includes.address.form')
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-2">
                                            <div class="alert alert-warning alert-mark-map text-center display-none">O endereço selecionado não foi confirmado no mapa no cadastro do cliente, isso pode acarretar uma má precisão da localização.</div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Datas</h3>
                                <div class="stepRental">
                                    <h6>Datas</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group flatpickr d-flex">
                                                <label class="label-date-btns">Data Prevista de Entrega <sup>*</sup></label>
                                                <input type="text" name="date_delivery" class="form-control col-md-9" value="{{ date('d/m/Y H:i') }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                                                <div class="input-button-calendar col-md-3 no-padding">
                                                    <a class="input-button pull-left btn-primary" title="toggle" data-toggle>
                                                        <i class="fa fa-calendar text-white"></i>
                                                    </a>
                                                    <a class="input-button pull-right btn-primary" title="clear" data-clear>
                                                        <i class="fa fa-times text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group flatpickr d-flex">
                                                <label class="label-date-btns">Data Prevista de Retirada</label>
                                                <input type="text" name="date_withdrawal" class="form-control col-md-9" value="{{ date('d/m/Y H:i', strtotime('+1 minute', time())) }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                                                <div class="input-button-calendar col-md-3 no-padding">
                                                    <a class="input-button pull-left btn-primary" title="toggle" data-toggle>
                                                        <i class="fa fa-calendar text-white"></i>
                                                    </a>
                                                    <a class="input-button pull-right btn-primary" title="clear" data-clear>
                                                        <i class="fa fa-times text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="switch pt-1">
                                                    <input type="checkbox" class="check-style check-xs" name="not_use_date_withdrawal" id="not_use_date_withdrawal" value="1">
                                                    <label for="not_use_date_withdrawal" class="check-style check-xs"></label> Não informar data de retirada
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Equipamentos</h3>
                                <div class="stepRental">
                                    <h6>Equipamentos</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-2 label-animate container-residues display-none">
                                            <label>Resíduos a serem utilizados</label>
                                            <div class="input-group label-animate">
                                                <select class="select2 form-control" multiple="multiple" name="residues[]"></select>
                                                <div class="input-group-addon input-group-append">
                                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newResidueModal" title="Novo Resíduo" @if(!in_array('ResidueCreatePost', $permissions)) disabled @endif><i class="fas fa-plus-circle"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 mt-2 equipments-selected">
                                            <div class="accordion accordion-multiple-filled" id="equipments-selected" role="tablist">
                                            </div>
                                            <hr class="separator-dashed mt-4 display-none">
                                        </div>
                                        <div class="form-group col-md-12 mt-2">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="searchEquipment" placeholder="Pesquise por nome, referência ou código">
                                                <div class="input-group-addon input-group-append btn-primary">
                                                    <i class="fa fa-search input-group-text text-white"></i>
                                                </div>
                                                <div class="input-group-addon input-group-append btn-danger" id="cleanSearchEquipment">
                                                    <i class="fa fa-times input-group-text text-white"></i>
                                                </div>
                                                <div class="input-group-addon input-group-append btn-success" @if(in_array('EquipmentCreatePost', $permissions))id="newEquipment" data-toggle="modal" data-target="#newEquipmentModal"@else disabled @endif>
                                                    <i class="fa fa-plus input-group-text text-white"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 mt-2 table-responsive content-equipment">
                                            <table class="table list-equipment d-table">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-left"><h6 class="text-center"><i class="fas fa-search"></i> Pesquise por um equipamento</h6></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <h3>Pagamento</h3>
                                <div class="stepRental">
                                    <h6>Pagamento</h6>
                                    <div class="row">
                                        <div id="payment" class="col-md-12 mt-3">
                                            <div class="col-md-12 grid-margin stretch-card">
                                                <ul class="bullet-line-list pl-3 col-md-12 list-equipments-payment"></ul>
                                                <div class="pl-3 col-md-12 list-equipments-payment-load text-center">
                                                    <h4>Carregando equipamentos <i class="fa fa-spinner fa-spin"></i></h4>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed payment-hidden">
                                            <div class="col-md-12 payment-hidden">
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Valor Bruto</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <span type="text" class="form-control d-flex align-items-center" id="gross_value"></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Acréscimo</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" value="0,00" id="extra_value" name="extra_value">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Desconto</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" value="0,00" id="discount_value" name="discount_value">
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed payment-hidden">
                                            <div class="col-md-12 payment-hidden">
                                                <div class="d-flex justify-content-end align-items-center mb-2 flex-wrap">
                                                    <label class="mb-0 mr-md-2">Valor Líquido</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control d-flex align-items-center" id="net_value" name="net_value" disabled>
                                                    </div>
                                                    <div class="form-group col-md-12 no-padding text-right mt-2">
                                                        <div class="switch">
                                                            <input type="checkbox" class="check-style check-xs" name="calculate_net_amount_automatic" id="calculate_net_amount_automatic" checked>
                                                            <label for="calculate_net_amount_automatic" class="check-style check-xs"></label> Calcular Valor Líquido
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed payment-hidden">
                                            <div class="col-md-12 d-flex justify-content-between payment-hidden">
                                                <div class="form-group">
                                                    <div class="switch">
                                                        <input type="checkbox" class="check-style check-xs" name="is_parceled" id="is_parceled">
                                                        <label for="is_parceled" class="check-style check-xs"></label> Gerar Parcelamento
                                                    </div>
                                                </div>
                                                <div class="form-group display-none automatic_parcel_distribution_parent">
                                                    <div class="switch">
                                                        <input type="checkbox" class="check-style check-xs" name="automatic_parcel_distribution" id="automatic_parcel_distribution" checked>
                                                        <label for="automatic_parcel_distribution" class="check-style check-xs"></label> Distribuir Valores
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-success display-none" id="add_parcel"><i class="fa fa-plus"></i> Parcela</button>
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-danger display-none" id="del_parcel" disabled><i class="fa fa-trash"></i> Parcela</button>
                                                </div>
                                            </div>
                                            <div class="col-md-12 display-none payment-hidden" id="parcels"></div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Finalização</h3>
                                <div class="stepRental">
                                    <h6>Finalizar</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-3">
                                            <h5>Observação da Locação</h5>
                                            <div id="observationDiv" class="quill-container"></div>
                                            <textarea type="hidden" class="d-none" name="observation" id="observation"></textarea>
                                        </div>
                                    </div>
                                </div>
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>
{{--                    <div class="card">--}}
{{--                        <div class="card-body d-flex justify-content-between">--}}
{{--                            <a href="{{ route('driver.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>--}}
{{--                            <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
{{--                <div class="col-md-4">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-body">--}}
{{--                            <p>Em andamento ...</p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>
    </div>
    @if(in_array('ClientCreatePost', $permissions))    @include('includes.client.modal-create')    @endif
    @if(in_array('EquipmentCreatePost', $permissions)) @include('includes.equipment.modal-create') @endif
    @if(in_array('VehicleCreatePost', $permissions))   @include('includes.vehicle.modal-create')   @endif
    @if(in_array('DriverCreatePost', $permissions))    @include('includes.driver.modal-create')    @endif
    @if(in_array('ResidueCreatePost', $permissions))   @include('includes.residue.modal-create')   @endif
    <div class="modal fade" tabindex="-1" role="dialog" id="confirmAddressRental">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Endereço no Mapa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group text-center mb-2">
                            <button type="button" class="btn btn-primary" id="updateLocationMapRental"><i class="fas fa-search"></i> Buscar endereço do formulário</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <div id="mapRental" style="height: 400px"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary col-md-3" data-dismiss="modal">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="routeGetStockEquipment" value="{{ route('ajax.equipment.get-stock') }}">
    <input type="hidden" id="routeGetPriceEquipment" value="{{ route('ajax.equipment.get-price') }}">
    <input type="hidden" id="routeGetEquipment" value="{{ route('ajax.equipment.get-equipment') }}">
    <input type="hidden" id="routeGetPriceStockEquipments" value="{{ route('ajax.equipment.get-price-stock-check') }}">
    <input type="hidden" id="routeGetEquipments" value="{{ route('ajax.equipment.get-equipments') }}">
    <input type="hidden" id="routeGetPriceStockPeriodEquipment" value="{{ route('ajax.equipment.get-price-per-period') }}">
    <input type="hidden" id="routeGetVehicle" value="{{ route('ajax.vehicle.get-vehicle') }}">
    <div class="modal fade" tabindex="-1" role="dialog" id="createRental">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Locação criada com sucesso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="text-center code_rental">Locação código <strong></strong> criada com sucesso!</h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 text-center">
                            <a href="" target="_blank" class="col-md-4 btn btn-primary rental_print"> <i class="fa fa-print"></i> Imprimir Recibo</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-around flex-wrap mt-5">
                            <a href="{{ route('rental.index') }}" class="btn btn-primary col-md-4">Listagem de Locações</a>
                            <a href="{{ route('rental.create') }}" class="btn btn-secondary col-md-4">Realizar nova Locação</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
