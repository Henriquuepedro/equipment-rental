$(() => {
    $('[name="cpf"]').mask('000.000.000-00');
    $('[name="phone"]').mask('(00) 000000000');
    $('[name="rg"], [name="cnh"]').mask('0#');
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
