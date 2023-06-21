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

                    // existe parcelamento
                    if ($('#is_parceled').is(':checked')) {
                        let daysTemp;
                        let priceTemp = 0;
                        let haveError = [false];

                        $('#parcels .parcel').each(function () {
                            if (daysTemp === undefined) {
                                daysTemp = parseInt($('[name="due_day[]"]', this).val());
                            } else if (daysTemp >= parseInt($('[name="due_day[]"]', this).val())) {
                                haveError = [true, 'A ordem dos vencimentos devem ser informados em ordem crescente.'];
                            } else {
                                daysTemp = parseInt($('[name="due_day[]"]', this).val());
                            }

                            if (realToNumber($('[name="value_parcel[]"]', this).val()) <= 0) {
                                haveError = [true, 'Não podem existir vencimentos com valor menor ou igual a zero.'];
                            }

                            priceTemp += realToNumber($('[name="value_parcel[]"]', this).val());
                        });

                        if (haveError[0]) { // ecnontrou erro nas datas de vencimento

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

var searchEquipmentOld = '';
var budget = $('#budget').val() ? true : false;

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
});

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
        setTimeout(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
            });
        }, 150);
    },
    submitHandler: function(form) {
        $('#formRental [type="submit"]').attr('disabled', true);
        form.submit();
    }
});

$('#searchEquipment').on('blur keyup', function (e){

    if(e.keyCode !== 13 && e.type === 'keyup') return false;

    const searchEquipment = $(this).val();
    let equipmentInUse = [];

    if (searchEquipment === searchEquipmentOld) return false;

    $('#equipments-selected .card-header').each(function(){
        equipmentInUse.push(parseInt($(this).attr('id-equipment')));
    });

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

$('table.list-equipment').on('click', '.equipment', function(){
    const idEquipment = $(this).attr('id-equipment');

    setEquipmentRental(idEquipment);
});

$(document).on('click', '.remove-equipment i', function (){
    $(this).closest('.card').slideUp(500);
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
    const idEquipment = parseInt($(this).attr('id-equipment'));
    $(`#collapseEquipment-${idEquipment}`).collapse('hide');
});

$(document).on('click', '.use_date_diff_equip', function (){
    const elEquip = $(this).closest('.card-body');
    let date_delivery, date_withdrawal;

    elEquip.find('input[name^="date_delivery_equipment_"]').attr('disabled', !$(this).is(':checked'));
    elEquip.find('.not_use_date_withdrawal').attr('disabled', !$(this).is(':checked'));

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

        elEquip.find('input[name^="date_delivery_equipment_"]').val(date_delivery);
        elEquip.find('input[name^="date_withdrawal_equipment_"]').val(date_withdrawal);

        if ($('#not_use_date_withdrawal').is(':checked')) {
            elEquip.find('.not_use_date_withdrawal').prop('checked', true);
        } else {
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

$(document).on('click', '.not_use_date_withdrawal', function (){
    const elEquip = $(this).closest('.col-md-6');

    elEquip.find('input[name^="date_withdrawal_equipment_"]').attr('disabled', $(this).is(':checked'));
    elEquip.find('.flatpickr a').attr('disabled', $(this).is(':checked'));

    elEquip.find('input[name^="date_withdrawal_equipment_"]').val('');

    if (!$(this).is(':checked'))
        elEquip.find('input[name^="date_withdrawal_equipment_"]').val(getTodayDateBr());

    checkLabelAnimate();
});

$('#extra_value, #discount_value, #net_value').on('keyup', () => {
    reloadTotalRental();
}).on('blur', function(){
    if ($(this).val() === '') $(this).val('0,00');
});

$('#net_value').on('keyup', function() {

    let netAmount   = realToNumber($(this).val());
    let grossAmount = realToNumber($('#gross_value').text());
    let discount    = $('#discount_value');
    let extra       = $('#extra_value');

    discount.val('0,00');
    extra.val('0,00');

    if (netAmount > grossAmount)
        extra.val(numberToReal(netAmount - grossAmount));
    else if (netAmount < grossAmount)
        discount.val(numberToReal(grossAmount - netAmount));

}).on('blur', function(){
    if ($(this).val() === '') $(this).val('0,00');
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
            } else
                descPeriod += 'Equipamento não contém valor por período definido.';

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

$('#is_parceled').change(function (){
    const check = $(this).is(':checked');

    if (check) {
        $('#add_parcel, #del_parcel, .automatic_parcel_distribution_parent').slideDown(500);
        $('#parcels').show().append(
            createParcel(0)
        ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({thousands: '.', decimal: ',', allowZero: true});

        recalculeParcels();
    }
    else {
        $('#add_parcel, #del_parcel, #parcels, .automatic_parcel_distribution_parent').slideUp(500);
        $('#automatic_parcel_distribution').prop('checked', true);
        setTimeout(() => { $('#parcels .form-group').remove() }, 550)
    }
})

$('#parcels').on('keyup change', '[name="due_day[]"]', function(){
    let days = parseInt($(this).val());
    const el = $(this).closest('.form-group');

    el.find('[name="due_date[]"]').val(sumDaysDateNow(days));
});

$('#parcels').on('blur', '[name="due_date[]"]', function(){
    const dataVctoInput = $(this).val();
    if (dataVctoInput === '') return false;

    const diasVcto = calculateDays(getTodayDateEn(false), dataVctoInput);
    const el = $(this).closest('.form-group');

    el.find('[name="due_day[]"]').val(diasVcto);
});

$('#add_parcel').click(function(){
    const parcels = $('#parcels .parcel').length;

    if (parcels == 12) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            html: '<ol><li>É permitido adicionar até 12 vencimentos.</li></ol>'
        });
        return false;
    }

    $('#parcels').show().append(
        createParcel(parcels)
    ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({thousands: '.', decimal: ',', allowZero: true});

    $('#del_parcel').attr('disabled', false);

    recalculeParcels();
});

$('#del_parcel').click(function(){
    const dues = $('#parcels .form-group').length - 1;

    $(`#parcels .form-group:eq(${dues})`).remove();

    if (dues === 1)
        $('#del_parcel').attr('disabled', true);

    recalculeParcels();

});

$('#automatic_parcel_distribution').change(function(){
    const check = $(this).is(':checked');

    if (check) {
        $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', true);
        recalculeParcels();
    } else
        $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', false);

});

$(document).on('change', '[name^="vehicle_"]', function (){
    const vehicle_id = $(this).val();
    if (vehicle_id == '0') return false;

    const el = $(this).closest('.card-body');
    const driver_actual = parseInt(el.find('[name^="driver_"]').val());

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        data: { vehicle_id },
        url: $('#routeGetVehicle').val(),
        async: true,
        success: response => {
            if (response.driver_id && el.find('[name^="driver_"]').val() === '0') {
                el.find('[name^="driver_"]').val(response.driver_id)
            } else if(response.driver_id && driver_actual !== parseInt(response.driver_id)) {
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
                        el.find('[name^="driver_"]').val(response.driver_id)
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

const setErrorStepWrong = step => {

    setTimeout(() => {
        $('#formRental .steps ul li').removeClass('error');
        for (let i = 0; i < step; i++)
            $(`#formRental .steps ul li:eq(${i})`).removeClass('current').addClass('done');

        $(`#formRental .steps ul li:eq(${step})`).addClass('error').find('a').trigger('click');
    }, 150);
}

const recalculeParcels = () => {
    if ($('#automatic_parcel_distribution').is(':checked')) {
        const parcels = $('#parcels .form-group').length;
        const netValue = realToNumber($('#net_value').val());

        let valueSumParcel = parseFloat(0.00);
        let valueParcel = netValue / parcels;

        for (let count = 0; count < parcels; count++) {

            if((count + 1) === parcels) valueParcel = netValue - valueSumParcel;

            valueSumParcel += parseFloat((netValue / parcels).toFixed(2));
            $(`#parcels .form-group [name="value_parcel[]"]:eq(${count})`).val(numberToReal(valueParcel));
        }
    }
}

const createParcel = (due, due_day = null, due_date = null, due_value = null) => {

    due_day = due_day === null ? calculateDays(sumMonthsDateNow(0), sumMonthsDateNow(due)) : due_day;
    due_date = due_date === null ? sumMonthsDateNow(due) : due_date;
    due_value = due_value === null ? '0,00' : numberToReal(due_value);

    const disabledValue = $('#automatic_parcel_distribution').is(':checked') ? 'disabled' : '';
    return `<div class="form-group mt-1 parcel display-none">
            <div class="d-flex align-items-center justify-content-between payment-item">
                <div class="input-group col-md-12 no-padding">
                    <div class="input-group-prepend stock-Equipment-payment col-md-3 no-padding">
                        <span class="input-group-text col-md-12 no-border-radius "><strong>${(due+1)}º Vencimento</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 text-center" name="due_day[]" value="${due_day}">
                    <input type="date" class="form-control col-md-4 text-center" name="due_date[]" value="${due_date}">
                    <div class="input-group-prepend col-md-1 no-padding">
                        <span class="input-group-text pl-3 pr-3 col-md-12"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 no-border-radius text-center" name="value_parcel[]" value="${due_value}" ${disabledValue}>
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
    /*if (budget) {
        checkLabelAnimate();
        return false;
    }*/

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
    let stockReal = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetStockEquipment').val(),
        data: { idEquipment },
        async: true,
        success: response => {
            return response;
        }
    });

    return stockReal;
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

    let price = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetPriceEquipment').val(),
        data: { idEquipment, diffDays },
        async: true,
        success: response => {
            return response;
        }, error: e => { console.log(e) }
    });

    return price;
}

const getEquipment = async equipment => {

    let data = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetEquipment').val(),
        data: { idEquipment: equipment },
        async: true,
        success: response => {
            return response;
        }, error: e => { console.log(e) }
    });

    return data.success ? data.data : false;
}

const createEquipmentPayment = async (equipment, priceStock = null, unity_price = null, total_price = null, quantity = null) => {

    let dataEquipment        = await getEquipment(equipment);
    let stockEquipment       = quantity === null ? $(`#collapseEquipment-${equipment} input[name^="stock_equipment_"]`).val() : quantity;
    const priceEquipment     = unity_price === null ? (priceStock === null ? await getPriceEquipment(equipment) : priceStock.price) : unity_price;
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
                <div class="input-group col-md-6 no-padding payment-hidden-invert-stock">
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

    setTimeout(() => {
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
    }, 250);
}

const reloadTotalRental = () => {

    let grossValue      = 0;
    let priceEquipment = 0;
    let discount        = realToNumber($('#discount_value').val());
    let extra           = realToNumber($('#extra_value').val());
    let netAmount       = realToNumber($('#net_value').val());

    discount    = isNaN(discount) ? 0 : discount;
    extra       = isNaN(extra) ? 0 : extra;

    $('.list-equipments-payment li').each(function() {
        priceEquipment = realToNumber($('.price-total-equipment', this).val());
        grossValue += isNaN(priceEquipment) ? 0 : priceEquipment;
    });

    $('#gross_value').text(numberToReal(grossValue));

    if ($('#calculate_net_amount_automatic').is(':checked'))
        $('#net_value').val(numberToReal(grossValue - discount + extra));
    else {
        if (grossValue > netAmount) {
            $('#discount_value').val(numberToReal(grossValue - netAmount));
            $('#extra_value').val('0,00');
        } else {
            $('#extra_value').val(numberToReal(netAmount - grossValue));
            $('#discount_value').val('0,00');
        }
    }

    if ($('#automatic_parcel_distribution').is(':checked'))
        recalculeParcels();

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

    let priceStock = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetPriceStockEquipments').val(),
        data: { arrEquipments, arrDiffDays },
        async: true,
        success: response => {
            return response;
        }, error: e => { console.log(e) }
    });

    return priceStock;
}

const setEquipmentRental = (
    idEquipment,
    quantity = null,
    vehicle_suggestion = null,
    driver_suggestion = null,
    use_date_diff_equip = null,
    expected_delivery_date = null,
    expected_withdrawal_date = null,
    not_use_date_withdrawal = null
) => {
    $(`.equipment[id-equipment="${idEquipment}"]`).empty().toggleClass('equipment load-equipment').append('<td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando ...</td>')

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetEquipment').val(),
        data: { idEquipment, validStock: !budget },
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

            let date_delivery = $('input[name="date_delivery"]').val();
            let date_withdrawal = $('input[name="date_withdrawal"]').val();
            const disabledVehicle = permissions.vehicle ? '' : 'disabled';
            const disabledDriver = permissions.driver ? '' : 'disabled';

            let prefix_equipment_name = '';
            let field_old_equipment_id = '';
            let equipment_quantity = 1;
            let equipment_vehicle = 0;
            let equipment_driver = 0;
            let equipment_use_date_diff_equip = '';
            let equipment_use_date_diff_equip_date = 'disabled';
            let equipment_content_use_date_diff_equip = 'display-none';
            let equipment_not_use_date_withdrawal = '';
            let equipment_disabled_not_use_date_withdrawal = 'disabled';
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

            let regEquipment = `
            <div class="card">
                <div class="card-header" role="tab" id="headingEquipment-${response.id}" id-equipment="${response.id}">
                    <h5 class="mb-0 d-flex align-items-center">
                        <a class="collapsed pull-left w-100" data-toggle="collapse" href="#collapseEquipment-${response.id}" aria-expanded="false" aria-controls="collapseEquipment-${response.id}" is-cacamba="${response.cacamba}">
                            ${prefix_equipment_name}${response.name}
                        </a>
                        <a class="remove-equipment pull-right"><i class="fa fa-trash"></i></a>
                    </h5>
                </div>
                <div id="collapseEquipment-${response.id}" class="collapse" role="tabpanel" aria-labelledby="headingEquipment-${response.id}" data-parent="#equipments-selected" id-equipment="${response.id}">
                    <input type="hidden" value="${response.id}" name="equipment_id[]">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8" style="margin-top: -20px">
                                <div class="form-group">
                                    <label>Referência</label>
                                    <input type="text" class="form-control" value="${response.reference}" name="reference_equipment_${response.id}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group flatpickr label-animate stock-group d-flex">
                                    <label class="label-date-btns">Quantidade</label>
                                    <input type="tel" name="stock_equipment_${response.id}" class="form-control col-md-9 flatpickr-input bbr-r-0 btr-r-0" value="${equipment_quantity}" max-stock="${response.stock}">
                                    <div class="input-button-calendar col-md-3 no-padding">
                                        <button class="input-button pull-right btn-primary w-100 btn-view-price-period-equipment" data-toggle="tootip" title="Visualizar valor por período" id-equipment="${response.id}">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-danger font-weight-bold stock_available pull-left">Disponível: ${response.stock}</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 label-animate">
                                <label>Veículo</label>
                                <div class="input-group label-animate">
                                    <select class="form-control" name="vehicle_${response.id}" disabled>
                                        <option>Carregando ...</option>
                                    </select>
                                    <div class="input-group-addon input-group-append">
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newVehicleModal" title="Novo Veículo" ${disabledVehicle}><i class="fas fa-plus-circle"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 label-animate">
                                <label>Motorista</label>
                                <div class="input-group label-animate">
                                    <select class="form-control" name="driver_${response.id}" disabled>
                                        <option>Carregando ...</option>
                                    </select>
                                    <div class="input-group-addon input-group-append">
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newDriverModal" title="Novo Motorista" ${disabledDriver}><i class="fas fa-plus-circle"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="switch pt-3">
                                    <input type="checkbox" class="check-style check-xs use_date_diff_equip" name="use_date_diff_equip_${response.id}" id="use_date_diff_equip_${response.id}" ${equipment_use_date_diff_equip}>
                                    <label for="use_date_diff_equip_${response.id}" class="check-style check-xs"></label> Usar datas de entrega e/ou retirada diferentes para esse equipamento.
                                </div>
                            </div>
                        </div>
                        <div class="row ${equipment_content_use_date_diff_equip} use_date_diff_equip_show mt-2">
                            <div class="col-md-6">
                                <div class="form-group flatpickr d-flex">
                                    <label class="label-date-btns">Data Prevista de Entrega</label>
                                    <input type="text" name="date_delivery_equipment_${response.id}" class="form-control col-md-9" value="${date_delivery}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input ${equipment_use_date_diff_equip_date}>
                                    <div class="input-button-calendar col-md-3 no-padding calendar_equipment">
                                        <a class="input-button pull-left btn-primary" title="toggle" data-toggle ${equipment_use_date_diff_equip_date}>
                                            <i class="fa fa-calendar text-white"></i>
                                        </a>
                                        <a class="input-button pull-right btn-primary" title="clear" data-clear ${equipment_use_date_diff_equip_date}>
                                            <i class="fa fa-times text-white"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group flatpickr d-flex">
                                    <label class="label-date-btns">Data Prevista de Retirada</label>
                                    <input type="text" name="date_withdrawal_equipment_${response.id}" class="form-control col-md-9" value="${date_withdrawal}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input ${equipment_disabled_not_use_date_withdrawal_equip}>
                                    <div class="input-button-calendar col-md-3 no-padding calendar_equipment">
                                        <a class="input-button pull-left btn-primary" title="toggle" data-toggle ${equipment_disabled_not_use_date_withdrawal_equip}>
                                            <i class="fa fa-calendar text-white"></i>
                                        </a>
                                        <a class="input-button pull-right btn-primary" title="clear" data-clear ${equipment_disabled_not_use_date_withdrawal_equip}>
                                            <i class="fa fa-times text-white"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="switch pt-1">
                                        <input type="checkbox" class="check-style check-xs not_use_date_withdrawal" name="not_use_date_withdrawal_equip_${response.id}" id="not_use_date_withdrawal_${response.id}" ${equipment_not_use_date_withdrawal} ${equipment_disabled_not_use_date_withdrawal}>
                                        <label for="not_use_date_withdrawal_${response.id}" class="check-style check-xs"></label> Não informar data de retirada
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12 mt-2">
                                <button type="button" class="btn btn-primary pull-right hideEquipment" id-equipment="${response.id}"><i class="fa fa-angle-up"></i> Ocultar</button>
                            </div>
                        </div>
                        ${field_old_equipment_id}
                    </div>
                </div>
            </div>`;
            $('#equipments-selected').append(regEquipment);
            $(`.load-equipment[id-equipment="${idEquipment}"]`).hide(300);
            showSeparatorEquipmentSelected();
            $('#cleanSearchEquipment').trigger('click')
            setTimeout(() => {
                $(`.load-equipment[id-equipment="${idEquipment}"]`).remove();

                if (!$(`.list-equipment tbody tr`).length) {
                    equipmentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                }
                checkLabelAnimate();

                // é uam edição, não adição.
                if (quantity === null) {
                    $(`#collapseEquipment-${idEquipment}`).collapse('show');
                }

                $(`#collapseEquipment-${idEquipment} input[name^="stock_equipment_"]`).mask('0#');
                $(`#collapseEquipment-${idEquipment} input[name^="date_withdrawal_equipment_"]`).inputmask();
                $(`#collapseEquipment-${idEquipment} input[name^="date_delivery_equipment_"]`).inputmask();
                $(`#collapseEquipment-${idEquipment} .flatpickr:not(.stock-group)`).flatpickr({
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
                    $(`#collapseEquipment-${idEquipment} input[name^="date_withdrawal_equipment_"]`).val('');
                    $(`#collapseEquipment-${idEquipment} .not_use_date_withdrawal`).prop('checked', true);
                }
                $(`#collapseEquipment-${idEquipment} .btn-view-price-period-equipment`).tooltip();

                if (response.cacamba) {
                    $('.container-residues').slideDown('slow');
                }

                loadVehicles(equipment_vehicle,`#collapseEquipment-${idEquipment} select[name^="vehicle_"]`);
                loadDrivers(equipment_driver, `#collapseEquipment-${idEquipment} select[name^="driver_"]`);

                $(`#not_use_date_withdrawal_${idEquipment}`).trigger('change');

            }, 350);
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

const getEquipmentsRental = (rental_id, callback) => {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: `${$('[name="base_url"]').val()}/ajax/locacao/equipamentos/${rental_id}`,
        async: true,
        success: response => {
            callback(response);
        }, error: e => { console.log(e) }
    });
}

const getPaymentsRental = (rental_id, callback) => {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: `${$('[name="base_url"]').val()}/ajax/locacao/pagamentos/${rental_id}`,
        async: true,
        success: response => {
            callback(response);
        }, error: e => { console.log(e) }
    });
}
