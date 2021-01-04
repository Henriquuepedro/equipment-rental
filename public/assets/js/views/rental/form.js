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
            let debug = false;
            let arrErrors = [];
            let notUseDateWithdrawal = $('#not_use_date_withdrawal').is(':checked');

            if (currentIndex === 0) {
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
            else if (currentIndex === 1) {
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
            else if (currentIndex === 2) {
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
            else if (currentIndex === 3) {
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

                let idEquipament,stockEquipament,referenceEquipament,nameEquipament,stockMax,dateDeliveryTime,dateWithdrawalTime;
                $('#equipaments-selected div.card').each(function() {
                    idEquipament        = parseInt($('.card-header', this).attr('id-equipament'));
                    stockEquipament     = parseInt($('[name="stock_equipament"]', this).val());
                    referenceEquipament = $('[name="reference_equipament"]', this).val();
                    nameEquipament      = $('.card-header a:eq(0)', this).text();
                    stockMax            = parseInt($('[name="stock_equipament"]', this).attr('max-stock'));

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
            else if (currentIndex === 4) {
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }
                if (reloadTotalRental() < 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>Valor líquido da locação não pode ser negativo.</li></ol>'
                    });
                    return false;
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

            if (priorIndex === 0) {
                // Com cobrança
                let paymentYes  = $('#formCreateRental-p-4 .payment-yes');
                let paymentNo   = $('#formCreateRental-p-4 .payment-no');

                typeLocation === 0 ? paymentYes.show() : paymentNo.show();
                typeLocation === 0 ? paymentNo.hide() : paymentYes.hide();
            }

            if (priorIndex === 3 && currentIndex === 4  ) {

                let returnAjax;
                let dataEquipaments = [];
                let dataEquipamentsPayCheck = [];
                let idEquipament,stockEquipament,referenceEquipament,nameEquipament;
                await $('#equipaments-selected div.card').each(async function() {
                    idEquipament        = parseInt($('.card-header', this).attr('id-equipament'));
                    stockEquipament     = parseInt($('[name="stock_equipament"]', this).val());
                    referenceEquipament = $('[name="reference_equipament"]', this).val();
                    nameEquipament      = $('.card-header a:eq(0)', this).text();
                    dataEquipaments.push([idEquipament, stockEquipament, nameEquipament]);
                });

                $('.list-equipaments-payment-load').show();
                $('.list-equipaments-payment').hide();

                await Promise.all(dataEquipaments.map(async equipament => {
                    returnAjax = await getStockEquipament(equipament[0]);
                    dataEquipamentsPayCheck.push(equipament[0]);
                    if (equipament[1] > returnAjax) {
                        $(`#collapseEquipament-${equipament[0]}`).find('input[name="stock_equipament"]').attr('max-stock', returnAjax).val(returnAjax);
                        $(`#collapseEquipament-${equipament[0]}`).find('.stock_available').text('Disponível: ' + returnAjax);
                        arrErrors.push(`O equipamento ( <strong>${equipament[2]}</strong> ) não tem estoque suficiente. <strong>Disponível: ${returnAjax} un</strong>`);
                    }

                    if (!$(`.list-equipaments-payment li[id-equipament="${equipament[0]}"]`).length)
                        await createEquipamentPayment(equipament[0]);
                }));

                await $('.list-equipaments-payment li').each(async function() {
                    idEquipament = parseInt($(this).attr('id-equipament'));
                    if (!dataEquipamentsPayCheck.includes(idEquipament))
                        $(`.list-equipaments-payment li[id-equipament="${idEquipament}"]`).remove()
                });

                reloadTotalRental();
                $('.list-equipaments-payment-load').hide();
                $('.list-equipaments-payment').slideDown('slow');

                $('#formCreateRental .actions a[href="#next"]').show();

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

    let dateDelivery    = new Date(transformDateForEn($(`#collapseEquipament-${idEquipament} input[name="date_delivery_equipament"]`).val().split(' ')[0]).replace(/-/g,'/'));
    let dateWithdrawal  = new Date(transformDateForEn($(`#collapseEquipament-${idEquipament} input[name="date_withdrawal_equipament"]`).val().split(' ')[0]).replace(/-/g,'/'));

    var timeDiff = Math.abs(dateWithdrawal.getTime() - dateDelivery.getTime());
    var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

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

const createEquipamentPayment = async equipament => {

    let priceEquipament = numberToReal(await getPriceEquipament(equipament));
    let dataEquipament = await getEquipament(equipament);
    let stockEquipament = $(`#collapseEquipament-${equipament} input[name="stock_equipament"]`).val();

    if (!dataEquipament) return false;

    let paymentEquipament = `
        <li class="pb-3" id-equipament="${equipament}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex col-md-8 no-padding">
                    <div class="ml-3">
                        <h6 class="mb-0">${dataEquipament.name}</h6>
                        <small class="text-muted"> <strong>${dataEquipament.reference}</strong> - ${stockEquipament} un. </small>
                    </div>
                </div>
                <div class="input-group col-md-4 no-padding">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control price-equipament" name="priceEquipament[]" id="price-equipament-${equipament}" value="${priceEquipament}">
                </div>
            </div>
        </li>
    `;

    $('.list-equipaments-payment').append(paymentEquipament);

    setTimeout(() => { $(`#price-equipament-${equipament}`).mask('#.##0,00', { reverse: true }) }, 250);
}

const reloadTotalRental = () => {

    let grossValue  = 0;
    let discount    = realToNumber($('#discount_value').val());
    let extra       = realToNumber($('#extra_value').val());

    $('.list-equipaments-payment li').each(function() {
        grossValue += realToNumber($('.price-equipament', this).val());
    });

    $('#gross_value').val(numberToReal(grossValue));
    $('#net_value').val(numberToReal(grossValue - discount + extra));

    return grossValue - discount + extra;
}
