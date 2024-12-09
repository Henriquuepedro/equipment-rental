$(() => {
    $('[name="cpf"]').mask('000.000.000-00');
    $('[name="phone"]').mask('(00) 000000000');
    $('[name="rg"], [name="cnh"]').mask('0#');
    $('[name="address_state"], [name="address_city"]').select2();
    $('[name="address_zipcode"]').mask('00.000-000');
    $('[name="commission"]').maskMoney({thousands: '.', decimal: ',', allowZero: true});

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

    loadSearchZipcode('#formUpdateDriver [name="address_zipcode"]', $('#formUpdateDriver'));
});

$(document).on('keydown', function(e){
    if(e.keyCode == 13){
        return false;
    }
});

jQuery.validator.addMethod("cpf", function(value, element) {
    value = jQuery.trim(value);

    return this.optional(element) || validCPF(value);

}, 'Informe um CPF válido');
