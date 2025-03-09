    (function ($) {
    'use strict';
    const form = $("#formRental");
    const budget = !!$('#budget').val();
    form.steps({
        labels: {
            current: "current step:",
            finish: "Finalizar <i class='fa fa-save'></i>",
            next: "Próximo <i class='fa fa-arrow-right'></i>",
            previous: "<i class='fa fa-arrow-left'></i> Antrior",
            loading: "Carregando ..."
        },
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

            if (newIndex === 1) {
                setTimeout(() => {
                    $('[name="client"]').select2();
                    $('[name="state"]').select2('destroy').select2();
                    $('[name="city"]').select2('destroy').select2();
                }, 250);
            }

            if (currentIndex === 0) {// tipo locacão.
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
            if (currentIndex <= 1 && newIndex > 1) { // cliente e endereo
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

                    if (currentIndex !== 1) {
                        setErrorStepWrong(1);
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });

                    return false;
                }
            }
            if (currentIndex <= 2 && newIndex > 2) { // datas
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
                    if (currentIndex < 2) {
                        setErrorStepWrong(2);
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
            if (currentIndex <= 3 && newIndex > 3) { // equipamento
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }

                if ($('#equipments-selected div').length === 0) {
                    if (currentIndex < 3) {
                        setErrorStepWrong(3);
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
                    dateWithdrawalTime,
                    use_date_diff_equip;

                $('#equipments-selected div.card').each(function() {
                    idEquipment        = parseInt($('.card-header', this).attr('id-equipment'));
                    stockEquipment     = parseInt($('[name^="stock_equipment_"]', this).val());
                    nameEquipment      = $('.card-header a:eq(0)', this).text();
                    stockMax            = parseInt($('[name^="stock_equipment_"]', this).attr('max-stock'));

                    if (isNaN(stockEquipment) || stockEquipment === 0) {
                        arrErrors.push(`O equipamento<br><strong>${nameEquipment}</strong><br>deve ser informado uma quantidade.`);
                    } else if (stockEquipment > stockMax && !budget) {
                        arrErrors.push(`O equipamento<br><strong>${nameEquipment}</strong><br>não tem estoque suficiente. <strong>Disponível: ${stockMax} un</strong>`);
                    }

                    notUseDateWithdrawal = $('.not_use_date_withdrawal', this).is(':checked');
                    use_date_diff_equip = $('.use_date_diff_equip', this).is(':checked');

                    dateDeliveryTime = new Date(transformDateForEn($('input[name^="date_delivery_equipment_"]', this).val())).getTime();
                    dateWithdrawalTime = new Date(transformDateForEn($('input[name^="date_withdrawal_equipment_"]', this).val())).getTime();

                    if (use_date_diff_equip && (dateDeliveryTime === 0 || (!notUseDateWithdrawal && dateWithdrawalTime === 0))) {
                        arrErrors.push(`A data prevista de entrega e data prevista de retirada do equipamento<br><strong>${nameEquipment}</strong><br>deve ser informada corretamente.`);
                    } else if (use_date_diff_equip && !notUseDateWithdrawal && dateDeliveryTime >= dateWithdrawalTime) {
                        arrErrors.push(`A data prevista de entrega do equipamento<br><strong>${nameEquipment}</strong><br>não pode ser maior ou igual que a data prevista de retirada.`);
                    }
                });

                if (arrErrors.length) {

                    if (currentIndex < 3) {
                        setErrorStepWrong(3);
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
            if (currentIndex <= 4 && newIndex > 4) { // pagamento

                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }

                const netValue = realToNumber($('#net_value').val());

                if (netValue < 0) {

                    if (currentIndex < 4) {
                        setErrorStepWrong(4);
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Valor líquido da locação não pode ser negativo.</li></ol>'
                    });
                    return false;
                }

                if (typeLocation === 0) {

                    const grossValue    = realToNumber($('#gross_value').text());
                    const netValue      = realToNumber($('#net_value').val());
                    const extraValue    = realToNumber($('#extra_value').val());
                    const discountValue = realToNumber($('#discount_value').val());

                    if (netValue == 0) {

                        if (currentIndex < 4) {
                            setErrorStepWrong(4);
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
                        if (currentIndex < 4) {
                            setErrorStepWrong(4);
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

                        if (currentIndex < 4) {
                            setErrorStepWrong(4);
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
                            if (currentIndex < 4) {
                                setErrorStepWrong(4);
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
            if (currentIndex === 0) {
                $('#formRental.wizard .actions a[href="#previous"]').attr('href', '#cancel').html('<i class="fa fa-times"></i> Cancelar').addClass('btn-danger').closest('li').removeClass('disabled');
            } else {
                $('#formRental.wizard .actions a[href="#cancel"]').attr('href', '#previous').html('<i class="fa fa-arrow-left"></i> Anterior').removeClass('btn-danger');
            }

            changeStepPosUnset();
            let arrErrors = [];
            let typeLocation = parseInt($('input[name="type_rental"]:checked').val());

            if (priorIndex === 0) { // tipo de cobrança

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

            if (priorIndex <= 3 && currentIndex >= 4) { // equipamento

                const result_validation = await updateDataEquipmentToPayment();

                arrErrors = result_validation.arrErrors;
                const newPricesUpdate = result_validation.newPricesUpdate;
                const newPricesUpdateNames = result_validation.newPricesUpdateNames;

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
                    if (typeLocation === 0 && newPricesUpdate.length) {
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
                    } else if (typeLocation === 1 && newPricesUpdate.length) {

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
                            $('#createRental').modal('show');
                            $('#createRental h3.code_rental strong').text(response.code);
                            $('#createRental a.rental_print').attr('href', response.urlPrint);

                            if (response.payment_today) {
                                $('#createRental .content-payment-today').show();
                                $('#createRental [name="due_date"]').val(transformDateForBr(response.payment_today.due_date));
                                $('#createRental [name="due_value"]').val(numberToReal(response.payment_today.due_value));
                                $('#createRental [name="payment_id"]').val(response.payment_today.id);
                                checkLabelAnimate();
                                getOptionsForm('form-of-payment', $('#createRental [name="form_payment"]'));
                            }
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
        },

        //enableCancelButton: true,
        onCanceled: function (event) {
            Swal.fire({
                title: 'Voltar para a listagem',
                html: 'Deseja realmente sair da página? Caso tenha alterado algum campo, será perdido.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#19d895',
                cancelButtonColor: '#bbb',
                confirmButtonText: 'Sim, desejo sair',
                cancelButtonText: 'Não, continuar na página',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = $('#back_to_list').val();
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

    let residue_ids = [0];
    if ($('[name="residues"]').val() && $('[name="residues"]').val().split(',').length) {
        residue_ids = $('[name="residues"]').val().split(',');
    }

    loadResidues(residue_ids, '.container-residues select[name="residues[]"]');
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

    $('#do_payment_today').on('change', function(){
        if ($(this).is(':checked')) {
            $('.display-payment-today').css('display', 'flex');
        } else {
            $('.display-payment-today').css('display', 'none');
        }
    });

    $('#confirm_payment_today').on('click', function (){
        const payment_id    = parseInt($('#createRental [name="payment_id"]').val());
        const form_payment  = $('#createRental [name="form_payment"]').val();
        const date_payment  = $('#createRental [name="date_payment"]').val();
        const btn           = $(this);

        if (isNaN(payment_id) || payment_id === 0 || form_payment === '' || date_payment === '') {
            Toast.fire({
                icon: 'warning',
                title: 'Preencha todos os campos para continuar'
            });
            return false;
        }

        btn.attr('disabled', true);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $('[name="base_url"]').val() + `/ajax/contas-a-receber/confirmar-pagamento`,
            data: {
                payment_id,
                form_payment,
                date_payment
            },
            dataType: 'json',
            success: response => {
                Toast.fire({
                    icon: response.success ? 'success' : 'warning',
                    title: response.message
                });

                if (response.success) {
                    $('#createRental .content-payment-today, #createRental .display-payment-today').remove();
                }
            }, error: e => {
                let arrErrors = [];

                $.each(e.responseJSON.errors, function( index, value ) {
                    arrErrors.push(value);
                });

                if (!arrErrors.length && e.responseJSON.message !== undefined) {
                    arrErrors.push('Você não tem permissão para fazer essa operação!');
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                });
            }
        }).always(() => {
            btn.attr('disabled', false);
        });
    });
});

const updateDataEquipmentToPayment = async () => {
    let arrErrors = [];
    let pricesAndStocks;
    let dataEquipments = [];
    let dataEquipmentsPayCheck = [];
    let newPricesUpdate = [];
    let newPricesUpdateNames = [];
    let idEquipments = [];
    let priceEquipment = 0;
    let priceEquipmentTotal = 0;
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

                priceEquipmentTotal = getTotalPriceEquipment(priceEquipment, equipment[0], equipment[1]);

                if (numberToReal(priceEquipmentTotal) !== $(`#price-total-equipment-${equipment[0]}`).val()) {
                    newPricesUpdate.push({
                        el: $(`#price-total-equipment-${equipment[0]}`),
                        price: numberToReal(priceEquipmentTotal)
                    });
                    newPricesUpdateNames.push(equipment[2] + ' | R$' + $(`#price-total-equipment-${equipment[0]}`).val() + ' <i class="fas fa-long-arrow-alt-right"></i> R$' + numberToReal(priceEquipmentTotal));
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

    return {
        "newPricesUpdate": newPricesUpdate,
        "newPricesUpdateNames": newPricesUpdateNames,
        "arrErrors": arrErrors
    }
}
