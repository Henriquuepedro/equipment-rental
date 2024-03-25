<script src="{{ asset('assets/js/views/map/map.js') }}" type="application/javascript"></script>
<script>
    let last_rental_id = 0;

    $(() => {
        $('#viewRental [name="state"]').select2();
        $('#viewRental [name="city"]').select2();
        $('#viewRental [name="residues[]"]').select2();
        $('[name="type_rental"]').iCheck({
            checkboxClass: 'icheckbox_square',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
        getLocationRental(true);
    });

    $(document).on('click', '.btnViewRental', function(){
        const rental_id = parseInt($(this).data('rental-id'));
        const modal = $('#viewRental');

        if (last_rental_id === rental_id) {
            modal.modal('show');
            return;
        }

        last_rental_id = rental_id;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{ route('ajax.rental.get_full') }}/' + rental_id,
            dataType: 'json',
            success: response => {
                modal.modal('show');
                setTimeout(() => {
                    //https://www.google.com/maps/place/27%C2%B040'04.0%22S+48%C2%B033'54.5%22W
                    //https://www.google.com/maps/dir//-27.6649878,-48.5241072

                    modal.find('[name="name_address"]').closest('.row').hide();
                    modal.find('#confirmAddressMap').closest('.row').hide();

                    modal.find('[name="lat"]').val(response.address_lat);
                    modal.find('[name="lng"]').val(response.address_lng);

                    modal.find('[name="client"]').val(response.client.name).prop('disabled', true);
                    modal.find('[name="cep"]').val(response.address_zipcode).prop('disabled', true).mask('00.000-000');
                    modal.find('[name="address"]').val(response.address_name).prop('disabled', true);
                    modal.find('[name="number"]').val(response.address_number).prop('disabled', true);
                    modal.find('[name="complement"]').val(response.address_complement).prop('disabled', true);
                    modal.find('[name="reference"]').val(response.address_reference).prop('disabled', true);
                    modal.find('[name="neigh"]').val(response.address_neigh).prop('disabled', true);
                    modal.find('.observation').html(response.observation);
                    modal.find(`[name="type_rental"][value="${response.type_rental}"]`).iCheck('check');
                    modal.find('[name="type_rental"]').iCheck('disable');

                    loadStates(modal.find('.show-address select[name="state"]'), response.address_state);
                    loadCities(modal.find('.show-address select[name="city"]'), response.address_state, response.address_city);

                    locationLatLngRental(response.address_lat, response.address_lng);

                    modal.find('[name="residues[]"]').select2('destroy').empty();

                    modal.find('.equipments, .payments').empty();
                    modal.find('.loading-equipments').show();

                    if (response.rental_equipment.length) {
                        $(response.rental_equipment).each(function (k, v) {
                            loadEquipment(modal, v, k);
                        });
                    } else {
                        modal.find('.loading-equipments').hide();
                    }

                    if (response.rental_payment.length) {
                        $(response.rental_payment).each(function (k, v) {
                            loadPayment(modal, v, k, response.rental_payment.length);
                        });
                    }

                    if (response.rental_residue.length) {
                        $(response.rental_residue).each(function (k, v) {
                            modal.find('[name="residues[]"]').append(`<option value="${v.id}" selected>${v.name_residue}</option>`)
                        });
                        setTimeout(() => {
                            modal.find('[name="residues[]"]').select2();
                        }, 500);
                    } else {
                        modal.find('[name="residues[]"]').select2();
                    }

                    modal.find('.content-equipments').css('display', response.rental_equipment.length === 0 ? 'none' : 'block')
                    modal.find('.content-payments').css('display', response.rental_payment.length === 0 ? 'none' : 'block')
                    modal.find('.content-observation').css('display', response.observation.replace(/<.*?>/g, '') === '' ? 'none' : 'block')
                    modal.find('.content-residues').css('display', response.rental_residue.length === 0 ? 'none' : 'block')
                }, 250);
            }, error: () => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: 'Não foi possível localizar a locação'
                });
            }
        });
    });

    const loadResidues = (modal, residue, key, quantity) => {
        const status = payment.payment_id ? '<i class="fa-regular fa-square-check text-success"></i>' : '<i class="fa-solid fa-hourglass-half text-warning"></i>';

        modal.find('.payments').append(`<div class="form-group mt-1 parcel">
            <div class="d-flex align-items-center justify-content-between payment-item">
                <div class="input-group col-md-12 no-padding">
                    <span class="col-md-1">${status}</span>
                    <span class="col-md-3 text-center">${transformDateForBr(payment.due_date)}</span>
                    <span class="col-md-2 text-center">${numberToReal(payment.due_value)}</span>
                    <span class="col-md-3 text-center">${formatDate(payment.payday ? payment.payday : null, FORMAT_DATE_BRAZIL, 'Não pago')}</span>
                    <span class="col-md-3 text-center">${payment.payment_name ?? 'Não pago'}</span>
                </div>
            </div>
        </div>`);
    }

    const loadPayment = (modal, payment, key, quantity) => {
        const status = payment.payment_id ? '<i class="fa-regular fa-square-check text-success"></i>' : '<i class="fa-solid fa-hourglass-half text-warning"></i>';

        modal.find('.payments').append(`<div class="form-group mt-1 parcel">
            <div class="d-flex align-items-center justify-content-between payment-item">
                <div class="input-group col-md-12 no-padding">
                    <span class="col-md-1 text-center">${status}</span>
                    <span class="col-md-3 text-center">${transformDateForBr(payment.due_date)}</span>
                    <span class="col-md-2 text-center">${numberToReal(payment.due_value)}</span>
                    <span class="col-md-3 text-center">${formatDate(payment.payday ? payment.payday : null, FORMAT_DATE_BRAZIL, 'Não pago')}</span>
                    <span class="col-md-3 text-center">${payment.payment_name ?? 'Não pago'}</span>
                </div>
            </div>
        </div>`);
    }

    const loadEquipment = (modal, equipment, key) => {
        const equipment_id_fake = Math.random().toString(16).slice(2);
        let vehicle_id_delivery, driver_id_delivery, vehicle_id_withdrawal, driver_id_withdrawal, vehicle_id_expected, driver_id_expected;
        let txt_vehicle_delivery, txt_driver_delivery, txt_vehicle_withdrawal, txt_driver_withdrawal, txt_vehicle_expected, txt_driver_expected;
        let data_vehicle_delivery, data_driver_delivery, data_vehicle_withdrawal, data_driver_withdrawal, data_vehicle_expected, data_driver_expected = null;
        let data_vehicles = [];
        let data_drivers = [];

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: `{{ route('ajax.equipment.get-equipment') }}/${equipment.equipment_id}/0`,
            success: async response => {
                vehicle_id_expected     = equipment.vehicle_suggestion ?? null;
                driver_id_expected      = equipment.driver_suggestion ?? null;
                vehicle_id_delivery     = equipment.actual_vehicle_delivery ?? null;
                driver_id_delivery      = equipment.actual_driver_delivery ?? null;
                vehicle_id_withdrawal   = equipment.actual_vehicle_withdrawal ?? null;
                driver_id_withdrawal    = equipment.actual_driver_withdrawal ?? null;

                if (vehicle_id_expected) {
                    if (typeof data_vehicles[vehicle_id_expected] === "undefined") {
                        data_vehicle_expected = await getDataRegister('vehicle', vehicle_id_expected);
                        data_vehicles[vehicle_id_expected] = data_vehicle_expected;
                    } else {
                        data_vehicle_expected = data_vehicles[vehicle_id_expected];
                    }
                }
                if (driver_id_expected) {
                    if (typeof data_drivers[driver_id_expected] === "undefined") {
                        data_driver_expected = await getDataRegister('driver', driver_id_expected);
                        data_drivers[driver_id_expected] = data_driver_expected;
                    } else {
                        data_driver_expected = data_drivers[driver_id_expected];
                    }
                }
                if (vehicle_id_delivery) {
                    if (typeof data_vehicles[vehicle_id_delivery] === "undefined") {
                        data_vehicle_delivery = await getDataRegister('vehicle', vehicle_id_delivery);
                        data_vehicles[vehicle_id_delivery] = data_vehicle_delivery;
                    } else {
                        data_vehicle_delivery = data_vehicles[vehicle_id_delivery];
                    }
                }
                if (vehicle_id_withdrawal) {
                    if (typeof data_vehicles[vehicle_id_withdrawal] === "undefined") {
                        data_vehicle_withdrawal = await getDataRegister('vehicle', vehicle_id_withdrawal);
                        data_vehicles[vehicle_id_withdrawal] = data_vehicle_withdrawal;
                    } else {
                        data_vehicle_withdrawal = data_vehicles[vehicle_id_withdrawal];
                    }
                }
                if (driver_id_delivery) {
                    if (typeof data_drivers[driver_id_delivery] === "undefined") {
                        data_driver_delivery = await getDataRegister('driver', driver_id_delivery);
                        data_drivers[driver_id_delivery] = data_driver_delivery;
                    } else {
                        data_driver_delivery = data_drivers[driver_id_delivery];
                    }
                }
                if (driver_id_withdrawal) {
                    if (typeof data_drivers[driver_id_withdrawal] === "undefined") {
                        data_driver_withdrawal = await getDataRegister('driver', driver_id_withdrawal);
                        data_drivers[driver_id_withdrawal] = data_driver_withdrawal;
                    } else {
                        data_driver_withdrawal = data_drivers[driver_id_withdrawal];
                    }
                }

                txt_vehicle_delivery    = typeof data_vehicles[vehicle_id_delivery] !== "undefined" ? data_vehicles[vehicle_id_delivery]['name'] : 'Não Entregue';
                txt_vehicle_withdrawal  = typeof data_vehicles[vehicle_id_withdrawal] !== "undefined" ? data_vehicles[vehicle_id_withdrawal]['name'] : 'Não Retirado';
                txt_driver_delivery     = typeof data_drivers[driver_id_delivery] !== "undefined" ? data_drivers[driver_id_delivery]['name'] : 'Não Entregue';
                txt_driver_withdrawal   = typeof data_drivers[driver_id_withdrawal] !== "undefined" ? data_drivers[driver_id_withdrawal]['name'] : 'Não Retirado';
                txt_vehicle_expected    = typeof data_vehicles[vehicle_id_expected] !== "undefined" ? data_vehicles[vehicle_id_expected]['name'] : 'Não Informado';
                txt_driver_expected     = typeof data_vehicles[driver_id_expected] !== "undefined" ? data_vehicles[driver_id_expected]['name'] : 'Não Informado';

                let content = `
                    <div class="card mb-0">
                        <div class="card-header" role="tab" id="headingEquipment-${equipment_id_fake}">
                            <h5 class="mb-0">
                                <a class="collapsed" data-bs-toggle="collapse" href="#collapseEquipment-${equipment_id_fake}" aria-expanded="false" aria-controls="collapseEquipment-${equipment_id_fake}">
                                    ${response.data.name}
                                </a>
                                <span class=""></span>
                            </h5>
                        </div>
                        <div id="collapseEquipment-${equipment_id_fake}" class="collapse" role="tabpanel" aria-labelledby="headingEquipment-${equipment_id_fake}" data-bs-parent=".equipments">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Referência</label>
                                            <input type="text" class="form-control" value="${response.data.reference}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group label-animate">
                                            <label>Quantidade</label>
                                            <input type="txt" class="form-control" value="${equipment.quantity}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="form-group col-md-3 label-animate">
                                        <label>Veículo Sugerido na Locação</label>
                                        <input type="text" class="form-control" value="${txt_vehicle_expected}" disabled>
                                    </div>
                                    <div class="form-group col-md-3 label-animate">
                                        <label>Motorista Sugerido na Locação</label>
                                        <input type="text" class="form-control" value="${txt_driver_expected}" disabled>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="form-group col-md-3 label-animate">
                                        <label>Veículo de Entrega</label>
                                        <input type="text" class="form-control" value="${txt_vehicle_delivery}" disabled>
                                    </div>
                                    <div class="form-group col-md-3 label-animate">
                                        <label>Motorista de Entrega</label>
                                        <input type="text" class="form-control" value="${txt_driver_delivery}" disabled>
                                    </div>
                                    <div class="form-group col-md-3 label-animate">
                                        <label>Veículo de Retirada</label>
                                        <input type="text" class="form-control" value="${txt_vehicle_withdrawal}" disabled>
                                    </div>
                                    <div class="form-group col-md-3 label-animate">
                                        <label>Motorista de Retirada</label>
                                        <input type="text" class="form-control" value="${txt_driver_withdrawal}" disabled>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Data Prevista de Entrega</label>
                                            <input type="text" class="form-control" value="${transformDateForBr(equipment.expected_delivery_date.slice(0, -3))}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Data Prevista de Retirada</label>
                                            <input type="text" class="form-control" value="${transformDateForBr((equipment.expected_withdrawal_date ? equipment.expected_withdrawal_date.slice(0, -3) : null), 'Não Informada')}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Data Entrega</label>
                                            <input type="text" class="form-control" value="${transformDateForBr((equipment.actual_delivery_date ? equipment.actual_delivery_date.slice(0, -3) : null), 'Não Entregue')}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Data Retirada</label>
                                            <input type="text" class="form-control" value="${transformDateForBr((equipment.actual_withdrawal_date ? equipment.actual_withdrawal_date.slice(0, -3) : null), 'Não Retirado')}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 mt-2">
                                        <button type="button" class="btn btn-primary btn-sm pull-right hideEquipment"><i class="fa fa-angle-up"></i> Ocultar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;

                modal.find('.equipments').append(content);
                checkLabelAnimate();

                if (key === 0) {
                    modal.find('.loading-equipments').hide();
                }
            }
        });
    }

    $(document).on('click', '.hideEquipment', function (){
        $(`#${$(this).closest('.collapse.show').attr('id')}`).collapse('hide');
    });
</script>
