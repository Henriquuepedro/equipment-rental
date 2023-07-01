@extends('adminlte::page')

@section('title', $budget ? 'Atualizar de Orçamento' : 'Atualizar de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Atualização de {{ $budget ? 'Orçamento' : 'Locação' }}</h1>
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
    @include('includes.client.modal-script')
    @include('includes.address.modal-script')
    @include('includes.equipment.modal-script')
    @include('includes.driver.modal-script')
    @include('includes.vehicle.modal-script')
    @include('includes.residue.modal-script')

    @if(in_array('ClientCreatePost', $permissions))    <script src="{{ asset('assets/js/views/client/form.js') }}" type="application/javascript"></script>    @endif
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
            let address_state = null;
            let address_city = null;
            let exist_payment = false;
            const rental_id = $('[name="rental_id"]').val();

            setTimeout(async () => {
                $('.show-address').each(function () {
                    $(this).find('input').each(function () {
                        $(this).val($(this).attr('value'));
                    });
                });

                checkLabelAnimate();

                if ($('[name="residues"]').val().split(',').length) {
                    $('.container-residues').show();
                    $('[name="residues[]"]').val($('[name="residues"]').val().split(',')).select2('destroy').select2();
                }

                getEquipmentsRental(rental_id, async function(response){
                    $(response).each(async function (k, equipment) {
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
                            not_use_date_withdrawal
                        );
                    });
                });

                await $('#add_parcel, .automatic_parcel_distribution_parent').slideDown(500);

                await getPaymentsRental(rental_id, function(response){
                    $(response).each(async function (k, payment) {
                        parcel       = parseInt(payment.parcel) - 1;
                        due_day      = payment.due_day;
                        due_date     = payment.due_date;
                        due_value    = payment.due_value;
                        payment_id   = payment.payment_id;
                        payment_name = payment.payment_name;
                        payday       = payment.payday;

                        if (exist_payment === false && payment_id) {
                            exist_payment = true;
                            $('#paid_installment_notice').show();
                        }

                        $('#parcels').append(
                            createParcel(parcel, due_day, due_date, due_value)
                        ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({
                            thousands: '.',
                            decimal: ',',
                            allowZero: true
                        }).closest('.payment-item').find('[name="due_date[]"]').trigger('blur').closest('.payment-item').find('.remove-payment').tooltip();
                    });
                });
            }, 500);

                setTimeout(() => {getEquipmentsRental(rental_id, async function(response){
                    $(response).each(async function (k, equipment) {
                        equipment_id             = equipment.equipment_id;
                        quantity                 = equipment.quantity;
                        total_value              = equipment.total_value;
                        unitary_value            = equipment.unitary_value;

                        createEquipmentPayment(equipment_id, null, unitary_value, total_value, quantity);
                    });
                });

                $('.list-equipments-payment-load').hide();
                $('.list-equipments-payment').slideDown('slow');

                $('li.disabled[aria-disabled="true"]').removeClass('disabled').addClass('done').prop('aria-disabled', false);
                $('#net_value').val($('#net_value').attr('value'));

                if ($('#calculate_net_amount_automatic').is(':checked')) {
                    $('#net_value').attr('disabled', true);
                    setTimeout(() => {
                        reloadTotalRental();
                    }, 100)
                } else {
                    $('#net_value').attr('disabled', false);
                    $('#discount_value').attr('disabled', true);
                    $('#extra_value').attr('disabled', true);
                }
            }, 750)

            if ($('[name="address_state"]').length) {
                address_state = $('[name="address_state"]').val();
            }

            await loadStates($('[name="state"]'), address_state);

            if (address_state && $('[name="address_city"]').length) {
                address_city = $('[name="address_city"]').val();
                await loadCities($('[name="city"]'), address_state, address_city);
            }

            $('input[name="type_rental"]').on('ifChanged', function(){
                console.log($('input[name="type_rental"]').val());

                if (parseInt($('input[name="type_rental"]:checked').val()) === 1 && $('#parcels div').length) {
                    Swal.fire({
                        title: 'Alteração de Tipo de Locação',
                        html: `Atualmente, existem pagamentos para sua locação, caso altere, os pagamentos serão excluídos. <br><br><h4>Deseja continuar?</h4>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#19d895',
                        cancelButtonColor: '#bbb',
                        confirmButtonText: 'Sim',
                        cancelButtonText: 'Não',
                        reverseButtons: true
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            $('input[name="type_rental"][value="0"]').iCheck('check')
                        }
                    })
                }
            });
        });
    </script>
    <script src="{{ asset('assets/js/views/rental/form.js') }}" type="application/javascript"></script>
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
                            <form action="{{ route($budget ? 'ajax.budget.update-rental' : 'ajax.rental.update-rental', ['id' => $rental->id]) }}" method="POST" enctype="multipart/form-data" id="formRental" class="pb-2">
                                <h3>Tipo de {{ $budget ? 'Orçamento' : 'Locação' }}</h3>
                                <div class="stepRental">
                                    <h6 class="title-step">Tipo de {!! $budget ? 'Orçamento' : 'Locação <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="Defina se haverá ou não cobrança para essa locação."></i>' !!}</h6>
                                    <div class="row">
                                        <div class="d-flex justify-content-around col-md-12">
                                            <div class="">
                                                <input type="radio" name="type_rental" id="have-payment" value="0" {{ $rental->type_rental == 0 ? 'checked' : '' }}>
                                                <label for="have-payment">Com Cobrança</label>
                                            </div>
                                            <div class="">
                                                <input type="radio" name="type_rental" id="no-have-payment" value="1" {{ $rental->type_rental == 1 ? 'checked' : '' }}>
                                                <label for="no-have-payment">Sem Cobrança</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Cliente e Endereço</h3>
                                <div class="stepRental">
                                    <h6 class="title-step">Cliente e Endereço</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 label-animate">
                                            @include('includes.client.form')
                                        </div>
                                    </div>
                                    @include('includes.address.form', [
                                        'address_zipcode'   => formatZipcode($rental->address_zipcode),
                                        'address_name'      => $rental->address_name,
                                        'address_number'    => $rental->address_number,
                                        'address_complement'=> $rental->address_complement,
                                        'address_reference' => $rental->address_reference,
                                        'address_neigh'     => $rental->address_neigh,
                                        'address_city'      => $rental->address_city,
                                        'address_state'     => $rental->address_state,
                                        'address_lat'       => $rental->address_lat,
                                        'address_lng'       => $rental->address_lng,
                                    ])
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-2">
                                            <div class="alert alert-warning alert-mark-map text-center display-none">O endereço selecionado não foi confirmado no mapa no cadastro do cliente, isso pode acarretar uma má precisão da localização. <br>Após a confirmação a geolocalização será atualizada no endereço do cliente.</div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Datas</h3>
                                <div class="stepRental">
                                    <h6 class="title-step">Datas</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group flatpickr d-flex">
                                                <label class="label-date-btns">Data Prevista de Entrega <sup>*</sup></label>
                                                <input type="tel" name="date_delivery" class="form-control col-md-9" value="{{ date('d/m/Y H:i', strtotime($rental->expected_delivery_date)) }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
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
                                                <input type="tel" name="date_withdrawal" class="form-control col-md-9" value="{{ !$rental->not_use_date_withdrawal ? date('d/m/Y H:i', strtotime($rental->expected_withdrawal_date)) : '' }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
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
                                                    <input type="checkbox" class="check-style check-xs" name="not_use_date_withdrawal" id="not_use_date_withdrawal" value="1" {{ $rental->not_use_date_withdrawal ? 'checked' : '' }}>
                                                    <label for="not_use_date_withdrawal" class="check-style check-xs"></label> Não informar data de retirada
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Equipamentos</h3>
                                <div class="stepRental">
                                    <h6 class="title-step">Equipamentos</h6>
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
                                                        <span type="text" class="form-control d-flex align-items-center" id="gross_value">{{ formatMoney($rental->gross_value) }}</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Acréscimo</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" value="{{ formatMoney($rental->extra_value) }}" id="extra_value" name="extra_value">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Desconto</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" value="{{ formatMoney($rental->discount_value) }}" id="discount_value" name="discount_value">
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
                                                        <input type="text" class="form-control d-flex align-items-center" id="net_value" name="net_value" value="{{ formatMoney($rental->net_value) }}" disabled>
                                                    </div>
                                                    <div class="form-group col-md-12 no-padding text-right mt-2 d-flex justify-content-end">
                                                        <div class="form-group no-padding text-right mt-2">
                                                            <div class="switch">
                                                                <input type="checkbox" class="check-style check-xs" name="calculate_net_amount_automatic" id="calculate_net_amount_automatic" {{ $rental->calculate_net_amount_automatic ? 'checked' : '' }}>
                                                                <label for="calculate_net_amount_automatic" class="check-style check-xs"></label> Calcular Valor Líquido
                                                            </div>
                                                        </div>
                                                        <div class="form-group automatic_parcel_distribution_parent mt-2">
                                                            <div class="switch">
                                                                <input type="checkbox" class="check-style check-xs" name="automatic_parcel_distribution" id="automatic_parcel_distribution"  {{ $rental->automatic_parcel_distribution ? 'checked' : '' }}>
                                                                <label for="automatic_parcel_distribution" class="check-style check-xs"></label> Distribuir Valores
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed payment-hidden">
                                            <div class="col-md-12 d-flex justify-content-end payment-hidden">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-success display-none" id="add_parcel"><i class="fa fa-plus"></i> Nova Parcela</button>
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
                                <h3>Finalização</h3>
                                <div class="stepRental">
                                    <h6 class="title-step">Finalizar</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-3">
                                            <h5>Observação {{ $budget ? 'do Orçamento' : 'da Locação' }}</h5>
                                            <div id="observationDiv" class="quill-container">{!! $rental->observation !!}</div>
                                            <textarea type="hidden" class="d-none" name="observation" id="observation"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="confirm_update_equipment_or_payment" value="0">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>
                </div>
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
                            <h3 class="text-center code_rental">{{ $budget ? 'Orçamento' : 'Locação' }} código <strong></strong> atualizada com sucesso!</h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-3 text-center">
                            <a href="" target="_blank" class="col-md-4 btn btn-primary rental_print"> <i class="fa fa-print"></i> Imprimir Recibo</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-around flex-wrap mt-5">
                            <a href="{{ route($budget ? 'budget.index' : 'rental.index') }}" class="btn btn-primary col-md-4">Listagem de {{ $budget ? 'Orçamentos' : 'Locações' }}</a>
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


    <input type="hidden" name="rental_id" value="{{ $rental->id }}">
    <input type="hidden" name="client_id" value="{{ $rental->client_id }}">
    <input type="hidden" name="address" value="{{ $rental->client_id }}">

    <input type="hidden" name="address_city" value="{{ $rental->address_city }}">
    <input type="hidden" name="address_state" value="{{ $rental->address_state }}">

    <input type="hidden" name="residues" value="{{ implode(',', array_map(function($residue) { return $residue['id']; }, $rental_residue->toArray())) }}">
@stop
