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
            let typeLocation;
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
                // if (debug) {
                //     changeStepPosAbsolute();
                //     return true;
                // }
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
                // if (debug) {
                //     changeStepPosAbsolute();
                //     return true;
                // }

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

            changeStepPosAbsolute();
            return true;
        },
        onStepChanged: async function (event, currentIndex, priorIndex)
        {
            changeStepPosUnset();
            let arrErrors = [];

            if (priorIndex === 3 && currentIndex === 4  ) {

                let returnAjax;
                let dataEquipaments = [];
                let idEquipament,stockEquipament,referenceEquipament,nameEquipament;
                await $('#equipaments-selected div.card').each(async function() {
                    idEquipament        = parseInt($('.card-header', this).attr('id-equipament'));
                    stockEquipament     = parseInt($('[name="stock_equipament"]', this).val());
                    referenceEquipament = $('[name="reference_equipament"]', this).val();
                    nameEquipament      = $('.card-header a:eq(0)', this).text();
                    dataEquipaments.push([idEquipament, stockEquipament, nameEquipament]);
                });

                await Promise.all(dataEquipaments.map(async equipament => {
                    returnAjax = await checkStockEquipament(equipament[0]);
                    if (equipament[1] > returnAjax) {
                        $(`#collapseEquipament-${equipament[0]}`).find('input[name="stock_equipament"]').attr('max-stock', returnAjax).val(returnAjax);
                        $(`#collapseEquipament-${equipament[0]}`).find('.stock_available').text('Disponível: ' + returnAjax);
                        arrErrors.push(`O equipamento ( <strong>${equipament[2]}</strong> ) não tem estoque suficiente. <strong>Disponível: ${returnAjax} un</strong>`);
                    }
                }));

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

const checkStockEquipament = async idEquipament => {
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
