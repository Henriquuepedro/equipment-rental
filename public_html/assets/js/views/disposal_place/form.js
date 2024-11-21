$(() => {
    $('[name="address_zipcode"]').mask('00.000-000');
    $('[name="phone_1"],[name="phone_2"]').mask('(00) 000000000');
    $('[name="rg_ie"]').mask('0#');
    $('[name="address_state"], [name="address_city"]').select2();
    if ($('[name="type_person"]:checked').length) {
        $('[name="type_person"]:checked').trigger('change');
    }

    const state = $('[name="address_state"]').data('value-state');
    const city = $('[name="address_city"]').data('value-city');
    if (typeof state !== "undefined" && typeof city !== "undefined") {
        loadStates($('[name="address_state"]'), state);
        loadCities($('[name="address_city"]'), state, city);
    } else if (typeof state !== "undefined" && typeof city === "undefined") {
        loadStates($('[name="address_state"]'), state);
    } else {
        loadStates($('[name="address_state"]'));
        loadCities($('[name="address_city"]'));
    }

    loadSearchZipcode('#formUpdateDisposalPlace [name="address_zipcode"]', $('#formUpdateDisposalPlace'));
});

$(document).on('keydown', function(e){
    if(e.keyCode == 13){
        return false;
    }
});

$('[name="type_person"]').on('change', function(){
    const type = $(this).val();
    const form = $(this).closest('form');

    if (type === 'pf') {
        form.find('label[for="name"]').html('Nome do local de descarte <sup>*</sup>');
        form.find('#name').closest('.form-group').removeClass('col-md-5').addClass('col-md-10');
        form.find('label[for="cpf_cnpj"]').html('CPF <sup>*</sup>');
        form.find('label[for="rg_ie"]').text('RG');
        form.find('#fantasy').val('').closest('.form-group').addClass('d-none');
        form.find('[name="cpf_cnpj"]').mask('000.000.000-00');
    }
    else if (type === 'pj') {
        form.find('label[for="name"]').html('Razão Social do local de descarte<sup>*</sup>');
        form.find('#name').closest('.form-group').removeClass('col-md-10').addClass('col-md-5');
        form.find('label[for="cpf_cnpj"]').html('CNPJ <sup>*</sup>');
        form.find('label[for="rg_ie"]').text('IE');
        form.find('#fantasy').closest('.form-group').removeClass('d-none');
        form.find('[name="cpf_cnpj"]').mask('00.000.000/0000-00');
    }

    form.find(".card").each(function() {
        $(this).slideDown('slow');
    });

    setTimeout(() => {
        $('[name="address_state"], [name="address_city"]').select2()
    }, 500)
});

jQuery.validator.addMethod("cpf_cnpj", function(value, element) {
    value = jQuery.trim(value);

    let retorno =  $('[name="type_person"]:checked').val() === 'pf' ? validCPF(value) : validCNPJ(value);

    return this.optional(element) || retorno;

}, 'Informe um CPF/CNPJ válido');

$(document).on('change','[name="address_state"]', function(){
    loadCities($('#formUpdateDisposalPlace [name="address_city"]'), $(this).val());
});
