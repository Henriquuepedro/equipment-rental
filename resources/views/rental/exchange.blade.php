@extends('adminlte::page')

@section('title', 'Trocar Equipamento')

@section('content_header')
    <h1 class="m-0 text-dark">Trocar Equipamento</h1>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @if ($settings['style_template'] == 3)
        <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
    @endif
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/views/rental.css') }}" rel="stylesheet">
@stop

@section('js')
<script src="{{ asset('assets/vendors/jquery-steps/jquery.steps.min.js') }}" type="application/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
<script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>
    @include('includes.equipment.modal-script')
    @include('includes.driver.modal-script')
    @include('includes.vehicle.modal-script')

    @if(in_array('EquipmentCreatePost', $permissions)) <script src="{{ asset('assets/js/views/equipment/form.js') }}" type="application/javascript"></script> @endif

    <script>
        $(async () => {
            let equipment_id;
            let quantity;
            let vehicle_suggestion;
            let driver_suggestion;
            let use_date_diff_equip;
            let expected_delivery_date;
            let expected_withdrawal_date;
            let not_use_date_withdrawal;
            let total_value;
            let unitary_value;
            let parcel;
            let due_day;
            let due_date;
            let due_value;
            let payment_id;
            let payment_name;
            let payday;
            const rental_id = $('[name="rental_id"]').val();

            setTimeout(async () => {
                await getEquipmentsRental(rental_id, false, async function(response){
                    $(response).each(async function (k, equipment) {
                        if (equipment.actual_delivery_date != null && equipment.actual_withdrawal_date == null && !equipment.exchanged) {
                            equipment_id             = equipment.equipment_id;
                            quantity                 = equipment.quantity;
                            vehicle_suggestion       = equipment.vehicle_suggestion;
                            driver_suggestion        = equipment.driver_suggestion;
                            use_date_diff_equip      = equipment.use_date_diff_equip;
                            expected_delivery_date   = equipment.expected_delivery_date;
                            expected_withdrawal_date = equipment.expected_withdrawal_date;
                            not_use_date_withdrawal  = equipment.not_use_date_withdrawal;
                            total_value              = equipment.total_value;
                            unitary_value            = equipment.unitary_value;

                            await setEquipmentRental(
                                equipment_id,
                                quantity,
                                vehicle_suggestion,
                                driver_suggestion,
                                use_date_diff_equip,
                                expected_delivery_date,
                                expected_withdrawal_date,
                                not_use_date_withdrawal,
                                true,
                                equipment.id
                            );
                        }
                    });
                });

                await getPaymentsRental(rental_id, false, function(response){
                    $(response).each(async function (k, payment) {
                        parcel       = parseInt(payment.parcel) - 1;
                        due_day      = payment.due_day;
                        due_date     = payment.due_date;
                        due_value    = payment.due_value;
                        payment_id   = payment.payment_id;
                        payment_name = payment.payment_name;
                        payday       = payment.payday;

                        if (payment_id) {
                            $('[name="total_rental_paid"]').val(parseFloat($('[name="total_rental_paid"]').val()) + parseFloat(payment.due_value));
                            $('#parcels_paid').append(
                                createParcel(parcel, due_day, due_date, due_value, false)
                            ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({
                                thousands: '.',
                                decimal: ',',
                                allowZero: true
                            }).closest('.payment-item').find('button, input').prop('disabled', true);
                        } else {
                            $('[name="total_rental_no_paid"]').val(parseFloat($('[name="total_rental_no_paid"]').val()) + parseFloat(payment.due_value));
                            $('#parcels').append(
                                createParcel(parcel, due_day, due_date, due_value)
                            ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({
                                thousands: '.',
                                decimal: ',',
                                allowZero: true
                            }).closest('.payment-item').find('[name="due_date[]"]').trigger('blur').closest('.payment-item').find('.remove-payment').tooltip();
                        }
                    });

                    setTimeout(() => {
                        if (!$('#parcels .parcel').length) {
                            $('#add_parcel').trigger('click');
                        }
                        if (!$('#parcels_paid .parcel').length) {
                            $('#separator_parcels_paid').hide();
                            $('#title_parcels_paid').hide();
                            $('#head_parcels_paid').hide();
                            $('#parcels_paid').hide();
                        }
                    }, 500);
                });
            }, 500);
        });
    </script>
    <script src="{{ asset('assets/js/views/rental/exchange.js') }}" type="application/javascript"></script>
    <script src="{{ asset('assets/js/views/rental/form.js') }}" type="application/javascript"></script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
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
                    <div class="alert alert-animate alert-secondary">
                        Ao realizar a troca do equipamento, não será mais possível realizar a alteração da locação.
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('ajax.rental.exchange-rental', ['id' => $rental->id]) }}" method="POST" enctype="multipart/form-data" id="formRental" class="pb-2">
                                <h3>Equipamentos</h3>
                                <div class="stepRental">
                                    <h6 class="title-step">Equipamentos</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-2 equipments-selected-to-exchange">
                                            <div class="accordion accordion-multiple-filled" id="equipments-selected-to-exchange" role="tablist">
                                            </div>
                                            <hr class="separator-dashed mt-4 display-none">
                                        </div>
                                    </div>
                                    <h6 class="title-step">Equipamentos Para Trocar</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-2 equipments-selected">
                                            <div class="accordion accordion-multiple-filled" id="equipments-selected" role="tablist">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Valores e Pagamento</h3>
                                <div class="stepRental">
                                    <h6 class="title-step">Valores e Pagamento</h6>
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
                                                        <span type="text" class="form-control d-flex align-items-center" id="gross_value">{{ formatMoney($rental->gross_value) }}"</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Acréscimo</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" id="extra_value" name="extra_value" value="{{ formatMoney($rental->extra_value) }}">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Desconto</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" id="discount_value" name="discount_value" value="{{ formatMoney($rental->discount_value) }}">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2 flex-wrap">
                                                    <label class="mb-0 mr-md-2">Valor Líquido</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control d-flex align-items-center" id="net_value" name="net_value" value="{{ formatMoney($rental->net_value) }}" disabled>
                                                    </div>
                                                    <div class="form-group col-md-12 no-padding text-right mt-2 d-flex justify-content-end">
                                                        <div class="form-group no-padding text-right mt-2 d-none">
                                                            <div class="switch">
                                                                <input type="checkbox" class="check-style check-xs" name="calculate_net_amount_automatic" id="calculate_net_amount_automatic" {{ $rental->calculate_net_amount_automatic ? 'checked' : '' }}>
                                                                <label for="calculate_net_amount_automatic" class="check-style check-xs"></label> Calcular Valor Líquido
                                                            </div>
                                                        </div>
                                                        <div class="form-group automatic_parcel_distribution_parent mt-2">
                                                            <div class="switch">
                                                                <input type="checkbox" class="check-style check-xs" name="automatic_parcel_distribution" id="automatic_parcel_distribution">
                                                                <label for="automatic_parcel_distribution" class="check-style check-xs"></label> Distribuir Valores
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed payment-hidden" id="separator_parcels_paid">
                                            <div class="col-md-12 payment-hidden mb-3 text-center underline text-primary" id="title_parcels_paid">
                                                <h4>Pagamentos já efetuados</h4>
                                            </div>
                                            <div class="col-md-12 payment-hidden" id="head_parcels_paid">
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
                                            <div class="col-md-12 payment-hidden" id="parcels_paid"></div>
                                            <hr class="separator-dashed payment-hidden">
                                            <div class="col-md-12 payment-hidden">
                                                <div class="form-group">
                                                    <div class="alert alert-animate alert-primary">
                                                        Será necessário reorganizar o pagamento para incluir o valor do novos equipamentos.<br>
                                                        Você poderá gerar uma nova parcela para os novos equipamentos ou distribuir o valor entre as parcelas, caso exista.
                                                    </div>
                                                </div>
                                            </div>
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
                                            <div class="col-md-12 display-none mt-4" id="paid_installment_notice">
                                                <div class="alert alert-warning">
                                                    <h4>Você tem parcelas pagas!</h4>
                                                    <p>Existem parcelas já pagas para essa locação, caso você altere algum dado das parcelas, deverá ser feito o pagamento novamente.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="rental_id" value="{{ $rental->id }}">
                                <input type="hidden" name="date_delivery" value="{{ $rental->expected_delivery_date ? dateInternationalToDateBrazil($rental->expected_delivery_date, DATETIME_BRAZIL_NO_SECONDS) : '' }}">
                                <input type="hidden" name="date_withdrawal" value="{{ $rental->expected_withdrawal_date ? dateInternationalToDateBrazil($rental->expected_withdrawal_date, DATETIME_BRAZIL_NO_SECONDS) : '' }}">
                                <input type="hidden" name="is_exchange" id="is_exchange" value="1">
                                <input type="hidden" name="total_rental_paid" value="0">
                                <input type="hidden" name="total_rental_no_paid" value="0">

                                <div class="d-none">
                                    <input type="radio" name="type_rental" value="{{ $rental->type_rental }}" checked>
                                </div>
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(in_array('EquipmentCreatePost', $permissions)) @include('includes.equipment.modal-create') @endif
    @if(in_array('VehicleCreatePost', $permissions))   @include('includes.vehicle.modal-create')   @endif
    @if(in_array('DriverCreatePost', $permissions))    @include('includes.driver.modal-create')    @endif
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
                            <h3 class="text-center code_rental">Locação código <strong></strong> atualizada com sucesso!</h3>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="exchangeEquipment">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Troca de equipamento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label>Selecione o equipamento a ser trocado.</label>
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
                    <div class="form-group col-md-12 mt-2 table-responsive content-equipment">
                        <table class="table list-equipment d-table">
                            <tbody>
                            <tr>
                                <td class="text-left"><h6 class="text-center"><i class="fas fa-search"></i> Pesquise por um equipamento</h6></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="equipment-to-exchange">
                    <input type="hidden" name="rental-equipment-to-exchange">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar troca</button>
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
@stop
