var searchEquipmentOld = '';
var budget = !!$('#budget').val();

const getIndexStep = step => {

    switch (step) {
        case 0:
            return 0;
        case 1:
            return 1;
        case 2:
            return 2;
        case 3:
            return 3;
        case 4:
            return 4;
        case 5:
            return 5;
    }
}

$("#formRental").validate({
    rules: {

    },
    messages: {

    },
    invalidHandler: function(event, validator) {
        $('html, body').animate({scrollTop:0}, 100);
        let arrErrors = [];
        $.each(validator.errorMap, function (key, val) {
            arrErrors.push(val);
        });
        //setTimeout(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
            });
        //}, 150);
    },
    submitHandler: function(form) {
        $('#formRental [type="submit"]').attr('disabled', true);
        form.submit();
    }
});

$(function(){
    $('#formRental.wizard .actions a[href="#previous"]').attr('href', '#cancel').html('<i class="fa fa-times"></i> Cancelar').addClass('btn-danger').closest('li').removeClass('disabled');
    $('#searchEquipment').on('blur keyup', function (e){
        if(e.keyCode !== 13 && e.type === 'keyup') {
            return false;
        }

        const searchEquipment = $(this).val();
        let equipmentInUse = [];

        if (searchEquipment === searchEquipmentOld) {
            return false;
        }
        $('#equipments-selected .card-header').each(function(){
            equipmentInUse.push(parseInt($(this).attr('id-equipment')));
        });

        if (parseInt($('#is_exchange').val()) === 1) {
            equipmentInUse = [];
        }

        $('table.list-equipment tbody').empty();

        searchEquipmentOld = searchEquipment;

        $('table.list-equipment tbody').empty();

        if (searchEquipment === '') {
            equipmentMessageDefault('<i class="fas fa-search"></i> Pesquise por um equipamento');
            return false;
        }

        equipmentMessageDefault('<i class="fas fa-spinner fa-spin"></i> Carregando equipamentos ...');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $('#routeGetEquipments').val(),
            data: { searchEquipment, equipmentInUse },
            success: response => {

                $('table.list-equipment tbody').empty();

                if (!response.length) {
                    equipmentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                    return false;
                }

                let badgeStock = '';
                let dataEquipment = '';
                $.each(response, function (key, val) {
                    badgeStock = val.stock <= 0 && !budget ? 'danger' : 'primary';

                    dataEquipment = `
                            <tr class="equipment" id-equipment="${val.id}">
                                <td class="text-left"><p class="text-left">${val.name}</p></td>
                                <td><div class="badge badge-pill badge-lg badge-info">${val.reference}</div></td>\`;
                                <td><div class="badge badge-pill badge-lg badge-${badgeStock}">${val.stock} un</div></td>
                                <td><div class="badge badge-pill badge-lg badge-warning">R$ ${val.value}</div></td>`;
                    dataEquipment += `
                                <td class="text-right">
                                    <button type="button" class="badge badge-lg badge-pill badge-success">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </td>
                            </tr>`;

                    $('table.list-equipment tbody').append(dataEquipment);
                });
            }, error: e => {
                console.log(e);
            },
            complete: function(xhr) {
                if (xhr.status === 403) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Você não tem permissão para fazer essa operação!'
                    });
                }
            }
        });
    });

    $('#cleanSearchEquipment').on('click', function (){
        $('#searchEquipment').val('').trigger('blur');
    });

    $(document).on('click', 'table.list-equipment .equipment', function(){
        const idEquipment = $(this).attr('id-equipment');

        setEquipmentRental(idEquipment);
    });

    $(document).on('click', '.remove-equipment i', function (){
        $(this).closest('.card').slideUp(500);

        if (parseInt($('#is_exchange').val()) === 1) {
            $(`#headingEquipmentToExchange-${$(this).closest('[id-equipment]').data('equipment-to-exchange')} a`).attr('disabled', false);
            $(`#headingEquipmentToExchange-${$(this).closest('[id-equipment]').data('equipment-to-exchange')}`).attr('disabled', false);
        }

        setTimeout(() => {
            $(this).closest('.card').remove();
            searchEquipmentOld = '';
            $('#searchEquipment').trigger('blur');
            showSeparatorEquipmentSelected();
            if (!$('[href^="#collapseEquipment-"][is-cacamba="true"]').length){
                $('.container-residues').slideUp('slow');
            }
        }, 550);
    });

    $(document).on('click', '.hideEquipment', function (){
        $(`#${$(this).closest('.collapse.show').attr('id')}`).collapse('hide');
    });

    $(document).on('click', '.use_date_diff_equip', function (){
        const elEquip = $(this).closest('[id-equipment]');
        let date_delivery, date_withdrawal;
        const rental_id = $('#' + elEquip.attr('aria-labelledby')).data('rental-id');

        elEquip.find('input[name^="date_delivery_equipment_"]').attr('disabled', !$(this).is(':checked'));
        if (!rental_id) {
            elEquip.find('.not_use_date_withdrawal').attr('disabled', !$(this).is(':checked'));
        }

        if (!elEquip.find('.not_use_date_withdrawal').is(':checked')) {
            elEquip.find('input[name^="date_withdrawal_equipment_"]').attr('disabled', !$(this).is(':checked'));
        }

        if (!elEquip.find('.not_use_date_withdrawal').is(':checked')) {
            elEquip.find('.calendar_equipment:eq(1) a').attr('disabled', !$(this).is(':checked'));
        }

        elEquip.find('.calendar_equipment:eq(0) a').attr('disabled', !$(this).is(':checked'));

        if (!$(this).is(':checked')) {
            date_delivery = $('input[name="date_delivery"]').val();
            date_withdrawal = $('input[name="date_withdrawal"]').val();

            if (date_delivery) {
                elEquip.find('input[name^="date_delivery_equipment_"]').val(date_delivery);
            }
            if (date_withdrawal) {
                elEquip.find('input[name^="date_withdrawal_equipment_"]').val(date_withdrawal);
            }

            if (!rental_id && $('#not_use_date_withdrawal').is(':checked')) {
                elEquip.find('.not_use_date_withdrawal').prop('checked', true);
            } else if (!rental_id) {
                elEquip.find('.not_use_date_withdrawal').prop('checked', false);
            }

            checkLabelAnimate();

            elEquip.find('.use_date_diff_equip_show').slideUp('slow');
        } else {
            elEquip.find('.use_date_diff_equip_show').slideDown({
                start: function () {
                    $(this).css({
                        display: "flex"
                    })
                }
            });
        }
    });

    $(document).on('blur change', '[name^="stock_equipment_"]', function (){
        const maxStock      = parseInt($(this).attr('max-stock'));
        const stock         = parseInt($(this).val());
        const idEquipment  = parseInt($(this).closest('.card').find('.card-header').attr('id-equipment'));

        if (stock > maxStock && !budget) {
            Toast.fire({
                icon: 'error',
                title: `A quantidade não pode ser superior a ${maxStock} un.`
            });
            // $(this).val(maxStock);
            setTimeout(() => {
                $(`#collapseEquipment-${idEquipment}`).collapse('show');
                $(this).focus();
            }, 250);
        }
    });

    $('#not_use_date_withdrawal').on('change', function (){
        const elEquip = $(this).closest('.col-md-6');

        elEquip.find('input[name="date_withdrawal"]').attr('disabled', $(this).is(':checked'));
        elEquip.find('.flatpickr a').attr('disabled', $(this).is(':checked'));

        elEquip.find('input[name="date_withdrawal"]').val('');

        if (!$(this).is(':checked')) {
            elEquip.find('input[name="date_withdrawal"]').val(getTodayDateBr());
        }
        checkLabelAnimate();
    });

    $(document).on('change', '.not_use_date_withdrawal', function (){
        const elEquip = $(this).closest('.col-md-6');

        elEquip.find('input[name^="date_withdrawal_equipment_"]').attr('disabled', $(this).is(':checked'));
        elEquip.find('.flatpickr a').attr('disabled', $(this).is(':checked'));

        elEquip.find('input[name^="date_withdrawal_equipment_"]').val('');

        let date_withdrawal = $('input[name="date_withdrawal"]').val();

        if (!$(this).is(':checked')) {
            if (!date_withdrawal || parseInt($('#is_exchange').val() ?? 0) === 1) {
                date_withdrawal = transformDateForBr(sumMinutesDateNow(1, true, false));
            }

            elEquip.find('input[name^="date_withdrawal_equipment_"]').val(date_withdrawal);
        }

        checkLabelAnimate();
    });

    $('#extra_value, #discount_value, #net_value').on('keyup', () => {
        reloadTotalRental();
    })
    .on('blur', function(){
        if ($(this).val() === '') {
            $(this).val('0,00');
        }
    });

    $(document).on('keyup', '#net_value', function() {
        let netAmount   = realToNumber($(this).val());
        let grossAmount = realToNumber($('#gross_value').text());
        let discount    = $('#discount_value');
        let extra       = $('#extra_value');

        discount.val('0,00');
        extra.val('0,00');

        if (netAmount > grossAmount) {
            extra.val(numberToReal(netAmount - grossAmount));
        } else if (netAmount < grossAmount) {
            discount.val(numberToReal(grossAmount - netAmount));
        }
    });

    $(document).on('click', '.btn-view-price-period-equipment', function (){
        const btn = $(this);
        const idEquipment = $(this).attr('id-equipment');

        btn.attr('disable', true);

        let descPeriod = '';

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $('#routeGetPriceStockPeriodEquipment').val(),
            data: { idEquipment },
            success: response => {
                if (response.length) {
                    descPeriod += '<ol class="no-padding">';
                    $.each(response, function (key, val) {
                        descPeriod += `<li><b>${val.day_start} dias</b> até <b>${val.day_end} dias</b> por <b>R$${numberToReal(val.value)}</b></li>`;
                    });
                    descPeriod += '</ol>';
                } else {
                    descPeriod += 'Equipamento não contém valor por período definido.';
                }

                btn.attr('disable', true);

                Swal.fire({
                    icon: 'info',
                    title: 'Valores Por Período',
                    html: descPeriod
                });
            }, error: () => {
                btn.attr('disable', true);
                Swal.fire({
                    icon: 'info',
                    title: 'Valores Por Período',
                    html: 'Não foi possível localizar os valores do equipamento.'
                });
            }
        });

        return false;
    });

    $(document).on('keyup change', '#parcels [name="due_day[]"]', function(){
        let days = parseInt($(this).val());
        const el = $(this).closest('.form-group');

        el.find('[name="due_date[]"]').val(sumDaysDateNow(days));
    });

    $(document).on('blur', '#parcels [name="due_date[]"]', function(){
        const dataVctoInput = $(this).val();
        if (dataVctoInput === '') {
            return false;
        }

        const diasVcto = calculateDays(getTodayDateEn(false), dataVctoInput);
        const el = $(this).closest('.form-group');

        el.find('[name="due_day[]"]').val(diasVcto);
    });

    $('#add_parcel').on('click', function(){
        const parcels = $('#parcels .parcel').length;

        if (parcels === 24) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>É permitido adicionar até 24 vencimentos.</li></ol>'
            });
            return false;
        }

        $('#parcels').show().append(
            createParcel(parcels)
        ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({thousands: '.', decimal: ',', allowZero: true}).closest('.payment-item').find('.remove-payment').tooltip();

        recalculeParcels();
    });

    $(document).on('click', '.remove-payment', function(){
        const parcels = $('#parcels .parcel').length;

        if (parcels === 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>Deve conter no mínimo uma linha de pagamento.</li></ol>'
            });
            return;
        }

        $('#parcels').find('.remove-payment').tooltip('dispose');

        $(this).closest('.parcel').remove();

        $('#parcels').find('.remove-payment').tooltip();

        recalculeParcels();
    });

    $('#automatic_parcel_distribution').on('change', function(){
        const check = $(this).is(':checked');

        if (check) {
            $('#parcels .parcel [name="value_parcel[]"]').attr('readonly', true);
            recalculeParcels();
        } else
            $('#parcels .parcel [name="value_parcel[]"]').attr('readonly', false);

    });

    $(document).on('change', '[name^="vehicle_"], [name^="withdrawal_equipment_actual_vehicle_"]', function (){

        const el_driver = $(this).closest('.row').find('[name^="driver_"], [name^="withdrawal_equipment_actual_driver_"]');

        const vehicle_id = $(this).val();
        if (vehicle_id == '0') {
            return false;
        }

        const el = $(this).closest('.card-body');
        const driver_actual = parseInt(el_driver.val());

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            data: { vehicle_id },
            url: $('#routeGetVehicle').val() + `/${vehicle_id}`,
            async: true,
            success: response => {
                if (response.driver_id && el_driver.val() === '0') {
                    el_driver.val(response.driver_id)
                } else if (response.driver_id && driver_actual !== parseInt(response.driver_id)) {
                    Swal.fire({
                        title: 'Alteração de Motorista',
                        html: `O veículo selecionado contém relacionado o motorista <b>${response.driver_name}</b>. <br>Deseja atualizar?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#19d895',
                        cancelButtonColor: '#bbb',
                        confirmButtonText: 'Sim, atualizar',
                        cancelButtonText: 'Não atualizar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            el_driver.val(response.driver_id)
                        }
                    })
                }
            }
        });
    });

    $('#calculate_net_amount_automatic').on('change', function(){
        if ($(this).is(':checked')) {
            $('#discount_value').attr('disabled', false).val('0,00');
            $('#extra_value').attr('disabled', false).val('0,00');
            $('#net_value').attr('disabled', true);
            reloadTotalRental();
        } else {
            $('#net_value').attr('disabled', false);
            $('#discount_value').attr('disabled', true);
            $('#extra_value').attr('disabled', true);
        }
    });

    $("#createRental").on("hidden.bs.modal", function () {
        window.location.reload();
    });

    $('[name="type_rental"]').on('ifChanged', function() {
        if (parseInt($(this).val()) === 0) {
            if (!$('#parcels .parcel').length && $('[name="rental_id"]').length) {
                $('#add_parcel').trigger('click');
            }
        }
    });

    $(document).on('change', '.withdrawal_equipment_actual', function(){
        const content = $(this).closest('.card-body');

        if ($(this).is(':checked')) {
            content.find('.content_input_withdrawal_equipment_actual, .content_driver_vehicle_withdrawal_equipment_actual').css('display', 'flex');
        } else {
            content.find('.content_input_withdrawal_equipment_actual, .content_driver_vehicle_withdrawal_equipment_actual').css('display', 'none');
        }
    });

    $(document).on("click", '#equipments-selected [data-target="#newVehicleModal"], #equipments-selected [data-target="#newDriverModal"]', function () {
        const modal = $(this).data('target');
        const select_name = $(this).closest('.input-group').find('select').prop('name');

        $(`${modal} [name="element_to_load"]`).val(select_name);
    });
});
const setErrorStepWrong = step => {

    setTimeout(() => {
        $('#formRental .steps ul li').removeClass('error');
        for (let i = 0; i < step; i++) {
            $(`#formRental .steps ul li:eq(${i})`).removeClass('current').addClass('done');
        }

        $(`#formRental .steps ul li:eq(${step})`).addClass('error').find('a').trigger('click');
    }, 150);
}

const recalculeParcels = () => {
    if ($('#automatic_parcel_distribution').is(':checked')) {
        const total_rental_paid   = parseFloat($('[name="total_rental_paid"]').val() ?? 0);
        const parcels = $('#parcels .parcel').length;
        const netValue = realToNumber($('#net_value').val()) - total_rental_paid;

        let valueSumParcel = parseFloat("0");
        let valueParcel = netValue / parcels;

        for (let count = 0; count < parcels; count++) {

            if ((count + 1) === parcels) {
                valueParcel = netValue - valueSumParcel;
            }

            valueSumParcel += parseFloat((netValue / parcels).toFixed(2));
            $(`#parcels .parcel [name="value_parcel[]"]:eq(${count})`).val(numberToReal(valueParcel));
        }
    }
}

const createParcel = (due, due_day = null, due_date = null, due_value = null, view_btn_delete = true) => {

    let last_day = parseInt($('#parcels .parcel:last [name="due_day[]"]').val());

    if (isNaN(last_day)) {
        last_day = 0;
    } else {
        last_day += 30
    }

    due_day = due_day === null ? last_day : due_day;
    due_date = due_date === null ? sumDaysDateNow(last_day) : due_date;
    due_value = due_value === null ? '0,00' : numberToReal(due_value);

    const disabledValue = $('#automatic_parcel_distribution').is(':checked') ? 'readonly' : '';
    const delete_button = view_btn_delete ? `<div class="input-group-prepend stock-Equipment-payment col-md-1 no-padding"><button type="button" class="btn btn-danger btn-flat w-100 remove-payment" title="Excluir Pagamento"><i class="fa fa-trash"></i></button></div>` : '';
    return `<div class="form-group mt-1 parcel">
        <div class="d-flex align-items-center justify-content-between payment-item">
            <div class="input-group col-md-12 no-padding">
                <input type="text" class="form-control col-md-3 text-center" name="due_day[]" value="${due_day}">
                <input type="date" class="form-control col-md-4 text-center" name="due_date[]" value="${due_date}">
                <div class="input-group-prepend col-md-1 no-padding">
                    <span class="input-group-text pl-3 pr-3 col-md-12"><strong>R$</strong></span>
                </div>
                <input type="text" class="form-control col-md-3 no-border-radius text-center" name="value_parcel[]" value="${due_value}" ${disabledValue}>
                ${delete_button}
            </div>
        </div>
    </div>`
}

const equipmentMessageDefault = message => {
    $('table.list-equipment tbody').append(`
            <tr>
                <td class="text-left"><h6 class="text-center">${message}</h6></td>
            </tr>
        `);
}

const showSeparatorEquipmentSelected = () => {
    if ($('#equipments-selected div').length)
        $('.equipments-selected hr.separator-dashed').slideDown(300);
    else
        $('.equipments-selected hr.separator-dashed').slideUp(300);
}

const fixEquipmentDates = () => {
    let notUseDateWithdrawal = $('#not_use_date_withdrawal').is(':checked');
    let dateDelivery = $('input[name="date_delivery"]').val();
    let dateWithdrawal = $('input[name="date_withdrawal"]').val();

    $('#equipments-selected div.card').each(function() {
        if (!$('.use_date_diff_equip', this).is(':checked')) {
            $('input[name^="date_delivery_equipment_"]', this).val(dateDelivery);
            $('input[name^="date_withdrawal_equipment_"]', this).val(dateWithdrawal);
            $('.not_use_date_withdrawal', this).prop('checked', notUseDateWithdrawal);
        }
    });
    checkLabelAnimate();

}

const changeStepPosAbsolute = () => {
    $('.wizard > .content > .body').css('position', 'absolute');
}

const changeStepPosUnset = () => {
    setTimeout(() => { $('.wizard > .content > .body').css('position', 'unset') }, 100);
}

const getStockEquipment = async idEquipment => {
    return await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetStockEquipment').val(),
        data: {idEquipment},
        async: true,
        success: response => {
            return response;
        }
    });
}

const getPriceEquipment = async idEquipment => {
    const check_not_use_date_withdrawal = $(`#collapseEquipment-${idEquipment} input[name="not_use_date_withdrawal"]`).is(':checked');
    let diffDays = false;

    if (!check_not_use_date_withdrawal) {
        let dateDelivery = new Date(transformDateForEn($(`#collapseEquipment-${idEquipment} input[name^="date_delivery_equipment_"]`).val().split(' ')[0]).replace(/-/g, '/'));
        let dateWithdrawal = new Date(transformDateForEn($(`#collapseEquipment-${idEquipment} input[name^="date_withdrawal_equipment_"]`).val().split(' ')[0]).replace(/-/g, '/'));

        let timeDiff = Math.abs(dateWithdrawal.getTime() - dateDelivery.getTime());
        diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
    }

    return await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetPriceEquipment').val(),
        data: {idEquipment, diffDays},
        async: true,
        success: response => {
            return response;
        }, error: e => {
            console.log(e)
        }
    });
}

const getEquipment = async equipment => {

    let data = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: $('#routeGetEquipment').val() + `/${equipment}`,
        async: true,
        success: response => {
            return response;
        }, error: e => { console.log(e) }
    });

    return data.success ? data.data : false;
}

const createEquipmentPayment = async (equipment, priceStock = null, unity_price = null, total_price = null, quantity = null) => {

    let dataEquipment   = await getEquipment(equipment);
    let stockEquipment  = quantity === null ? $(`#collapseEquipment-${equipment} input[name^="stock_equipment_"]`).val() : quantity;
    const priceEquipment    = unity_price === null ? (priceStock === null ? await getPriceEquipment(equipment) : priceStock.price) : unity_price;
    let priceEquipmentFormat = numberToReal(priceEquipment);
    let priceEquipmentTotal  = numberToReal(total_price === null ? (priceEquipment * stockEquipment) : total_price);

    if (!dataEquipment) {
        return false;
    }

    let paymentEquipment = `
        <li class="pb-3" id-equipment="${equipment}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex col-md-6 no-padding">
                    <div class="ml-3">
                        <h6 class="mb-1">${dataEquipment.name}</h6>
                        <small class="text-muted"><strong>${dataEquipment.reference}</strong></small>
                    </div>
                </div>
                <div class="input-group col-md-6 no-padding payment-invert-stock">
                    <div class="input-group-prepend stock-equipment-payment">
                        <span class="input-group-text pl-3 pr-3"><strong>${stockEquipment}un</strong></span>
                    </div>
                    <input type="text" class="form-control price-un-equipment payment-hidden" id="price-un-equipment-${equipment}" value="${priceEquipmentFormat}" disabled>
                    <div class="input-group-prepend payment-hidden">
                        <span class="input-group-text pl-3 pr-3"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control price-total-equipment payment-hidden" name="priceTotalEquipment_${equipment}" id="price-total-equipment-${equipment}" value="${priceEquipmentTotal}">
                </div>
            </div>
        </li>
    `;

    $('.list-equipments-payment').append(paymentEquipment);

    //setTimeout(() => {
        $(`#price-un-equipment-${equipment}, #price-total-equipment-${equipment}`).maskMoney({thousands: '.', decimal: ',', allowZero: true});
        if ($('.list-equipments-payment li').length === 1) {
            $('.list-equipments-payment li').addClass('one-li-list-equipments-payment');
        } else {
            $('.list-equipments-payment li').removeClass('one-li-list-equipments-payment');
        }

        $(`#price-total-equipment-${equipment}`).on('keyup', () => {
            reloadTotalRental();
        }).on('blur', function(){
            if ($(this).val() === '') {
                $(this).val('0,00')
            }
        });
    //}, 250);
}

const reloadTotalRental = async () => {

    let grossValue          = 0;
    let priceEquipment      = 0;
    let discount    = realToNumber($('#discount_value').val());
    let extra       = realToNumber($('#extra_value').val());
    let netAmount   = realToNumber($('#net_value').val());
    let total_rental_paid   = parseFloat($('[name="total_rental_paid"]').val() ?? 0);
    let total_rental_no_paid= parseFloat($('[name="total_rental_no_paid"]').val() ?? 0);

    discount    = isNaN(discount) ? 0 : discount;
    extra       = isNaN(extra) ? 0 : extra;

    $('.list-equipments-payment li').each(function() {
        priceEquipment = realToNumber($('.price-total-equipment', this).val());
        grossValue += isNaN(priceEquipment) ? 0 : priceEquipment;
    });

    grossValue += total_rental_paid;
    grossValue += total_rental_no_paid;

    if ($('#is_exchange').length) {
        grossValue += discount;
        grossValue -= extra;
    }

    $('#gross_value').text(numberToReal(grossValue));

    if ($('#calculate_net_amount_automatic').is(':checked')) {
        $('#net_value').val(numberToReal(grossValue - discount + extra));
    } else {
        if (grossValue > netAmount) {
            $('#discount_value').val(numberToReal(grossValue - netAmount));
            $('#extra_value').val('0,00');
        } else {
            $('#extra_value').val(numberToReal(netAmount - grossValue));
            $('#discount_value').val('0,00');
        }
    }

    if ($('#automatic_parcel_distribution').is(':checked')) {
        await recalculeParcels();
    }

    return grossValue - discount + extra;
}

const getPriceStockEquipments = async idEquipment => {

    let arrDiffDays = [];
    let arrEquipments = [];
    let dateDelivery, dateWithdrawal, timeDiff, diffDays, not_use_date_withdrawal;

    $('#equipments-selected div.card').each(async function() {
        not_use_date_withdrawal = $('.not_use_date_withdrawal', this).is(':checked');
        idEquipment             = parseInt($('.card-header', this).attr('id-equipment'));
        diffDays                = false;

        if (!not_use_date_withdrawal) {
            dateDelivery = new Date(transformDateForEn($('input[name^="date_delivery_equipment_"]', this).val().split(' ')[0]).replace(/-/g, '/'));
            dateWithdrawal = new Date(transformDateForEn($('input[name^="date_withdrawal_equipment_"]', this).val().split(' ')[0]).replace(/-/g, '/'));

            timeDiff = Math.abs(dateWithdrawal.getTime() - dateDelivery.getTime());
            diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
        }
        arrDiffDays[idEquipment] = diffDays;
        arrEquipments.push(idEquipment);
    });

    if (!arrDiffDays.length || !arrEquipments.length) {
        return false;
    }

    return await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetPriceStockEquipments').val(),
        data: {arrEquipments, arrDiffDays},
        async: true,
        success: response => {
            return response;
        }, error: e => {
            console.log(e)
        }
    });
}

const setEquipmentRental = (
    idEquipment,
    quantity = null,
    vehicle_suggestion = null,
    driver_suggestion = null,
    use_date_diff_equip = null,
    expected_delivery_date = null,
    expected_withdrawal_date = null,
    not_use_date_withdrawal = null,
    is_exchange = false,
    rentalEquipmentId = '',
) => {
    $(`.equipment[id-equipment="${idEquipment}"]`)
        .empty()
        .toggleClass('equipment load-equipment')
        .append('<td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando ...</td>')

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: $('#routeGetEquipment').val() + `/${idEquipment}/0`,
        success: response => {

            if (!response.success) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: `<ol><li>${response.data}</li></ol>`
                });
                searchEquipmentOld = '';
                $('#searchEquipment').trigger('blur');
                return false;
            }

            const permissions = response.permissions;
            response = response.data;

            const rental_id = $('[name="rental_id"]').val() ?? 0;
            let date_delivery = $('input[name="date_delivery"]').val();
            let date_withdrawal = $('input[name="date_withdrawal"]').val();
            const disabledVehicle = permissions.vehicle ? '' : 'disabled';
            const disabledDriver = permissions.driver ? '' : 'disabled';
            const readonlyFields = is_exchange ? 'readonly' : '';
            const disabledFields = is_exchange ? 'disabled' : '';

            let prefix_equipment_name = '';
            let field_old_equipment_id = '';
            let equipment_quantity = 1;
            let equipment_vehicle = 0;
            let equipment_driver = 0;
            let equipment_use_date_diff_equip = '';
            let equipment_use_date_diff_equip_date = 'disabled';
            let equipment_content_use_date_diff_equip = 'display-none';
            let equipment_not_use_date_withdrawal = '';
            let equipment_disabled_not_use_date_withdrawal = '';
            let equipment_disabled_not_use_date_withdrawal_equip = '';

            if (quantity !== null) {
                equipment_quantity = quantity;
                prefix_equipment_name = '<span class="font-weight-bold">[ATUAL]</span> '
                field_old_equipment_id = `<input type="hidden" name = "old_equipment_id_${response.id}" value = "${response.id}">`;
            }
            if (vehicle_suggestion !== null) {
                equipment_vehicle = vehicle_suggestion;
            }
            if (driver_suggestion !== null) {
                equipment_driver = driver_suggestion;
            }
            if (use_date_diff_equip !== null && use_date_diff_equip == 1) {
                equipment_use_date_diff_equip = 'checked';
                equipment_use_date_diff_equip_date = '';
                equipment_content_use_date_diff_equip = '';
            }
            if (expected_delivery_date !== null) {
                date_delivery = transformDateForBr(expected_delivery_date);
            }
            if (expected_withdrawal_date !== null) {
                date_withdrawal = transformDateForBr(expected_withdrawal_date);
            }
            if (not_use_date_withdrawal !== null && not_use_date_withdrawal == 1) {
                equipment_not_use_date_withdrawal = 'checked';
                equipment_disabled_not_use_date_withdrawal = '';
                equipment_disabled_not_use_date_withdrawal_equip = 'disabled';
            }

            const document_is_exchange = parseInt($('#is_exchange').val() ?? 0);
            const sizeButtonWithBtnOption = is_exchange ? 'col-md-12' : 'col-md-9' ;
            const btnActionEquipment = is_exchange ? `<a class="exchange-equipment pull-right" data-toggle="tooltip" title="Trocar Equipamento"><i class="fa fa fa-arrow-right-arrow-left"></i></a>` : `<a class="remove-equipment pull-right" data-toggle="tooltip" title="Remover"><i class="fa fa-trash"></i></a>`;
            const btnViewValuePerPeriod = is_exchange ? '' : `<div class="input-button-calendar col-md-3 no-padding"><button class="input-button pull-right btn-primary w-100 btn-view-price-period-equipment" data-toggle="tootip" title="Visualizar valor por período" id-equipment="${response.id}" ${disabledFields}><i class="fas fa-file-invoice-dollar"></i></button></div>`;
            const classViewValuePerPeriod = is_exchange ? '' : 'flatpickr-input bbr-r-0 btr-r-0';
            const btnNewVehicle = is_exchange ? '' : `<div class="input-group-addon input-group-append"><button type="button" class="btn btn-success" data-toggle="modal" data-target="#newVehicleModal" title="Novo Veículo" ${disabledVehicle} ${disabledFields}><i class="fas fa-plus-circle"></i></button></div>`;
            const btnNewDriver = is_exchange ? '' : `<div class="input-group-addon input-group-append"><button type="button" class="btn btn-success" data-toggle="modal" data-target="#newDriverModal" title="Novo Motorista" ${disabledDriver} ${disabledFields}><i class="fas fa-plus-circle"></i></button></div>`;
            const btnDateDelivery = is_exchange ? '' : `<div class="input-button-calendar col-md-3 no-padding calendar_equipment"><a class="input-button pull-left btn-primary" title="toggle" data-toggle ${equipment_use_date_diff_equip_date} ${disabledFields}><i class="fa fa-calendar text-white"></i></a><a class="input-button pull-right btn-primary" title="clear" data-clear ${equipment_use_date_diff_equip_date} ${disabledFields}><i class="fa fa-times text-white"></i></a></div>`;
            const btnDateWithdrawal = is_exchange ? '' : `<div class="input-button-calendar col-md-3 no-padding calendar_equipment"><a class="input-button pull-left btn-primary" title="toggle" data-toggle ${equipment_disabled_not_use_date_withdrawal_equip} ${disabledFields}><i class="fa fa-calendar text-white"></i></a><a class="input-button pull-right btn-primary" title="clear" data-clear ${equipment_disabled_not_use_date_withdrawal_equip} ${disabledFields}><i class="fa fa-times text-white"></i></a></div>`;
            let content_equipments = '#equipments-selected';
            let heading_equipments = 'headingEquipment';
            let collapse_equipments = 'collapseEquipment';
            let data_equipment_to_exchange = '';
            let prefix_field = '';
            let input_actual_equipment = '';
            let hide_use_date_diff_equip = '';
            let content_withdrawal_equipment_at_exchange = '';
            if (is_exchange) {
                content_equipments = '#equipments-selected-to-exchange';
                heading_equipments = 'headingEquipmentToExchange';
                collapse_equipments = 'collapseEquipmentToExchange';
                prefix_field = 'exchange_';
            }

            if (document_is_exchange === 1) {
                data_equipment_to_exchange = `data-equipment-to-exchange="${$('#exchangeEquipment [name="equipment-to-exchange"]').val()}"`;
            }

            if (!is_exchange && document_is_exchange === 1) {
                const exchange_equipment_id = $('#exchangeEquipment [name="equipment-to-exchange"]').val();
                prefix_equipment_name = $(`[href="#collapseEquipmentToExchange-${exchange_equipment_id}"]`).html() + '&nbsp;&nbsp;&nbsp;<i class="fa-solid fa-right-long"></i>&nbsp;&nbsp;&nbsp;';

                input_actual_equipment = `<input type="hidden" value="${exchange_equipment_id}" name="exchange_equipment_id_${response.id}">`;
                input_actual_equipment += `<input type="hidden" value="${$('#exchangeEquipment [name="rental-equipment-to-exchange"]').val()}" name="rental_equipment_id_${response.id}">`;

                equipment_use_date_diff_equip = 'checked';
                equipment_content_use_date_diff_equip = 'mt-4';
                date_delivery = getTodayDateBr(true, false);
                date_withdrawal = transformDateForBr(sumMinutesDateNow(1, true, false));
                hide_use_date_diff_equip = 'd-none';
                equipment_use_date_diff_equip_date = '';
                equipment_not_use_date_withdrawal = '';
                content_withdrawal_equipment_at_exchange = `
                <div class="row mt-4">
                    <div class="form-group col-md-6">
                        <div class="switch pt-1">
                            <input type="checkbox" class="check-style check-xs withdrawal_equipment_actual" name="withdrawal_equipment_actual_${response.id}" id="withdrawal_equipment_actual_${response.id}">
                            <label for="withdrawal_equipment_actual_${response.id}" class="check-style check-xs"></label> Retirar o equipamento atual
                        </div>
                    </div>
                    <div class="form-group flatpickr col-md-6 content_input_withdrawal_equipment_actual display-none">
                        <label class="label-date-btns">Data Prevista de Retirada</label>
                        <input type="text" name="date_withdrawal_equipment_actual_${response.id}" class="form-control" value="${getTodayDateBr(true, false)}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                        <div class="input-button-calendar col-md-3 no-padding calendar_equipment">
                            <a class="input-button pull-left btn-primary" title="toggle" data-toggle>
                                <i class="fa fa-calendar text-white"></i>
                            </a>
                            <a class="input-button pull-right btn-primary" title="clear" data-clear>
                                <i class="fa fa-times text-white"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="content_driver_vehicle_withdrawal_equipment_actual display-none col-md-12 no-padding">
                        <div class="form-group col-md-6 label-animate">
                            <label>Veículo</label>
                            <div class="input-group label-animate">
                                <select class="form-control" name="withdrawal_equipment_actual_vehicle_${response.id}" disabled>
                                    <option>Carregando ...</option>
                                </select>
                                ${btnNewVehicle}
                            </div>
                        </div>
                        <div class="form-group col-md-6 label-animate">
                            <label>Motorista</label>
                            <div class="input-group label-animate">
                                <select class="form-control" name="withdrawal_equipment_actual_driver_${response.id}" disabled>
                                    <option>Carregando ...</option>
                                </select>
                                ${btnNewDriver}
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            let regEquipment = `
            <div class="card">
                <div class="card-header" role="tab" id="${heading_equipments}-${response.id}" id-equipment="${response.id}" rental-equipment-id="${rentalEquipmentId}" data-rental-id="${rental_id}" ${data_equipment_to_exchange}>
                    <h5 class="mb-0 d-flex align-items-center">
                        <a class="collapsed pull-left w-100" data-toggle="collapse" href="#${collapse_equipments}-${response.id}" aria-expanded="false" aria-controls="${collapse_equipments}-${response.id}" is-cacamba="${response.cacamba}">
                            ${prefix_equipment_name}${response.name}
                        </a>`;
                    regEquipment += btnActionEquipment;
                    regEquipment += `</h5>
                </div>
                <div id="${collapse_equipments}-${response.id}" class="collapse" role="tabpanel" aria-labelledby="${heading_equipments}-${response.id}" data-parent="${content_equipments}" id-equipment="${response.id}" rental-equipment-id="${rentalEquipmentId}">
                    <input type="hidden" value="${response.id}" name="${prefix_field}equipment_id[]">
                    ${input_actual_equipment}
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8" style="margin-top: -20px">
                                <div class="form-group">
                                    <label>Referência</label>
                                    <input type="text" class="form-control" value="${response.reference}" name="${prefix_field}reference_equipment_${response.id}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group flatpickr label-animate stock-group d-flex">
                                    <label class="label-date-btns">Quantidade</label>
                                    <input type="tel" name="${prefix_field}stock_equipment_${response.id}" class="form-control ${sizeButtonWithBtnOption} ${classViewValuePerPeriod}" value="${equipment_quantity}" max-stock="${response.stock}" ${readonlyFields}>`;
                    regEquipment += btnViewValuePerPeriod;
                    regEquipment += `</div>
                                <small class="text-danger font-weight-bold stock_available pull-left">Disponível: ${response.stock}</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 label-animate">
                                <label>Veículo</label>
                                <div class="input-group label-animate">
                                    <select class="form-control" name="${prefix_field}vehicle_${response.id}" disabled ${readonlyFields}>
                                        <option>Carregando ...</option>
                                    </select>`;
                    regEquipment += btnNewVehicle;
                    regEquipment += `</div>
                            </div>
                            <div class="form-group col-md-6 label-animate">
                                <label>Motorista</label>
                                <div class="input-group label-animate">
                                    <select class="form-control" name="${prefix_field}driver_${response.id}" disabled ${readonlyFields}>
                                        <option>Carregando ...</option>
                                    </select>`;
                    regEquipment += btnNewDriver;
                    regEquipment += `
                                </div>
                            </div>
                        </div>
                        <div class="row ${hide_use_date_diff_equip}">
                            <div class="col-md-12">
                                <div class="switch pt-3">
                                    <input type="checkbox" class="check-style check-xs use_date_diff_equip" name="${prefix_field}use_date_diff_equip_${response.id}" id="${prefix_field}use_date_diff_equip_${response.id}" ${equipment_use_date_diff_equip} ${disabledFields}>
                                    <label for="${prefix_field}use_date_diff_equip_${response.id}" class="check-style check-xs"></label> Usar datas de entrega e/ou retirada diferentes para esse equipamento.
                                </div>
                            </div>
                        </div>
                        <div class="row ${equipment_content_use_date_diff_equip} use_date_diff_equip_show mt-2">
                            <div class="col-md-6">
                                <div class="form-group flatpickr d-flex">
                                    <label class="label-date-btns">Data Prevista de Entrega</label>
                                    <input type="text" name="${prefix_field}date_delivery_equipment_${response.id}" class="form-control ${sizeButtonWithBtnOption}" value="${date_delivery}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input ${equipment_use_date_diff_equip_date} ${readonlyFields}>`;
                    regEquipment += btnDateDelivery;
                    regEquipment += `</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group flatpickr d-flex">
                                    <label class="label-date-btns">Data Prevista de Retirada</label>
                                    <input type="text" name="${prefix_field}date_withdrawal_equipment_${response.id}" class="form-control ${sizeButtonWithBtnOption}" value="${date_withdrawal}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input ${equipment_disabled_not_use_date_withdrawal_equip} ${disabledFields}>`;
                    regEquipment += btnDateWithdrawal;
                    regEquipment += `</div>
                                <div class="form-group">
                                    <div class="switch pt-1">
                                        <input type="checkbox" class="check-style check-xs not_use_date_withdrawal" name="${prefix_field}not_use_date_withdrawal_equip_${response.id}" id="${prefix_field}not_use_date_withdrawal_${response.id}" ${equipment_not_use_date_withdrawal} ${equipment_disabled_not_use_date_withdrawal} ${disabledFields}>
                                        <label for="${prefix_field}not_use_date_withdrawal_${response.id}" class="check-style check-xs"></label> Não informar data de retirada
                                    </div>
                                </div>
                            </div>
                        </div>`;
            regEquipment += content_withdrawal_equipment_at_exchange;
            regEquipment += `<div class="row">
                            <div class="form-group col-md-12 mt-2">
                                <button type="button" class="btn btn-primary pull-right hideEquipment" id-equipment="${response.id}"><i class="fa fa-angle-up"></i> Ocultar</button>
                            </div>
                        </div>
                        ${field_old_equipment_id}
                    </div>
                </div>
            </div>`;
            $(`${content_equipments}`).append(regEquipment);
            $(`.load-equipment[id-equipment="${idEquipment}"]`).hide(300);
            showSeparatorEquipmentSelected();
            $('#cleanSearchEquipment').trigger('click')
            //setTimeout(() => {
                $(`.load-equipment[id-equipment="${idEquipment}"]`).remove();

                if (!$(`.list-equipment tbody tr`).length) {
                    equipmentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                }
                checkLabelAnimate();

                // é uma edição, não adição.
                if (quantity === null) {
                    $(`#${collapse_equipments}-${idEquipment}`).collapse('show');
                }

                $(`#${collapse_equipments}-${idEquipment} input[name^="stock_equipment_"]`).mask('0#');
                $(`#${collapse_equipments}-${idEquipment} input[name^="date_withdrawal_equipment_"]`).inputmask();
                $(`#${collapse_equipments}-${idEquipment} input[name^="date_delivery_equipment_"]`).inputmask();
                $(`#${collapse_equipments}-${idEquipment} .flatpickr:not(.stock-group)`).flatpickr({
                    enableTime: true,
                    dateFormat: "d/m/Y H:i",
                    time_24hr: true,
                    wrap: true,
                    clickOpens: false,
                    allowInput: true,
                    locale: "pt",
                    onClose: function(selectedDates, dateStr, instance){
                        checkLabelAnimate();
                    }
                });
                if (not_use_date_withdrawal === null && $('#not_use_date_withdrawal').is(':checked')) {
                    $(`#${collapse_equipments}-${idEquipment} input[name^="date_withdrawal_equipment_"]`).val('');
                    $(`#${collapse_equipments}-${idEquipment} .not_use_date_withdrawal`).prop('checked', true);
                }
                $(`#${collapse_equipments}-${idEquipment} .btn-view-price-period-equipment`).tooltip();

                if (response.cacamba) {
                    $('.container-residues').slideDown('slow');
                }

                loadVehicles(equipment_vehicle,`#${collapse_equipments}-${idEquipment} select[name^="vehicle_"], #${collapse_equipments}-${idEquipment} select[name^="withdrawal_equipment_actual_vehicle_"]`);
                loadDrivers(equipment_driver, `#${collapse_equipments}-${idEquipment} select[name^="driver_"], #${collapse_equipments}-${idEquipment} select[name^="withdrawal_equipment_actual_driver_"]`);

                if (document_is_exchange === 1) {
                    const equipment_actual_to_exchange = $('#exchangeEquipment [name="equipment-to-exchange"]').val();
                    if (equipment_actual_to_exchange) {
                        $('#exchangeEquipment').modal('hide');
                        $(`#headingEquipmentToExchange-${equipment_actual_to_exchange} a`).attr('disabled', true);
                        $(`#headingEquipmentToExchange-${equipment_actual_to_exchange}`).attr('disabled', true);
                        $(`#${heading_equipments}-${equipment_actual_to_exchange} [data-toggle="tooltip"]`).tooltip();
                        updateCardEquipment(equipment_actual_to_exchange);
                    } else {
                        $(`#${heading_equipments}-${idEquipment} [data-toggle="tooltip"]`).tooltip();
                    }
                } else {
                    $(`#not_use_date_withdrawal_${idEquipment}`).trigger('change');
                    $(`#${heading_equipments}-${idEquipment} [data-toggle="tooltip"]`).tooltip();
                }

            //}, 350);
        }, error: e => {
            console.log(e);
        },
        complete: function(xhr) {
            if (xhr.status === 403) {
                Toast.fire({
                    icon: 'error',
                    title: 'Você não tem permissão para fazer essa operação!'
                });
            }
        }
    });
}

const getEquipmentsRental = (rental_id, is_budget, callback) => {
    const path_request = is_budget ? 'orcamento' : 'locacao';

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: `${$('[name="base_url"]').val()}/ajax/${path_request}/equipamentos/${rental_id}`,
        async: true,
        success: response => {
            callback(response);
        }, error: e => { console.log(e) }
    });
}

const getPaymentsRental = (rental_id, is_budget, callback) => {
    const path_request = is_budget ? 'orcamento' : 'locacao';

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: `${$('[name="base_url"]').val()}/ajax/${path_request}/pagamentos/${rental_id}`,
        async: true,
        success: response => {
            callback(response);
        }, error: e => { console.log(e) }
    });
}
