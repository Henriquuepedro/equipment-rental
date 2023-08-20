(function ($) {
    'use strict';
    var form = $("#formRental");
    var budget = $('#budget').val() ? true : false;
    form.steps({
        headerTag: "h3",
        bodyTag: "div.stepRental",
        transitionEffect: "slideLeft",
        stepsOrientation: "vertical",
        onStepChanging: function (event, currentIndex, newIndex)
        {
            let debug = false;
            let arrErrors = [];
            let notUseDateWithdrawal = $('#not_use_date_withdrawal').val();
            let typeLocation = parseInt($('input[name="type_rental"]').val());

            if (currentIndex === 0) {
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }

                if ($('#equipments-selected div').length === 0) {
                    if (currentIndex < 0) {
                        setErrorStepWrong(0);
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Faça a troca de pelo menos um equipamento.</li></ol>'
                    });
                    return false;
                }

                let idEquipment,
                    stockEquipment,
                    nameEquipment,
                    stockMax,
                    dateDeliveryTime,
                    dateWithdrawalTime,
                    withdrawal_equipment,
                    date_withdrawal_equipment,
                    driver_withdrawal_equipment,
                    vehicle_withdrawal_equipment;

                $('#equipments-selected div.card').each(function() {
                    idEquipment             = parseInt($('.card-header', this).attr('id-equipment'));
                    stockEquipment          = parseInt($('[name^="stock_equipment_"]', this).val());
                    nameEquipment           = $('.card-header a:eq(0)', this).html();
                    stockMax                = parseInt($('[name^="stock_equipment_"]', this).attr('max-stock'));
                    withdrawal_equipment    = $('.withdrawal_equipment_actual', this).is(':checked');

                    if (isNaN(stockEquipment) || stockEquipment === 0) {
                        arrErrors.push(`O equipamento<br><strong>${nameEquipment}</strong><br>deve ser informado uma quantidade.`);
                    } else if (stockEquipment > stockMax && !budget) {
                        arrErrors.push(`O equipamento<br><strong>${nameEquipment}</strong><br>não tem estoque suficiente. <strong>Disponível: ${stockMax} un</strong>`);
                    }

                    notUseDateWithdrawal = $('.not_use_date_withdrawal', this).is(':checked');

                    dateDeliveryTime = new Date(transformDateForEn($('input[name^="date_delivery_equipment_"]', this).val())).getTime();
                    dateWithdrawalTime = new Date(transformDateForEn($('input[name^="date_withdrawal_equipment_"]', this).val())).getTime();

                    if ((dateDeliveryTime === 0 || (!notUseDateWithdrawal && dateWithdrawalTime === 0))) {
                        arrErrors.push(`A data prevista de entrega e data prevista de retirada do equipamento<br><strong>${nameEquipment}</strong><br>deve ser informada corretamente.`);
                    } else if (!notUseDateWithdrawal && dateDeliveryTime >= dateWithdrawalTime) {
                        arrErrors.push(`A data prevista de entrega do equipamento<br><strong>${nameEquipment}</strong><br>não pode ser maior ou igual que a data prevista de retirada.`);
                    }

                    if (withdrawal_equipment) {
                        date_withdrawal_equipment    = new Date(transformDateForEn($('[name^="date_withdrawal_equipment_actual_"]', this).val())).getTime();
                        driver_withdrawal_equipment  = parseInt($('[name^="withdrawal_equipment_actual_driver_"]', this).val());
                        vehicle_withdrawal_equipment = parseInt($('[name^="withdrawal_equipment_actual_vehicle_"]', this).val());

                        if (date_withdrawal_equipment === 0 || driver_withdrawal_equipment === 0 || vehicle_withdrawal_equipment === 0) {
                            arrErrors.push(`Devem ser informados todos os campos para retirar o equipamento atual<br><strong>${nameEquipment}</strong>`);
                        }
                    }
                });

                if (arrErrors.length) {

                    if (currentIndex < 0) {
                        setErrorStepWrong(0);
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                    return false;
                }

                $('div[id^=collapseEquipment-]').collapse('hide');

                if (!$('#parcels .parcel').length) {
                    $('#add_parcel').trigger('click');
                    $('#automatic_parcel_distribution')
                        .prop('checked', true)
                        .trigger('change')
                        .prop('checked', false)
                        .trigger('change');
                }
            }

            $('#formRental .actions a').hide();
            $('#formRental .steps ul li a').attr('disabled', true);
            changeStepPosAbsolute();
            return true;
        },
        onStepChanged: async function (event, currentIndex, priorIndex)
        {
            changeStepPosUnset();
            let arrErrors = [];
            let typeLocation = parseInt($('input[name="type_rental"]:checked').val());


            const rental_p = 'formRental-p-1';
            const payment  = $(`#${rental_p} #payment`);
            typeLocation === 0 ? payment.removeClass('payment-no').addClass('payment-yes') : payment.removeClass('payment-yes').addClass('payment-no');

            if (priorIndex <= 0 && currentIndex >= 1) { // equipamento
                let pricesAndStocks;
                let dataEquipments = [];
                let dataEquipmentsPayCheck = [];
                let newPricesUpdate = [];
                let newPricesUpdateNames = [];
                let idEquipments = [];
                let priceEquipment = 0;
                let idEquipment,stockEquipment,nameEquipment;
                await $('#equipments-selected div.card').each(async function() {
                    idEquipment        = parseInt($('.card-header', this).attr('id-equipment'));
                    stockEquipment     = parseInt($('[name^="stock_equipment_"]', this).val());
                    nameEquipment      = $('.card-header a:eq(0)', this).text();
                    dataEquipments.push([idEquipment, stockEquipment, nameEquipment]);
                    idEquipments.push(idEquipment);
                });

                $('.list-equipments-payment-load').show();
                $('.list-equipments-payment').hide();
                $('#gross_value').html('<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;Calculando');
                if ($('#calculate_net_amount_automatic').is(':checked')) {
                    $('#net_value').val('Calculando...');
                }

                pricesAndStocks = await getPriceStockEquipments(idEquipments);
                if (pricesAndStocks) {
                    await Promise.all(dataEquipments.map(async equipment => {
                        dataEquipmentsPayCheck.push(equipment[0]);
                        if (equipment[1] > pricesAndStocks[equipment[0]].stock && !budget) {
                            $(`#collapseEquipment-${equipment[0]}`).find('input[name^="stock_equipment_"]').attr('max-stock', pricesAndStocks[equipment[0]].stock).val(pricesAndStocks[equipment[0]].stock);
                            $(`#collapseEquipment-${equipment[0]}`).find('.stock_available').text('Disponível: ' + pricesAndStocks[equipment[0]].stock);
                            arrErrors.push(`O equipamento<br><strong>${equipment[2]}</strong><br>não tem estoque suficiente. <strong>Disponível: ${pricesAndStocks[equipment[0]].stock} un</strong>`);
                        }

                        if (!$(`.list-equipments-payment li[id-equipment="${equipment[0]}"]`).length) {
                            await createEquipmentPayment(equipment[0], pricesAndStocks[equipment[0]]);
                        } else {
                            priceEquipment = pricesAndStocks[equipment[0]].price;

                            $(`#price-un-equipment-${equipment[0]}`).val(numberToReal(priceEquipment));
                            $(`.list-equipments-payment li[id-equipment="${equipment[0]}"] .stock-equipment-payment strong`).text(equipment[1] + 'un');

                            if (numberToReal(priceEquipment * equipment[1]) !== $(`#price-total-equipment-${equipment[0]}`).val()) {
                                newPricesUpdate.push({
                                    el: $(`#price-total-equipment-${equipment[0]}`),
                                    price: numberToReal(priceEquipment * equipment[1])
                                });
                                newPricesUpdateNames.push(equipment[2] + ' | R$' + $(`#price-total-equipment-${equipment[0]}`).val() + ' <i class="fas fa-long-arrow-alt-right"></i> R$' + numberToReal(priceEquipment * equipment[1]));
                            }
                        }
                    }));
                }

                await $('.list-equipments-payment li').each(async function() {
                    idEquipment = parseInt($(this).attr('id-equipment'));
                    if (!dataEquipmentsPayCheck.includes(idEquipment)) {
                        $(`.list-equipments-payment li[id-equipment="${idEquipment}"]`).remove();
                    }
                });

                if (arrErrors.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                    changeStepPosUnset();
                    form.steps("previous");
                    let countMenuIndex = 0;
                    $('#formRental .steps ul li').each(function (){
                        countMenuIndex++;
                        if (countMenuIndex > 4) {
                            $(this).removeClass('done').addClass('disabled last');
                        }
                    });
                    $('#formRental .steps ul li.current').addClass('error');
                } else {
                    if (!$('[name="rental_id"]').length && typeLocation === 0 && newPricesUpdate.length) {
                        await Swal.fire({
                            title: newPricesUpdate.length === 1 ? 'Valor de equipamento atualizado.' : 'Valores de equipamentos atualizados.',
                            html: newPricesUpdate.length === 1 ? `O valor do equipamento abaixo foi alterado: <br><br><ol><li><b>${newPricesUpdateNames[0]}</b></li></ol><h4>Deseja atualizar?</h4>` : "Os valores dos equipamentos abaixo foram alterados: <br><br><ol><li><b>" + newPricesUpdateNames.join('</b></li><li><b>') + '</b></li></ol><h4>Deseja atualizar?</h4>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#19d895',
                            cancelButtonColor: '#bbb',
                            confirmButtonText: 'Sim, atualizar',
                            cancelButtonText: 'Não atualizar',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.each(newPricesUpdate, function (key, val) {
                                    val.el.val(val.price);
                                });
                            }

                            reloadTotalRental();
                            $('.list-equipments-payment-load').hide();
                            $('.list-equipments-payment').slideDown('slow');
                        })
                    } else if (!$('[name="rental_id"]').length && typeLocation === 1 && newPricesUpdate.length) {

                        $.each(newPricesUpdate, function (key, val) {
                            val.el.val(val.price);
                        });

                        reloadTotalRental();
                        $('.list-equipments-payment-load').hide();
                        $('.list-equipments-payment').slideDown('slow');

                    } else {
                        reloadTotalRental();
                        $('.list-equipments-payment-load').hide();
                        $('.list-equipments-payment').slideDown('slow');
                    }
                }
            }

            $('#formRental .actions a').show();
            $('#formRental .steps ul li a').attr('disabled', false);

            // Used to skip the "Warning" step if the user is old enough.
            // form.steps("next");
            // form.steps("previous");
        },
        onFinishing: function (event, currentIndex)
        {
            $('#formRental .actions a[href="#finish"]').attr('disabled', true);
            form.validate().settings.ignore = ":disabled";
            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            const netValue = realToNumber($('#net_value').val());
            let typeLocation = parseInt($('input[name="type_rental"]:checked').val());

            if (netValue < 0) {

                if (currentIndex < 1) {
                    setErrorStepWrong(1);
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>Valor líquido da locação não pode ser negativo.</li></ol>'
                });
                return false;
            }

            if (typeLocation === 0) {

                const netValue = realToNumber($('#net_value').val());

                if (netValue == 0) {

                    if (currentIndex < 1) {
                        setErrorStepWrong(1);
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>O valor líquido deve ser maior que zero.</li></ol>'
                    });
                    return false;
                }

                let daysTemp;
                let priceTemp = 0;
                let haveError = [false];

                $('#parcels .parcel, #parcels_paid .parcel').each(function () {
                    priceTemp += realToNumber($('[name="value_parcel[]"]', this).val());
                });

                if (haveError[0]) { // encontrou erro nas datas de vencimento

                    if (currentIndex < 1) {
                        setErrorStepWrong(1);
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: `<ol><li>${haveError[1]}</li></ol>`
                    });
                    $('a[href="#finish"]').attr('disabled', false);
                    return false;
                }

                if (priceTemp.toFixed(2) !== netValue.toFixed(2)) { // os valores das parcelas não corresponde ao valor líquido
                    if ($('#automatic_parcel_distribution').is(':checked')) {
                        recalculeParcels();
                    } else {
                        if (currentIndex < 1) {
                            setErrorStepWrong(1);
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>A soma das parcelas deve corresponder ao valor líquido.</li></ol>'
                        });
                        $('a[href="#finish"]').attr('disabled', false);
                        return false;
                    }
                }
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('#formRental').attr('action'),
                data: $('#formRental').serialize(),
                success: response => {
                    if (response.success) {
                        $('#createRental').modal();
                        $('#createRental h3.code_rental strong').text(response.code);
                        $('#createRental a.rental_print').attr('href', response.urlPrint);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>'+response.message+'</li></ol>'
                        });
                        $('#formRental .actions a[href="#finish"]').attr('disabled', false);
                    }

                }, error: e => {
                    console.log(e);

                    let arrErrors = []

                    $.each(e.responseJSON.errors, function( index, value ) {
                        arrErrors.push(value);
                    });

                    if (!arrErrors.length && e.responseJSON.message !== undefined) {
                        arrErrors.push('Não foi possível identificar o motivo do erro, recarregue a página e tente novamente!');
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                    });
                    $('#formRental .actions a[href="#finish"]').attr('disabled', false);
                },
                complete: function(e) {
                    if (e.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Você não tem permissão para fazer essa operação!'
                        });
                    }
                }
            });
        }
    });
})(jQuery);

$(function() {
    $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500);
    // $('[name="date_withdrawal"], [name="date_delivery"]').mask('00/00/0000 00:00');
    // if (!budget) {
    $('.flatpickr').flatpickr({
        enableTime: true,
        dateFormat: "d/m/Y H:i",
        time_24hr: true,
        wrap: true,
        clickOpens: false,
        allowInput: true,
        locale: "pt",
        onClose: function (selectedDates, dateStr, instance) {
            checkLabelAnimate();
        }
    });
    // }
    $('#discount_value, #extra_value, #net_value').maskMoney({thousands: '.', decimal: ',', allowZero: true});
    loadDrivers(0, '#newVehicleModal [name="driver"]');
    $('[name="type_rental"]').iCheck({
        checkboxClass: 'icheckbox_square',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });

    if ($('#observationDiv').length) {

        var quill = new Quill('#observationDiv', {
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link']
                ]
            },
            theme: 'snow' // or 'bubble'
        });
    }
});

$(document).on('click', '.exchange-equipment', function(){
    const equipment_id = ($(this).parents('[id-equipment]').attr('id-equipment'));
    const rental_equipment_id = ($(this).parents('[id-equipment]').attr('rental-equipment-id'));

    $('#exchangeEquipment [name="equipment-to-exchange"]').val(equipment_id);
    $('#exchangeEquipment [name="rental-equipment-to-exchange"]').val(rental_equipment_id);
    $('#exchangeEquipment').modal();

});

$('#exchangeEquipment').on('hide.bs.modal', function(){
    $('#cleanSearchEquipment').trigger('click');
});

const updateCardEquipment = (equipment) => {
    if (equipment) {
        const el = $(`#collapseEquipment-${equipment}`);
        const not_use_date_withdrawal = $('[id="not_use_date_withdrawal"]').val();
        const date_delivery = $('[name="date_delivery"]').val();
        const date_withdrawal = $('[name="date_withdrawal"]').val();

        if (date_delivery) {
            el.find(`[date_delivery_equipment_${equipment}]`).val(date_delivery);
        }
        if (date_withdrawal && !not_use_date_withdrawal) {
            el.find(`[date_withdrawal_equipment_${equipment}]`).val(date_withdrawal);
        }

        if (not_use_date_withdrawal) {
            $(`#not_use_date_withdrawal_${equipment}`).prop('checked', true);
        }

        $(`#not_use_date_withdrawal_${equipment}`).attr('disabled', false).trigger('change');

        $(`#collapseEquipmentToExchange-${equipment}`).collapse('hide');
    }
}
