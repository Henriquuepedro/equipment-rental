$(() => {
    $('[name="cep"]').mask('00.000-000');
    $('[name="phone_1"],[name="phone_2"]').mask('(00) 000000000');
    $('[name="stock"]').mask('0#');
    $('[name="value"]').mask('#.##0,00', { reverse: true });
    $('[name="day_start[]"], [name="day_end[]"]').mask('0#');
    $('[name="value_period[]"]').mask('#.##0,00', { reverse: true });
    if ($('[name="type_equipament"]:checked').length) {
        $('[name="type_equipament"]:checked').trigger('change');
        $(".form-control").each(function() {
            if ($(this).val() != '')
                $(this).parent().addClass("label-animate");
        });
    }
});

$('[name="type_equipament"]').on('change', function(){
    const type = $(this).val();

    if (type === 'cacamba') {
        $('#name').val('').closest('.form-group').addClass('d-none');
        $('#volume').closest('.form-group').removeClass('d-none');
    }
    else if (type === 'others') {
        $('#volume').val($('#volume option:eq(0)').val()).closest('.form-group').addClass('d-none');
        $('#name').closest('.form-group').removeClass('d-none');
    }

    $('.error-form').slideUp('slow');
    $(".card").each(function() {
        $(this).slideDown('slow');
    });
});
jQuery.validator.addMethod("name_valid", function(value, element) {
    value = jQuery.trim(value);
    return value !== "";

}, 'Informe um nome para o equipamento');

jQuery.validator.addMethod("volume", function(value, element) {
    value = jQuery.trim(value);
    return value !== "Selecione ...";

}, 'Selecione um volume para a caçamba');

$('#add-new-period').on('click', function () {

    const verifyPeriod = verifyPeriodComplet();
    if (!verifyPeriod[0]) {
        if (verifyPeriod[2] !== undefined) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+verifyPeriod[2].join('</li><li>')+'</li></ol>'
            })
        } else {
            Toast.fire({
                icon: 'warning',
                title: `Finalize o cadastro do ${verifyPeriod[1]}º período, para adicionar um novo.`
            });
        }
        return false;
    }

    let countPeriod = 0;
    countPeriod = $('.period').length + 1;

    $('#new-periods').append(`
            <div class="period display-none">
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>${countPeriod}º Período</label>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Dia Inicial</label>
                        <input type="text" class="form-control" name="day_start[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Dia Final</label>
                        <input type="text" class="form-control" name="day_end[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Valor</label>
                        <input type="text" class="form-control" name="value_period[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-1">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger remove-period col-md-12"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `).find('.period').slideDown('slow');

    $('[name="day_start[]"], [name="day_end[]"]').mask('0#');
    $('[name="value_period[]"]').mask('#.##0,00', { reverse: true });
    $('#no-have-period').slideUp(500);
});

$(document).on('click', '.remove-period', function (){
    $(this).closest('.period').slideUp(500);
    setTimeout(() => { if ($('.period').length === 0) $('#no-have-period').slideDown(500) }, 600);
    setTimeout(() => { $(this).closest('.period').remove() }, 500);
});

$(document).on('keydown', function(e){
    if(e.keyCode == 13){
        return false;
    }
});

const verifyPeriodComplet = () => {
    cleanBorderPeriod();

    const periodCount = $('.period').length;
    let arrErrors = [];
    let day_start = 0;
    let day_end = 0;
    let value = 0;
    let periodUser = 0;
    let _verifyValuesOutRange;
    let arrDaysVerify = [];
    for (let countPeriod = 0; countPeriod < periodCount; countPeriod++) {
        periodUser++;
        day_start   = $(`[name="day_start[]"]:eq(${countPeriod})`);
        day_end     = $(`[name="day_end[]"]:eq(${countPeriod})`);
        value       = $(`[name="value_period[]"]:eq(${countPeriod})`);
        if (!day_start.val().length) {
            day_start.css('border', '1px solid red');
            arrErrors.push('Dia inicial do período precisa ser preenchido.');
        }
        if (!day_end.val().length) {
            day_end.css('border', '1px solid red');
            arrErrors.push('Dia final do período precisa ser preenchido.');
        }
        if (!value.val().length) {
            value.css('border', '1px solid red');
            arrErrors.push('Valor do período precisa ser preenchido.');
        }
        if (parseInt(day_start.val()) > parseInt(day_end.val())) {
            day_start.css('border', '1px solid red');
            day_end.css('border', '1px solid red');
            arrErrors.push('Dia inicial do período não pode ser maior que a final.');
        }
        _verifyValuesOutRange = verifyValuesOutRange(parseInt(day_start.val()), parseInt(day_end.val()), arrDaysVerify);
        if (_verifyValuesOutRange[0]) {
            day_start.css('border', '1px solid red');
            day_end.css('border', '1px solid red');
            arrErrors.push(`Existem erros no período. O ${periodUser}º período está inválido, já existe algum dia em outros perído.`);
        }
        arrDaysVerify = _verifyValuesOutRange[1];
        if (arrErrors.length) return [false, (countPeriod + 1), arrErrors];
    }
    return [true];
}

const verifyValuesOutRange = (day_start, day_end, arrDaysVerify) => {
    for (let countPer = day_start; countPer <= day_end; countPer++) {
        if (inArray(countPer, arrDaysVerify)) return [true, arrDaysVerify];
        arrDaysVerify.push(countPer);
    }
    return [false, arrDaysVerify];
}

const cleanBorderPeriod = () => {
    $('[name="day_start[]"]').removeAttr('style');
    $('[name="day_end[]"]').removeAttr('style');
    $('[name="value_period[]"]').removeAttr('style');
}
