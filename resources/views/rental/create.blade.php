@extends('adminlte::page')

@section('title', $budget ? 'Cadastro de Orçamento' : 'Cadastro de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de {{ $budget ? 'Orçamento' : 'Locação' }}</h1>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/views/rental.css') }}" rel="stylesheet">
@stop

@section('js')
<script src="{{ asset('assets/vendors/jquery-steps/jquery.steps.min.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/js/views/rental/form.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/js/views/rental/create.js') }}" type="application/javascript"></script>
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
        <div class="col-md-12 grid-margin">
            <div class="row flex-grow">
                <div class="col-md-12">
                @if ($errors->any())
                    <div class="alert alert-animate alert-warning">
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
                            <form action="{{ route($budget ? 'ajax.budget.new-rental' : 'ajax.rental.new-rental') }}" method="POST" enctype="multipart/form-data" id="formRental" class="pb-2">
                                <h3>Tipo de {{ $budget ? 'Orçamento' : 'Locação' }}</h3>
                                <div class="stepRental">
                                    <h6>Tipo de {!! $budget ? 'Orçamento' : 'Locação <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="Defina se haverá ou não cobrança para essa locação."></i>' !!}</h6>
                                    <div class="row">
                                        <div class="d-flex justify-content-around col-md-12">
                                            <div class="">
                                                <input type="radio" name="type_rental" id="have-payment" value="0">
                                                <label for="have-payment">Com Cobrança</label>
                                            </div>
                                            <div class="">
                                                <input type="radio" name="type_rental" id="no-have-payment" value="1">
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
                                            <div class="alert alert-warning alert-mark-map text-center display-none">O endereço selecionado não foi confirmado no mapa no cadastro do cliente, isso pode acarretar uma má precisão da localização ou houve alguma alteração no endereço. <br>Após a confirmação a geolocalização será atualizada no endereço do cliente.</div>
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
                                                <input type="tel" name="date_delivery" class="form-control col-md-9" value="{{ date(DATETIME_BRAZIL_NO_SECONDS) }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
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
                                                <input type="tel" name="date_withdrawal" class="form-control col-md-9" value="{{ date(DATETIME_BRAZIL_NO_SECONDS, strtotime('+1 minute', time())) }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
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
                                <h3>Valores e Pagamento</h3>
                                <div class="stepRental">
                                    <h6>Valores e Pagamento</h6>
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
                                                    <div class="form-group col-md-12 no-padding text-right mt-2 d-flex justify-content-end">
                                                        <div class="form-group no-padding text-right mt-2">
                                                            <div class="switch">
                                                                <input type="checkbox" class="check-style check-xs" name="calculate_net_amount_automatic" id="calculate_net_amount_automatic" checked>
                                                                <label for="calculate_net_amount_automatic" class="check-style check-xs"></label> Calcular Valor Líquido
                                                            </div>
                                                        </div>
                                                        <div class="form-group automatic_parcel_distribution_parent mt-2">
                                                            <div class="switch">
                                                                <input type="checkbox" class="check-style check-xs" name="automatic_parcel_distribution" id="automatic_parcel_distribution" checked>
                                                                <label for="automatic_parcel_distribution" class="check-style check-xs"></label> Distribuir Valores
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed payment-hidden">
                                            <div class="col-md-12 d-flex justify-content-end payment-hidden">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-success" id="add_parcel"><i class="fa fa-plus"></i> Nova Parcela</button>
                                                </div>
                                            </div>
                                            <div class="col-md-12 payment-hidden">
                                                <div class="form-group mt-1">
                                                    <div class="d-flex align-items-center justify-content-between payment-item">
                                                        <div class="input-group col-md-12 no-padding">
                                                            <span class="col-md-3 text-center">Dia do vencimento</span>
                                                            <span class="col-md-4 text-center">Data do vencimento</span>
                                                            <span class="col-md-1 no-padding">&nbsp;</span>
                                                            <span class="col-md-3 no-border-radius text-center">Valor</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 payment-hidden" id="parcels"></div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Finalização</h3>
                                <div class="stepRental">
                                    <h6>Finalizar</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-3">
                                            <h5>Observação {{ $budget ? 'do Orçamento' : 'da Locação' }}</h5>
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
    <div class="modal fade" tabindex="-1" role="dialog" id="createRental">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $budget ? 'Orçamento criado' : 'Locação criada' }} com sucesso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="text-center code_rental">{{ $budget ? 'Orçamento' : 'Locação' }} código <strong></strong> criada com sucesso!</h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 text-center">
                            <a href="" target="_blank" class="col-md-4 btn btn-primary rental_print"> <i class="fa fa-print"></i> Imprimir Recibo</a>
                        </div>
                    </div>
                    @if (!$budget)
                    <div class="row content-payment-today display-none">
                        <div class="form-group col-md-12 mt-5 text-center">
                            <h4>Foi identificado pagamento para hoje, deseja realizar o pagamento?</h4>
                        </div>
                        <div class="form-group col-md-12 text-center">
                            <div class="switch pt-1 d-flex justify-content-center">
                                <input type="checkbox" class="check-style check-xs" name="do_payment_today" id="do_payment_today" value="1">
                                <label for="do_payment_today" class="check-style check-xs"></label>&nbsp;Sim, fazer o pagamento hoje
                            </div>
                        </div>
                    </div>
                    <div class="row display-payment-today display-none">
                        <div class="form-group col-md-3">
                            <label>Data de Vencimento</label>
                            <input type="text" class="form-control" name="due_date" value="" disabled>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Valor</label>
                            <input type="text" class="form-control" name="due_value" value="" disabled>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Forma de Pagamento</label>
                            <select class="form-control" name="form_payment"></select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Data de Pagamento</label>
                            <input type="date" class="form-control" name="date_payment" value="{{ dateNowInternational(null, DATE_INTERNATIONAL) }}">
                        </div>
                        <input type="hidden" class="form-control" name="payment_id">
                    </div>
                    <div class="row mt-2 display-payment-today display-none">
                        <div class="form-group col-md-12 d-flex justify-content-center">
                            <button class="btn btn-success col-md-3" id="confirm_payment_today">Realizar pagamento</button>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-around flex-wrap mt-5">
                            <a href="{{ route($budget ? 'budget.index' : 'rental.index') }}" class="btn btn-primary col-md-4">Listagem de {{ $budget ? 'Orçamentos' : 'Locações' }}</a>
                            <a href="{{ route($budget ? 'budget.create' : 'rental.create') }}" class="btn btn-secondary col-md-4">Realizar {{ $budget ? 'novo Orçamento' : 'nova Locação' }}</a>
                        </div>
                    </div>
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
    <input type="hidden" id="budget" value="{{ $budget }}">
    <input type="hidden" id="back_to_list" value="{{ route($budget ? 'budget.index' : 'rental.index') }}">

@stop
