(function ($) {
    'use strict';
    var form = $("#formCreateRental");
    form.steps({
        headerTag: "h3",
        bodyTag: "div.stepRental",
        transitionEffect: "slideLeft",
        stepsOrientation: "vertical",
        onStepChanging: function (event, currentIndex, newIndex)
        {
            let debug = true;
            let arrErrors = [];
            let notUseDateWithdrawal = $('#not_use_date_withdrawal').is(':checked');

            if (currentIndex === 0) {// tipo locacao
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

                changeStepPosAbsolute();
                return true;
            }
            else if (currentIndex === 1 && newIndex > 1) { // cliente e endereo
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }
                if ($('select[name="client"]').val() === '0') arrErrors.push('Selecione um cliente.');

                if (arrErrors.length === 0) {
                    if ($('input[name="address"]').val() === '') arrErrors.push('Informe um endereço.');
                    if ($('input[name="number"]').val() === '') arrErrors.push('Informe um número para o endereço.');
                    if ($('input[name="neigh"]').val() === '') arrErrors.push('Informe um bairro.');
                    if ($('input[name="city"]').val() === '') arrErrors.push('Informe uma cidade.');
                    if ($('input[name="state"]').val() === '') arrErrors.push('Informe um estado.');
                    if ($('input[name="lat"]').val() === '' || $('input[name="lng"]').val() === '') arrErrors.push('Confirme o endereço no mapa.');
                }

                if (arrErrors.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                }

                if (arrErrors.length === 0) {
                    changeStepPosAbsolute();
                    return true;
                }
                return false;
            }
            else if (currentIndex === 2 && newIndex > 2) { // datas
                if (debug) {
                    changeStepPosAbsolute();
                    fixEquipmentDates();
                    return true;
                }
                let dateDelivery = $('input[name="date_delivery"]').val();
                let dateWithdrawal = $('input[name="date_withdrawal"]').val();

                if (dateDelivery.length < 16) arrErrors.push('Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy.');
                if (!notUseDateWithdrawal && dateWithdrawal.length < 16) arrErrors.push('Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy.');

                if (arrErrors.length === 0) {
                    let dateDeliveryTime = new Date(transformDateForEn(dateDelivery)).getTime();
                    let dateWithdrawalTime = new Date(transformDateForEn(dateWithdrawal)).getTime();

                    if (dateDeliveryTime === 0 || (!notUseDateWithdrawal && dateWithdrawalTime === 0)) arrErrors.push('Data prevista de entrega e data prevista de retirada devem ser informadas corretamente.');
                    else if (!notUseDateWithdrawal && dateDeliveryTime >= dateWithdrawalTime) arrErrors.push('Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada.');
                }

                if (arrErrors.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                }

                if (arrErrors.length === 0) {
                    changeStepPosAbsolute();
                    fixEquipmentDates();
                    return true;
                }
                return false;
            }
            else if (currentIndex === 3 && newIndex > 3) { // equipamento
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }

                if ($('#equipaments-selected div').length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Selecione um equipamento.</li></ol>'
                    });
                    return false;
                }

                let idEquipament,
                    stockEquipament,
                    referenceEquipament,
                    nameEquipament,
                    stockMax,
                    dateDeliveryTime,
                    dateWithdrawalTime,
                    residueEquipament,
                    vehicleEquipament,
                    driveEquipament;

                $('#equipaments-selected div.card').each(function() {
                    idEquipament        = parseInt($('.card-header', this).attr('id-equipament'));
                    stockEquipament     = parseInt($('[name="stock_equipament"]', this).val());
                    referenceEquipament = $('[name="reference_equipament"]', this).val();
                    nameEquipament      = $('.card-header a:eq(0)', this).text();
                    stockMax            = parseInt($('[name="stock_equipament"]', this).attr('max-stock'));
                    residueEquipament   = parseInt($('[name="residue[]"]', this).val());
                    vehicleEquipament   = parseInt($('[name="vehicle[]"]', this).val());
                    driveEquipament     = parseInt($('[name="driver[]"]', this).val());

                    /*if (!residueEquipament)
                        arrErrors.push(`O equipamento ( <strong>${nameEquipament}</strong> ) deve ser informado um resíduo.`);

                    if (!vehicleEquipament)
                        arrErrors.push(`O equipamento ( <strong>${nameEquipament}</strong> ) deve ser informado um veículo.`);

                    if (!driveEquipament)
                        arrErrors.push(`O equipamento ( <strong>${nameEquipament}</strong> ) deve ser informado um motorista.`);*/

                    if (isNaN(stockEquipament) || stockEquipament === 0)
                        arrErrors.push(`O equipamento ( <strong>${nameEquipament}</strong> ) deve ser informado uma quantidade.`);

                    else if (stockEquipament > stockMax)
                        arrErrors.push(`O equipamento ( <strong>${nameEquipament}</strong> ) não tem estoque suficiente. <strong>Disponível: ${stockMax} un</strong>`);

                    notUseDateWithdrawal = $('.not_use_date_withdrawal', this).is(':checked');

                    dateDeliveryTime = new Date(transformDateForEn($('input[name="date_delivery_equipament"]', this).val())).getTime();
                    dateWithdrawalTime = new Date(transformDateForEn($('input[name="date_withdrawal_equipament"]', this).val())).getTime();

                    if (dateDeliveryTime === 0 || (!notUseDateWithdrawal && dateWithdrawalTime === 0)) arrErrors.push(`A data prevista de entrega e data prevista de retirada do equipamento ( <strong>${nameEquipament}</strong> ) deve ser informada corretamente.`);
                    else if (!notUseDateWithdrawal && dateDeliveryTime >= dateWithdrawalTime) arrErrors.push(`A data prevista de entrega do equipamento ( <strong>${nameEquipament}</strong> ) não pode ser maior ou igual que a data prevista de retirada.`);
                });

                if (arrErrors.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                }

                if (arrErrors.length === 0) {
                    $('div[id^=collapseEquipament-]').collapse('hide');
                    $('#formCreateRental .actions a[href="#next"]').hide();
                    changeStepPosAbsolute();
                    return true;
                }
                return false;
            }
            else if (currentIndex === 4 && newIndex > 4) { // pagamento

                // if (debug) {
                //     changeStepPosAbsolute();
                //     return true;
                // }

                const netValue = realToNumber($('#net_value').val());

                if (netValue < 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Valor líquido da locação não pode ser negativo.</li></ol>'
                    });
                    return false;
                }

                // existe parcelamento
                if ($('#is_parceled').is(':checked')) {
                    let daysTemp;
                    let priceTemp = 0;
                    let haveErrorDays = false;

                    $('#parcels .parcel').each(function(){
                         if (daysTemp === undefined) daysTemp = parseInt($('[name="due_day[]"]', this).val());
                         else if (daysTemp >= parseInt($('[name="due_day[]"]', this).val())) {
                             haveErrorDays = true;
                             return false;
                         } else daysTemp = parseInt($('[name="due_day[]"]', this).val());

                        priceTemp += realToNumber($('[name="value_parcel[]"]', this).val());
                    });

                    if (haveErrorDays) { // ecnontrou erro nas datas de vencimento
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>A ordem dos vencimentos devem ser informados em ordem crescente.</li></ol>'
                        });
                        return false;
                    }

                    if (priceTemp.toFixed(2) !== netValue.toFixed(2)) { // os valores das parcelas não corresponde ao valor líquido
                        if ($('#automatic_parcel_distribution').is(':checked')) recalculeParcels();
                        else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Atenção',
                                html: '<ol><li>A soma das parcelas deve corresponder ao valor líquido.</li></ol>'
                            });
                            return false;
                        }
                    }
                }

                changeStepPosAbsolute();
                return true;
            }

            changeStepPosAbsolute();
            return true;
        },
        onStepChanged: async function (event, currentIndex, priorIndex)
        {
            changeStepPosUnset();
            let arrErrors = [];
            let typeLocation = parseInt($('input[name="type_rental"]:checked').val());
            let time0,time1,time2,time3,time4,time5,time6,time7,time8 = 0;

            if (priorIndex === 0) { // tipo de cobrança
                // Com cobrança
                let payment  = $('#formCreateRental-p-4 #payment');

                typeLocation === 0 ? payment.removeClass('payment-no').addClass('payment-yes') : payment.removeClass('payment-yes').addClass('payment-no');

                if (typeLocation === 0) $('#formCreateRental-t-4').html('<span class="number">5.</span> Pagamento');
                else $('#formCreateRental-t-4').html('<span class="number">5.</span> Resumo Equipamento');
            }
            let date = new Date();
            time0 = date.getTime();

            if (priorIndex <= 3 && currentIndex === 4) { // equipamento

                let pricesAndStocks;
                let dataEquipaments = [];
                let dataEquipamentsPayCheck = [];
                let newPricesUpdate = [];
                let newPricesUpdateNames = [];
                let idEquipaments = [];
                let priceEquipament = 0;
                let idEquipament,stockEquipament,referenceEquipament,nameEquipament;
                date = new Date();
                time1 = date.getTime();
                await $('#equipaments-selected div.card').each(async function() {
                    idEquipament        = parseInt($('.card-header', this).attr('id-equipament'));
                    stockEquipament     = parseInt($('[name="stock_equipament"]', this).val());
                    referenceEquipament = $('[name="reference_equipament"]', this).val();
                    nameEquipament      = $('.card-header a:eq(0)', this).text();
                    dataEquipaments.push([idEquipament, stockEquipament, nameEquipament]);
                    idEquipaments.push(idEquipament);
                });
                date = new Date();
                time2 = date.getTime();

                $('.list-equipaments-payment-load').show();
                $('.list-equipaments-payment').hide();
                $('#gross_value').html('<i class="fa fa-spin fa-spinner"></i>&nbsp;&nbsp;Calculando');
                if ($('#calculate_net_amount_automatic').is(':checked'))
                    $('#net_value').val('Calculando...');

                date = new Date();
                time3 = date.getTime();
                pricesAndStocks = await getPriceStockEquipaments(idEquipaments);
                date = new Date();
                time4 = date.getTime();
                if (pricesAndStocks) {
                    await Promise.all(dataEquipaments.map(async equipament => {
                        dataEquipamentsPayCheck.push(equipament[0]);
                        if (equipament[1] > pricesAndStocks[equipament[0]].stock) {
                            $(`#collapseEquipament-${equipament[0]}`).find('input[name="stock_equipament"]').attr('max-stock', pricesAndStocks[equipament[0]].stock).val(pricesAndStocks[equipament[0]].stock);
                            $(`#collapseEquipament-${equipament[0]}`).find('.stock_available').text('Disponível: ' + pricesAndStocks[equipament[0]].stock);
                            arrErrors.push(`O equipamento ( <strong>${equipament[2]}</strong> ) não tem estoque suficiente. <strong>Disponível: ${pricesAndStocks[equipament[0]].stock} un</strong>`);
                        }

                        if (!$(`.list-equipaments-payment li[id-equipament="${equipament[0]}"]`).length) await createEquipamentPayment(equipament[0], pricesAndStocks[equipament[0]]);
                        else {
                            priceEquipament = pricesAndStocks[equipament[0]].price;

                            date = new Date();
                            time5 = date.getTime();
                            $(`#price-un-equipament-${equipament[0]}`).val(numberToReal(priceEquipament));
                            $(`.list-equipaments-payment li[id-equipament="${equipament[0]}"] .stock-equipament-payment strong`).text(equipament[1] + 'un');

                            if (numberToReal(priceEquipament * equipament[1]) !== $(`#price-total-equipament-${equipament[0]}`).val()) {
                                newPricesUpdate.push({
                                    el: $(`#price-total-equipament-${equipament[0]}`),
                                    price: numberToReal(priceEquipament * equipament[1])
                                });
                                newPricesUpdateNames.push(equipament[2] + ' | R$' + $(`#price-total-equipament-${equipament[0]}`).val() + ' <i class="fas fa-long-arrow-alt-right"></i> R$' + numberToReal(priceEquipament * equipament[1]));
                            }
                            date = new Date();
                            time6 = date.getTime();
                        }
                    }));
                }
                date = new Date();
                time7 = date.getTime();

                await $('.list-equipaments-payment li').each(async function() {
                    idEquipament = parseInt($(this).attr('id-equipament'));
                    if (!dataEquipamentsPayCheck.includes(idEquipament))
                        $(`.list-equipaments-payment li[id-equipament="${idEquipament}"]`).remove()
                });
                date = new Date();
                time8 = date.getTime();

                console.log({
                    0: time1-time0,
                    1: time2-time1,
                    2: time3-time2,
                    'check-equip': time4-time3,
                    3: time5-time4,
                    4: time6-time5,
                    5: time7-time6,
                    6: time8-time7,
                })

                if (arrErrors.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
                    });
                    changeStepPosUnset();
                    form.steps("previous");
                    let countMenuIndex = 0;
                    $('#formCreateRental .steps ul li').each(function (){
                        countMenuIndex++;
                        if (countMenuIndex > 4)
                            $(this).removeClass('done').addClass('disabled last');
                    });
                    $('#formCreateRental .steps ul li.current').addClass('error');
                } else {

                    if (newPricesUpdate.length) {
                        Swal.fire({
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
                            if (result.isConfirmed)
                                $.each(newPricesUpdate, function (key, val) {
                                    val.el.val(val.price);
                                });

                            reloadTotalRental();
                            $('.list-equipaments-payment-load').hide();
                            $('.list-equipaments-payment').slideDown('slow');

                            $('#formCreateRental .actions a[href="#next"]').show();
                        })
                    } else {
                        reloadTotalRental();
                        $('.list-equipaments-payment-load').hide();
                        $('.list-equipaments-payment').slideDown('slow');

                        $('#formCreateRental .actions a[href="#next"]').show();
                    }
                }
            }
            if (priorIndex === 3 && currentIndex !== 4)
                $('#formCreateRental .actions a[href="#next"]').show();
            // height custom screen
            //$('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500);

            // Used to skip the "Warning" step if the user is old enough.
            // form.steps("next");
            // form.steps("previous");
        },
        onFinishing: function (event, currentIndex)
        {
            form.validate().settings.ignore = ":disabled";
            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            alert("Submitted!");
        }
    });
})(jQuery);

var searchEquipamentOld = '';

$(function() {
    $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500);
    // $('[name="date_withdrawal"], [name="date_delivery"]').mask('00/00/0000 00:00');
    $('[name="date_withdrawal"], [name="date_delivery"]').inputmask();
    $('.flatpickr').flatpickr({
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
    $('#discount_value, #extra_value, #net_value').maskMoney({
        thousands: '.',
        decimal: ',',
        allowZero: true
    });
    loadDrivers(0, '#newVehicleModal [name="driver"]');
    $('[name="type_rental"]').iCheck({
        checkboxClass: 'icheckbox_square',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });
});

$("#formCreateRental").validate({
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
        $('#formCreateRental [type="submit"]').attr('disabled', true);
        form.submit();
    }
});

$('#searchEquipament').on('blur keyup', function (e){

    if(e.keyCode !== 13 && e.type === 'keyup') return false;

    const searchEquipament = $(this).val();
    let equipamentInUse = [];

    if (searchEquipament === searchEquipamentOld) return false;

    $('#equipaments-selected .card-header').each(function(){
        equipamentInUse.push(parseInt($(this).attr('id-equipament')));
    });

    $('table.list-equipament tbody').empty();

    searchEquipamentOld = searchEquipament;

    $('table.list-equipament tbody').empty();

    if (searchEquipament === '') {
        equipamentMessageDefault('<i class="fas fa-search"></i> Pesquise por um equipamento');
        return false;
    }

    equipamentMessageDefault('<i class="fas fa-spinner fa-spin"></i> Carregando equipamentos ...');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetEquipaments').val(),
        data: { searchEquipament, equipamentInUse },
        success: response => {

            $('table.list-equipament tbody').empty();

            if (!response.length) {
                equipamentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                return false;
            }

            let badgeStock = '';
            $.each(response, function (key, val) {
                badgeStock = val.stock <= 0 ? 'danger' : 'primary';
                $('table.list-equipament tbody').append(`
                        <tr class="equipament" id-equipament="${val.id}">
                            <td class="text-left"><h6 class="mb-1 text-left">${val.name}</h6></td>
                            <td><div class="badge badge-pill badge-lg badge-info">${val.reference}</div></td>
                            <td><div class="badge badge-pill badge-lg badge-${badgeStock}">${val.stock} un</div></td>
                            <td class="text-right">
                                <button type="button" class="badge badge-lg badge-pill badge-success">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    `);
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

$('#cleanSearchEquipament').on('click', function (){
    $('#searchEquipament').val('').trigger('blur');
});

$('table.list-equipament').on('click', '.equipament', function(){
    const idEquipament = $(this).attr('id-equipament');

    $(`.equipament[id-equipament="${idEquipament}"]`).empty().toggleClass('equipament load-equipament').append('<td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando ...</td>')

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetEquipament').val(),
        data: { idEquipament, validStock: true },
        success: response => {

            if (!response.success) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: `<ol><li>${response.data}</li></ol>`
                });
                searchEquipamentOld = '';
                $('#searchEquipament').trigger('blur');
                return false;
            }

            response = response.data;

            const date_delivery = $('input[name="date_delivery"]').val();
            const date_withdrawal = $('input[name="date_withdrawal"]').val();

            const colRef = response.cacamba ? 'col-md-4' : 'col-md-8';
            const colQty = response.cacamba ? 'col-md-3' : 'col-md-4';
            const displayResidue = response.cacamba ? '' : 'display-none';

            $('#equipaments-selected').append(`
                    <div class="card">
                        <div class="card-header" role="tab" id="headingEquipament-${response.id}" id-equipament="${response.id}">
                            <h5 class="mb-0 d-flex align-items-center">
                                <a class="collapsed pull-left w-100" data-toggle="collapse" href="#collapseEquipament-${response.id}" aria-expanded="false" aria-controls="collapseEquipament-${response.id}">
                                    ${response.name}
                                </a>
                                <a class="remove-equipament pull-right"><i class="fa fa-trash"></i></a>
                            </h5>
                        </div>
                        <div id="collapseEquipament-${response.id}" class="collapse" role="tabpanel" aria-labelledby="headingEquipament-${response.id}" data-parent="#equipaments-selected" id-equipament="${response.id}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group ${colRef}">
                                        <label>Referência</label>
                                        <input type="text" class="form-control" value="${response.reference}" name="reference_equipament" disabled>
                                    </div>
                                    <div class="${colQty}">
                                        <div class="form-group flatpickr label-animate stock-group">
                                            <label>Quantidade</label>
                                            <input type="tel" name="stock_equipament" class="form-control col-md-9 pull-left flatpickr-input" value="1" max-stock="${response.stock}">
                                            <div class="input-button-calendar col-md-3 pull-right no-padding">
                                                <button class="input-button pull-right btn-primary w-100 btn-view-price-period-equipament" data-toggle="tootip" title="Visualizar valor por período" id-equipament="${response.id}">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-danger font-weight-bold stock_available pull-left">Disponível: ${response.stock}</small>
                                    </div>
                                    <div class="form-group col-md-5 label-animate ${displayResidue}">
                                        <label>Resíduo</label>
                                        <div class="input-group label-animate">
                                            <select class="form-control" name="residue[]" disabled>
                                                <option>Carregando ...</option>
                                            </select>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newResidueModal" title="Novo Resíduo"><i class="fas fa-plus-circle"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6 label-animate">
                                        <label>Veículo</label>
                                        <div class="input-group label-animate">
                                            <select class="form-control" name="vehicle[]" disabled>
                                                <option>Carregando ...</option>
                                            </select>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newVehicleModal" title="Novo Veículo"><i class="fas fa-plus-circle"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 label-animate">
                                        <label>Motorista</label>
                                        <div class="input-group label-animate">
                                            <select class="form-control" name="driver[]" disabled>
                                                <option>Carregando ...</option>
                                            </select>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newDriverModal" title="Novo Motorista"><i class="fas fa-plus-circle"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="switch pt-3">
                                            <input type="checkbox" class="check-style check-xs use_date_diff_equip" name="use_date_diff_equip" id="use_date_diff_equip_${response.id}">
                                            <label for="use_date_diff_equip_${response.id}" class="check-style check-xs"></label> Usar datas de entrega e/ou retirada diferentes para esse equipamento.
                                        </div>
                                    </div>
                                </div>
                                <div class="row display-none use_date_diff_equip_show">
                                    <div class="col-md-6">
                                        <div class="form-group flatpickr">
                                            <label>Data Prevista de Entrega</label>
                                            <input type="text" name="date_delivery_equipament" class="form-control col-md-9 pull-left" value="${date_delivery}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input disabled>
                                            <div class="input-button-calendar col-md-3 pull-right no-padding calendar_equipament">
                                                <a class="input-button pull-left btn-primary" title="toggle" data-toggle disabled>
                                                    <i class="fa fa-calendar text-white"></i>
                                                </a>
                                                <a class="input-button pull-right btn-primary" title="clear" data-clear disabled>
                                                    <i class="fa fa-times text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group flatpickr">
                                            <label>Data Prevista de Retirada</label>
                                            <input type="text" name="date_withdrawal_equipament" class="form-control col-md-9 pull-left" value="${date_withdrawal}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input disabled>
                                            <div class="input-button-calendar col-md-3 pull-right no-padding calendar_equipament">
                                                <a class="input-button pull-left btn-primary" title="toggle" data-toggle disabled>
                                                    <i class="fa fa-calendar text-white"></i>
                                                </a>
                                                <a class="input-button pull-right btn-primary" title="clear" data-clear disabled>
                                                    <i class="fa fa-times text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="switch pt-1">
                                                <input type="checkbox" class="check-style check-xs not_use_date_withdrawal" name="not_use_date_withdrawal_equip" id="not_use_date_withdrawal_${response.id}" disabled>
                                                <label for="not_use_date_withdrawal_${response.id}" class="check-style check-xs"></label> Não informar data de retirada
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 mt-2">
                                        <button type="button" class="btn btn-primary pull-right hideEquipament" id-equipament="${response.id}"><i class="fa fa-angle-up"></i> Ocultar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            $(`.load-equipament[id-equipament="${idEquipament}"]`).hide(300);
            showSeparatorEquipamentSelected();
            $('#cleanSearchEquipament').trigger('click')
            setTimeout(() => {
                $(`.load-equipament[id-equipament="${idEquipament}"]`).remove();

                if (!$(`.list-equipament tbody tr`).length) {
                    equipamentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                }
                checkLabelAnimate();
                $(`#collapseEquipament-${idEquipament}`).collapse('show');
                $(`#collapseEquipament-${idEquipament} input[name="stock_equipament"]`).mask('0#');
                $(`#collapseEquipament-${idEquipament} input[name="date_withdrawal_equipament"]`).inputmask();
                $(`#collapseEquipament-${idEquipament} input[name="date_delivery_equipament"]`).inputmask();
                $(`#collapseEquipament-${idEquipament} .btn-view-price-period-equipament`).tooltip();

                $(`#collapseEquipament-${idEquipament} .flatpickr:not(.stock-group)`).flatpickr({
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
                if ($('#not_use_date_withdrawal').is(':checked')) {
                    $(`#collapseEquipament-${idEquipament} input[name="date_withdrawal_equipament"]`).val('');
                    $(`#collapseEquipament-${idEquipament} .not_use_date_withdrawal`).prop('checked', true);
                }

                loadVehicles(0,`#collapseEquipament-${idEquipament} select[name="vehicle[]"]`);
                loadDrivers(0, `#collapseEquipament-${idEquipament} select[name="driver[]"]`);
                loadResidues(0,`#collapseEquipament-${idEquipament} select[name="residue[]"]`);
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
});

$(document).on('click', '.remove-equipament i', function (){
    $(this).closest('.card').slideUp(500);
    setTimeout(() => {
        $(this).closest('.card').remove();
        searchEquipamentOld = '';
        $('#searchEquipament').trigger('blur');
        showSeparatorEquipamentSelected();
    }, 550);
});

$(document).on('click', '.hideEquipament', function (){
    const idEquipament = parseInt($(this).attr('id-equipament'));
    $(`#collapseEquipament-${idEquipament}`).collapse('hide');
});

$(document).on('click', '.use_date_diff_equip', function (){
    const elEquip = $(this).closest('.card-body');
    let date_delivery, date_withdrawal;

    elEquip.find('input[name="date_delivery_equipament"]').attr('disabled', !$(this).is(':checked'));
    elEquip.find('.not_use_date_withdrawal').attr('disabled', !$(this).is(':checked'));

    if (!elEquip.find('.not_use_date_withdrawal').is(':checked'))
        elEquip.find('input[name="date_withdrawal_equipament"]').attr('disabled', !$(this).is(':checked'));

    if (!elEquip.find('.not_use_date_withdrawal').is(':checked'))
        elEquip.find('.calendar_equipament:eq(1) a').attr('disabled', !$(this).is(':checked'));

    elEquip.find('.calendar_equipament:eq(0) a').attr('disabled', !$(this).is(':checked'));

    if (!$(this).is(':checked')) {
        date_delivery = $('input[name="date_delivery"]').val();
        date_withdrawal = $('input[name="date_withdrawal"]').val();

        elEquip.find('input[name="date_delivery_equipament"]').val(date_delivery);
        elEquip.find('input[name="date_withdrawal_equipament"]').val(date_withdrawal);

        if ($('#not_use_date_withdrawal').is(':checked'))
            elEquip.find('.not_use_date_withdrawal').prop('checked', true);
        else
            elEquip.find('.not_use_date_withdrawal').prop('checked', false);

        checkLabelAnimate();

        elEquip.find('.use_date_diff_equip_show').slideUp('slow');
    } else
        elEquip.find('.use_date_diff_equip_show').slideDown({
            start: function () {
                $(this).css({
                    display: "flex"
                })
            }
        });
});

$(document).on('blur change', '[name="stock_equipament"]', function (){
    const maxStock      = parseInt($(this).attr('max-stock'));
    const stock         = parseInt($(this).val());
    const idEquipament  = parseInt($(this).closest('.card').find('.card-header').attr('id-equipament'));

    if (stock > maxStock) {
        Toast.fire({
            icon: 'error',
            title: `A quantidade não pode ser superior a ${maxStock} un.`
        });
        $(this).val(maxStock);
        setTimeout(() => {
            $(`#collapseEquipament-${idEquipament}`).collapse('show');
            $(this).focus();
        }, 550);
    }
});

$('#not_use_date_withdrawal').on('click', function (){
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

    elEquip.find('input[name="date_withdrawal_equipament"]').attr('disabled', $(this).is(':checked'));
    elEquip.find('.flatpickr a').attr('disabled', $(this).is(':checked'));

    elEquip.find('input[name="date_withdrawal_equipament"]').val('');

    if (!$(this).is(':checked'))
        elEquip.find('input[name="date_withdrawal_equipament"]').val(getTodayDateBr());

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

$(document).on('click', '.btn-view-price-period-equipament', function (){
    const btn = $(this);
    const idEquipament = $(this).attr('id-equipament');

    btn.attr('disable', true);

    let descPeriod = '';

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetPriceStockPeriodEquipament').val(),
        data: { idEquipament },
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
        ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').mask('#.##0,00', { reverse: true });

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
    ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').mask('#.##0,00', { reverse: true });

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

$(document).on('change', '[name="vehicle[]"]', function (){
    const vehicle_id = $(this).val();
    if (vehicle_id == 'Selecione ...') return false;

    const el = $(this).closest('.card-body');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        data: { vehicle_id },
        url: $('#routeGetVehicle').val(),
        async: true,
        success: response => {
            if (response.driver_id && el.find('[name="driver[]"]').val() === 'Selecione ...')
                el.find('[name="driver[]"]').val(response.driver_id)
            else if(response.driver_id)
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
                    if (result.isConfirmed)
                        el.find('[name="driver[]"]').val(response.driver_id)
                })

        }
    });
});

$('#calculate_net_amount_automatic').on('change', function(){
    if ($(this).is(':checked')) {
        $('#discount_value').val('0,00');
        $('#extra_value').val('0,00');
        $('#net_value').attr('disabled', true);
        reloadTotalRental();
    } else $('#net_value').attr('disabled', false);
});

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

const createParcel = due => {
    const disabledValue = $('#automatic_parcel_distribution').is(':checked') ? 'disabled' : '';
    return `<div class="form-group mt-1 parcel display-none">
            <div class="d-flex align-items-center justify-content-between">
                <div class="input-group col-md-12 no-padding">
                    <div class="input-group-prepend stock-equipament-payment col-md-3 no-padding">
                        <span class="input-group-text col-md-12 no-border-radius "><strong>${(due+1)}º Vencimento</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 text-center" name="due_day[]" value="${(due*30)}">
                    <input type="date" class="form-control col-md-4 text-center" name="due_date[]" value="${sumDaysDateNow(due*30)}">
                    <div class="input-group-prepend col-md-1 no-padding">
                        <span class="input-group-text pl-3 pr-3 col-md-12"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 no-border-radius text-center" name="value_parcel[]" value="0,00" ${disabledValue}>
                </div>
            </div>
        </div>`
}

const equipamentMessageDefault = message => {
    $('table.list-equipament tbody').append(`
            <tr>
                <td class="text-left"><h6 class="text-center">${message}</h6></td>
            </tr>
        `);
}

const showSeparatorEquipamentSelected = () => {
    if ($('#equipaments-selected div').length)
        $('.equipaments-selected hr.separator-dashed').slideDown(300);
    else
        $('.equipaments-selected hr.separator-dashed').slideUp(300);
}

const fixEquipmentDates = () => {
    let notUseDateWithdrawal = $('#not_use_date_withdrawal').is(':checked');
    let dateDelivery = $('input[name="date_delivery"]').val();
    let dateWithdrawal = $('input[name="date_withdrawal"]').val();

    $('#equipaments-selected div.card').each(function() {
        if (!$('.use_date_diff_equip', this).is(':checked')) {
            $('input[name="date_delivery_equipament"]', this).val(dateDelivery);
            $('input[name="date_withdrawal_equipament"]', this).val(dateWithdrawal);
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

const getStockEquipament = async idEquipament => {
    let stockReal = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetStockEquipament').val(),
        data: { idEquipament },
        async: true,
        success: response => {
            return response;
        }
    });

    return stockReal;
}

const getPriceEquipament = async idEquipament => {
    const check_not_use_date_withdrawal = $(`#collapseEquipament-${idEquipament} input[name="not_use_date_withdrawal"]`).is(':checked');
    let diffDays = false;

    if (!check_not_use_date_withdrawal) {
        let dateDelivery = new Date(transformDateForEn($(`#collapseEquipament-${idEquipament} input[name="date_delivery_equipament"]`).val().split(' ')[0]).replace(/-/g, '/'));
        let dateWithdrawal = new Date(transformDateForEn($(`#collapseEquipament-${idEquipament} input[name="date_withdrawal_equipament"]`).val().split(' ')[0]).replace(/-/g, '/'));

        let timeDiff = Math.abs(dateWithdrawal.getTime() - dateDelivery.getTime());
        diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
    }

    let price = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetPriceEquipament').val(),
        data: { idEquipament, diffDays },
        async: true,
        success: response => {
            return response;
        }, error: e => { console.log(e) }
    });

    return price;
}

const getEquipament = async equipament => {

    let data = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetEquipament').val(),
        data: { idEquipament: equipament },
        async: true,
        success: response => {
            return response;
        }, error: e => { console.log(e) }
    });

    return data.success ? data.data : false;
}

const createEquipamentPayment = async (equipament, priceStock = null) => {

    let dataEquipament = await getEquipament(equipament);
    let stockEquipament = $(`#collapseEquipament-${equipament} input[name="stock_equipament"]`).val();
    const priceEquipament = priceStock === null ? await getPriceEquipament(equipament) : priceStock.price;
    let priceEquipamentFormat = numberToReal(priceEquipament);
    let priceEquipamentTotal = numberToReal(priceEquipament * stockEquipament);

    if (!dataEquipament) return false;

    let paymentEquipament = `
        <li class="pb-3" id-equipament="${equipament}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex col-md-6 no-padding">
                    <div class="ml-3">
                        <h6 class="mb-1">${dataEquipament.name}</h6>
                        <small class="text-muted"><strong>${dataEquipament.reference}</strong></small>
                    </div>
                </div>
                <div class="input-group col-md-6 no-padding payment-hidden-invert-stock">
                    <div class="input-group-prepend stock-equipament-payment">
                        <span class="input-group-text pl-3 pr-3"><strong>${stockEquipament}un</strong></span>
                    </div>
                    <input type="text" class="form-control price-un-equipament payment-hidden" id="price-un-equipament-${equipament}" value="${priceEquipamentFormat}" disabled>
                    <div class="input-group-prepend payment-hidden">
                        <span class="input-group-text pl-3 pr-3"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control price-total-equipament payment-hidden" name="priceTotalEquipament[]" id="price-total-equipament-${equipament}" value="${priceEquipamentTotal}">
                </div>
            </div>
        </li>
    `;

    $('.list-equipaments-payment').append(paymentEquipament);

    setTimeout(() => {
        $(`#price-un-equipament-${equipament}, #price-total-equipament-${equipament}`).maskMoney({
            thousands: '.',
            decimal: ',',
            allowZero: true
        });
        if ($('.list-equipaments-payment li').length === 1) $('.list-equipaments-payment li').addClass('one-li-list-equipaments-payment');
        else $('.list-equipaments-payment li').removeClass('one-li-list-equipaments-payment');

        $(`#price-total-equipament-${equipament}`).on('keyup', () => {
            reloadTotalRental();
        }).on('blur', function(){
            if ($(this).val() === '') $(this).val('0,00')
        });
    }, 250);
}

const reloadTotalRental = () => {

    let grossValue  = 0;
    let priceEquipament = 0;
    let discount    = realToNumber($('#discount_value').val());
    let extra       = realToNumber($('#extra_value').val());

    discount    = isNaN(discount) ? 0 : discount;
    extra       = isNaN(extra) ? 0 : extra;

    $('.list-equipaments-payment li').each(function() {
        priceEquipament = realToNumber($('.price-total-equipament', this).val());
        grossValue += isNaN(priceEquipament) ? 0 : priceEquipament;
    });

    $('#gross_value').text(numberToReal(grossValue));

    if ($('#calculate_net_amount_automatic').is(':checked'))
        $('#net_value').val(numberToReal(grossValue - discount + extra));

    if ($('#automatic_parcel_distribution').is(':checked'))
        recalculeParcels();

    return grossValue - discount + extra;
}

const getPriceStockEquipaments = async idEquipament => {

    let arrDiffDays = [];
    let arrEquipaments = [];
    let dateDelivery, dateWithdrawal, timeDiff, diffDays, not_use_date_withdrawal;

    $('#equipaments-selected div.card').each(async function() {
        not_use_date_withdrawal = $('.not_use_date_withdrawal', this).is(':checked');
        idEquipament            = parseInt($('.card-header', this).attr('id-equipament'));
        diffDays                = false;

        if (!not_use_date_withdrawal) {
            dateDelivery = new Date(transformDateForEn($('input[name="date_delivery_equipament"]', this).val().split(' ')[0]).replace(/-/g, '/'));
            dateWithdrawal = new Date(transformDateForEn($('input[name="date_withdrawal_equipament"]', this).val().split(' ')[0]).replace(/-/g, '/'));

            timeDiff = Math.abs(dateWithdrawal.getTime() - dateDelivery.getTime());
            diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
        }
        arrDiffDays[idEquipament] = diffDays;
        arrEquipaments.push(idEquipament);
    });

    if (!arrDiffDays.length || !arrEquipaments.length) return false;

    let priceStock = await $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('#routeGetPriceStockEquipaments').val(),
        data: { arrEquipaments, arrDiffDays },
        async: true,
        success: response => {
            return response;
        }, error: e => { console.log(e) }
    });

    return priceStock;
}
