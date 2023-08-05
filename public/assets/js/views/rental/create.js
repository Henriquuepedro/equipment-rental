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
            let notUseDateWithdrawal = $('#not_use_date_withdrawal').is(':checked');
            let typeLocation = parseInt($('input[name="type_rental"]:checked').val());

            if (newIndex === getIndexStep(1)) {
                setTimeout(() => {
                    $('[name="client"]').select2();
                    $('[name="state"]').select2('destroy').select2();
                    $('[name="city"]').select2('destroy').select2();
                }, 200);
            }

            if (currentIndex === getIndexStep(0)) {// tipo locacao
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }
                if (!$('input[name="type_rental"]:checked').length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Selecione um tipo de locação.</li></ol>'
                    });
                    return false;
                }
            }
            if (currentIndex <= getIndexStep(1) && newIndex > getIndexStep(1)) { // cliente e endereo
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }

                if ($('select[name="client"]').val() === '0') {
                    arrErrors.push('Selecione um cliente.');
                }

                if (arrErrors.length === 0) {
                    if ($('input[name="address"]').val() === '') {
                        arrErrors.push('Informe um endereço.');
                    }
                    if ($('input[name="number"]').val() === '') {
                        arrErrors.push('Informe um número para o endereço.');
                    }
                    if ($('input[name="neigh"]').val() === '') {
                        arrErrors.push('Informe um bairro.');
                    }
                    if ($('select[name="city"]').val() === '') {
                        arrErrors.push('Informe uma cidade.');
                    }
                    if ($('select[name="state"]').val() === '') {
                        arrErrors.push('Informe um estado.');
                    }
                    if ($('input[name="lat"]').val() === '' || $('input[name="lng"]').val() === '') {
                        arrErrors.push('Confirme o endereço no mapa.');
                    }
                }

                if (arrErrors.length) {

                    if (currentIndex !== getIndexStep(1)) {
                        setErrorStepWrong(getIndexStep(1));
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });

                    return false;
                }
            }
            if (currentIndex <= getIndexStep(2) && newIndex > getIndexStep(2)) { // datas
                if (debug) {
                    changeStepPosAbsolute();
                    fixEquipmentDates();
                    return true;
                }
                let dateDelivery = $('input[name="date_delivery"]').val();
                let dateWithdrawal = $('input[name="date_withdrawal"]').val();

                if (dateDelivery.length < 16) {
                    arrErrors.push('Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm.');
                }
                if (!notUseDateWithdrawal && dateWithdrawal.length < 16) {
                    arrErrors.push('Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm.');
                }

                if (arrErrors.length === 0) {
                    let dateDeliveryTime = new Date(transformDateForEn(dateDelivery)).getTime();
                    let dateWithdrawalTime = new Date(transformDateForEn(dateWithdrawal)).getTime();

                    if (dateDeliveryTime === 0 || (!notUseDateWithdrawal && dateWithdrawalTime === 0)) arrErrors.push('Data prevista de entrega e data prevista de retirada devem ser informadas corretamente.');
                    else if (!notUseDateWithdrawal && dateDeliveryTime >= dateWithdrawalTime) arrErrors.push('Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada.');
                }

                if (arrErrors.length) {
                    if (currentIndex < getIndexStep(2)) {
                        setErrorStepWrong(getIndexStep(2));
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                    return false;
                }

                fixEquipmentDates();
            }
            if (currentIndex <= getIndexStep(3) && newIndex > getIndexStep(3)) { // equipamento
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }

                if ($('#equipments-selected div').length === 0) {
                    if (currentIndex < getIndexStep(3)) {
                        setErrorStepWrong(getIndexStep(3));
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Selecione um equipamento.</li></ol>'
                    });
                    return false;
                }

                let idEquipment,
                    stockEquipment,
                    nameEquipment,
                    stockMax,
                    dateDeliveryTime,
                    dateWithdrawalTime;

                $('#equipments-selected div.card').each(function() {
                    idEquipment        = parseInt($('.card-header', this).attr('id-equipment'));
                    stockEquipment     = parseInt($('[name^="stock_equipment_"]', this).val());
                    nameEquipment      = $('.card-header a:eq(0)', this).text();
                    stockMax            = parseInt($('[name^="stock_equipment_"]', this).attr('max-stock'));

                    if (isNaN(stockEquipment) || stockEquipment === 0) {
                        arrErrors.push(`O equipamento ( <strong>${nameEquipment}</strong> ) deve ser informado uma quantidade.`);
                    } else if (stockEquipment > stockMax && !budget) {
                        arrErrors.push(`O equipamento ( <strong>${nameEquipment}</strong> ) não tem estoque suficiente. <strong>Disponível: ${stockMax} un</strong>`);
                    }

                    notUseDateWithdrawal = $('.not_use_date_withdrawal', this).is(':checked');

                    dateDeliveryTime = new Date(transformDateForEn($('input[name^="date_delivery_equipment_"]', this).val())).getTime();
                    dateWithdrawalTime = new Date(transformDateForEn($('input[name^="date_withdrawal_equipment_"]', this).val())).getTime();

                    if ((dateDeliveryTime === 0 || (!notUseDateWithdrawal && dateWithdrawalTime === 0))) {
                        arrErrors.push(`A data prevista de entrega e data prevista de retirada do equipamento ( <strong>${nameEquipment}</strong> ) deve ser informada corretamente.`);
                    } else if (!notUseDateWithdrawal && dateDeliveryTime >= dateWithdrawalTime) {
                        arrErrors.push(`A data prevista de entrega do equipamento ( <strong>${nameEquipment}</strong> ) não pode ser maior ou igual que a data prevista de retirada.`);
                    }
                });

                if (arrErrors.length) {

                    if (currentIndex < getIndexStep(3)) {
                        setErrorStepWrong(getIndexStep(3));
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                    return false;
                }

                $('div[id^=collapseEquipment-]').collapse('hide');
            }
            if (currentIndex <= getIndexStep(4) && newIndex > getIndexStep(4)) { // pagamento

                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }

                const netValue = realToNumber($('#net_value').val());

                if (netValue < 0) {

                    if (currentIndex < getIndexStep(4)) {
                        setErrorStepWrong(getIndexStep(4));
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Valor líquido da locação não pode ser negativo.</li></ol>'
                    });
                    return false;
                }

                if (typeLocation == 0) {

                    const grossValue    = realToNumber($('#gross_value').text());
                    const netValue      = realToNumber($('#net_value').val());
                    const extraValue    = realToNumber($('#extra_value').val());
                    const discountValue = realToNumber($('#discount_value').val());

                    if (netValue == 0) {

                        if (currentIndex < getIndexStep(4)) {
                            setErrorStepWrong(getIndexStep(4));
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>O valor líquido deve ser maior que zero.</li></ol>'
                        });
                        return false;
                    }

                    // valores divergente
                    if (netValue != (grossValue - discountValue + extraValue)) {
                        if (currentIndex < getIndexStep(4)) {
                            setErrorStepWrong(getIndexStep(4));
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>Soma de valores divergente, recalcule os valores.</li></ol>'
                        });
                        return false;
                    }

                    let daysTemp;
                    let priceTemp = 0;
                    let haveError = [false];

                    $('#parcels .parcel').each(function () {
                        priceTemp += realToNumber($('[name="value_parcel[]"]', this).val());
                    });

                    if (haveError[0]) { // encontrou erro nas datas de vencimento

                        if (currentIndex < getIndexStep(4)) {
                            setErrorStepWrong(getIndexStep(4));
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: `<ol><li>${haveError[1]}</li></ol>`
                        });
                        return false;
                    }

                    if (priceTemp.toFixed(2) !== netValue.toFixed(2)) { // os valores das parcelas não corresponde ao valor líquido
                        if ($('#automatic_parcel_distribution').is(':checked')) {
                            recalculeParcels();
                        } else {
                            if (currentIndex < getIndexStep(4)) {
                                setErrorStepWrong(getIndexStep(4));
                            }

                            Swal.fire({
                                icon: 'warning',
                                title: 'Atenção',
                                html: '<ol><li>A soma das parcelas deve corresponder ao valor líquido.</li></ol>'
                            });
                            return false;
                        }
                    }
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
            let time0,time1,time2,time3,time4,time5,time6,time7,time8 = 0;

            if (priorIndex === getIndexStep(0)) { // tipo de cobrança

                const rental_p = 'formRental-p-4';
                const rental_t = 'formRental-t-4';
                const numberIndex = 5;
                const payment  = $(`#${rental_p} #payment`);

                typeLocation === 0 ? payment.removeClass('payment-no').addClass('payment-yes') : payment.removeClass('payment-yes').addClass('payment-no');

                if (typeLocation === 0) {
                    $(`#${rental_p} h6.title-step`).text('Valores e Pagamento');
                    $(`#${rental_t}`).html(`<span class="number">${numberIndex}.</span> Valores e Pagamento`);
                } else {
                    $(`#${rental_p} h6.title-step`).text('Resumo Equipamento');
                    $(`#${rental_t}`).html(`<span class="number">${numberIndex}.</span> Resumo Equipamento`);
                }
            }
            let date = new Date();
            time0 = date.getTime();

            if (priorIndex <= getIndexStep(3) && currentIndex >= getIndexStep(4)) { // equipamento
                let pricesAndStocks;
                let dataEquipments = [];
                let dataEquipmentsPayCheck = [];
                let newPricesUpdate = [];
                let newPricesUpdateNames = [];
                let idEquipments = [];
                let priceEquipment = 0;
                let idEquipment,stockEquipment,nameEquipment;
                date = new Date();
                time1 = date.getTime();
                await $('#equipments-selected div.card').each(async function() {
                    idEquipment        = parseInt($('.card-header', this).attr('id-equipment'));
                    stockEquipment     = parseInt($('[name^="stock_equipment_"]', this).val());
                    nameEquipment      = $('.card-header a:eq(0)', this).text();
                    dataEquipments.push([idEquipment, stockEquipment, nameEquipment]);
                    idEquipments.push(idEquipment);
                });
                date = new Date();
                time2 = date.getTime();

                $('.list-equipments-payment-load').show();
                $('.list-equipments-payment').hide();
                $('#gross_value').html('<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;Calculando');
                if ($('#calculate_net_amount_automatic').is(':checked')) {
                    $('#net_value').val('Calculando...');
                }

                date = new Date();
                time3 = date.getTime();
                pricesAndStocks = await getPriceStockEquipments(idEquipments);
                date = new Date();
                time4 = date.getTime();
                if (pricesAndStocks) {
                    await Promise.all(dataEquipments.map(async equipment => {
                        dataEquipmentsPayCheck.push(equipment[0]);
                        if (equipment[1] > pricesAndStocks[equipment[0]].stock && !budget) {
                            $(`#collapseEquipment-${equipment[0]}`).find('input[name^="stock_equipment_"]').attr('max-stock', pricesAndStocks[equipment[0]].stock).val(pricesAndStocks[equipment[0]].stock);
                            $(`#collapseEquipment-${equipment[0]}`).find('.stock_available').text('Disponível: ' + pricesAndStocks[equipment[0]].stock);
                            arrErrors.push(`O equipamento ( <strong>${equipment[2]}</strong> ) não tem estoque suficiente. <strong>Disponível: ${pricesAndStocks[equipment[0]].stock} un</strong>`);
                        }

                        if (!$(`.list-equipments-payment li[id-equipment="${equipment[0]}"]`).length) {
                            await createEquipmentPayment(equipment[0], pricesAndStocks[equipment[0]]);
                        } else {
                            priceEquipment = pricesAndStocks[equipment[0]].price;

                            date = new Date();
                            time5 = date.getTime();
                            $(`#price-un-equipment-${equipment[0]}`).val(numberToReal(priceEquipment));
                            $(`.list-equipments-payment li[id-equipment="${equipment[0]}"] .stock-equipment-payment strong`).text(equipment[1] + 'un');

                            if (numberToReal(priceEquipment * equipment[1]) !== $(`#price-total-equipment-${equipment[0]}`).val()) {
                                newPricesUpdate.push({
                                    el: $(`#price-total-equipment-${equipment[0]}`),
                                    price: numberToReal(priceEquipment * equipment[1])
                                });
                                newPricesUpdateNames.push(equipment[2] + ' | R$' + $(`#price-total-equipment-${equipment[0]}`).val() + ' <i class="fas fa-long-arrow-alt-right"></i> R$' + numberToReal(priceEquipment * equipment[1]));
                            }
                            date = new Date();
                            time6 = date.getTime();
                        }
                    }));
                }
                date = new Date();
                time7 = date.getTime();

                await $('.list-equipments-payment li').each(async function() {
                    idEquipment = parseInt($(this).attr('id-equipment'));
                    if (!dataEquipmentsPayCheck.includes(idEquipment)) {
                        $(`.list-equipments-payment li[id-equipment="${idEquipment}"]`).remove();
                    }
                });
                date = new Date();
                time8 = date.getTime();

                /*console.log({
                    0: time1-time0,
                    1: time2-time1,
                    2: time3-time2,
                    'check-equip': time4-time3,
                    3: time5-time4,
                    4: time6-time5,
                    5: time7-time6,
                    6: time8-time7,
                })*/

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
                    if (!$('[name="rental_id"]').length && typeLocation == 0 && newPricesUpdate.length) {
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
                    } else if (!$('[name="rental_id"]').length && typeLocation == 1 && newPricesUpdate.length) {

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
            $("#observation").val($("#observationDiv .ql-editor").html());

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('#formRental').attr('action'),
                data: $('#formRental').serialize(),
                success: response => {
                    if (response.success) {
                        if (
                            response.hasOwnProperty("show_alert_update_equipment_or_payment") &&
                            response.show_alert_update_equipment_or_payment &&
                            (
                                (response.show_alert_update_equipment_or_payment.hasOwnProperty("equipment") && response.show_alert_update_equipment_or_payment.equipment) ||
                                (response.show_alert_update_equipment_or_payment.hasOwnProperty("payment") && response.show_alert_update_equipment_or_payment.payment)
                            )
                        ) {
                            const update_equipment  = response.show_alert_update_equipment_or_payment.equipment;
                            const update_payment    = response.show_alert_update_equipment_or_payment.payment;
                            const title_alert = update_equipment && update_payment ? "Alteração de equipamento e pagamento" :
                                (update_equipment ? "Alteração de equipamento" : "Alteração de pagamento");
                            const description_alert = update_equipment && update_payment ? "equipamentos e pagamentos, caso exista parcela paga ou equipamento entregue ou retirado" :
                                (update_equipment ? "equipamentos, caso exista equipamento entregue ou retirado" : "pagamentos, caso exista parcela paga");

                            Swal.fire({
                                title: title_alert,
                                html: `Existem alterações de ${description_alert}, deve ser realizado a ação novamente. <br>Deseja atualizar?`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#19d895',
                                cancelButtonColor: '#bbb',
                                confirmButtonText: 'Sim, atualizar',
                                cancelButtonText: 'Não atualizar',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('[name="confirm_update_equipment_or_payment"]').val(1);
                                    $('#formRental a[href="#finish"]').trigger('click')
                                }
                            })
                        } else {
                            $('#createRental').modal();
                            $('#createRental h3.code_rental strong').text(response.code);
                            $('#createRental a.rental_print').attr('href', response.urlPrint);
                        }

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
    $('[name="date_withdrawal"], [name="date_delivery"]').inputmask();
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
    loadResidues(0, '.container-residues select[name="residues[]"]');
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

    if (!$('#parcels .parcel').length && !$('[name="rental_id"]').length) {
        $('#add_parcel').trigger('click');
    }
});
