$(() => {
    $('[name="cep[]"], [name="cep"]').mask('00.000-000');
    $('[name="phone_1"],[name="phone_2"]').mask('(00) 000000000');
    $('[name="rg_ie"]').mask('0#');
    if ($('[name="type_person"]:checked').length) {
        $('[name="type_person"]:checked').trigger('change');
    }
    $('[name="state"], [name="city"]').select2();

    getOptionsForm('nationality', $('form[id*="Provider"] [name="nationality"]'), $('[name="nationality_id"]').val() ?? null);
    getOptionsForm('marital_status', $('form[id*="Provider"] [name="marital_status"]'), $('[name="marital_status_id"]').val() ?? null);

    const state = $('[name="state"]').data('value-state');
    const city = $('[name="city"]').data('value-city');
    if (typeof state !== "undefined" && typeof city !== "undefined") {
        loadStates($('[name="state"]'), state);
        loadCities($('[name="city"]'), state, city);
    } else if (typeof state !== "undefined" && typeof city === "undefined") {
        loadStates($('[name="state"]'), state);
    } else {
        loadStates($('[name="state"]'));
        loadCities($('[name="city"]'));
    }

    loadSearchZipcode('#formUpdateProvider [name="cep"]', $('#formUpdateProvider'));
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
        form.find('label[for="name"]').html('Nome do Fornecedor <sup>*</sup>');
        form.find('#name').closest('.form-group').removeClass('col-md-6').addClass('col-md-12');
        form.find('label[for="cpf_cnpj"]').text('CPF');
        form.find('label[for="rg_ie"]').text('RG');
        form.find('#fantasy').val('').closest('.form-group').addClass('d-none');
        form.find('[name="cpf_cnpj"]').mask('000.000.000-00');
        form.find('.personal_data').slideDown('slow');
    }
    else if (type === 'pj') {
        form.find('label[for="name"]').html('Razão Social <sup>*</sup>');
        form.find('#name').closest('.form-group').removeClass('col-md-12').addClass('col-md-6');
        form.find('label[for="cpf_cnpj"]').text('CNPJ');
        form.find('label[for="rg_ie"]').text('IE');
        form.find('#fantasy').closest('.form-group').removeClass('d-none');
        form.find('[name="cpf_cnpj"]').mask('00.000.000/0000-00');
        form.find('.personal_data').slideUp('slow');
    }

    form.find(".card").each(function() {
        $(this).slideDown('slow');
    });

    setTimeout(() => {
        $('[name="state"], [name="city"]').select2()
    }, 500)
});

$(document).on('blur', '[name="cep[]"], [name="cep"], #cep_new', function (){
    const cep = $(this).val().replace(/\D/g, '');
    let el;
    if ($(this).closest('#new-addressses').length)
        el = $(this).closest('.box, td');
    else
        el = $(this).closest('.card-body, td');

    if (cep.length === 0) return false;
    if (cep.length !== 8) {
        Toast.fire({
            icon: 'error',
            title: 'CEP não encontrado'
        });
        return false;
    }
    $.getJSON("https://viacep.com.br/ws/"+ cep +"/json/", function(dados) {

        if (!("erro" in dados)) {
            if(dados.logradouro !== '') {
                el.find('[name^="address"], #address_new').val(dados.logradouro).parent().addClass("label-animate");
            }
            if(dados.bairro !== '') {
                el.find('[name^="neigh"], #neigh_new').val(dados.bairro).parent().addClass("label-animate");
            }
            if(dados.uf !== '') {
                loadStates($('[name="state"]'), dados.uf);
            }
            if(dados.localidade !== '' && dados.uf !== '') {
                loadCities($('[name="city"]'), dados.uf, dados.localidade);
            }
        } //end if.
        else {
            Toast.fire({
                icon: 'error',
                title: 'CEP não encontrado'
            })
        }
    });
})

jQuery.validator.addMethod("cpf_cnpj", function(value, element) {
    value = jQuery.trim(value);

    let retorno =  $('[name="type_person"]:checked').val() === 'pf' ? validCPF(value) : validCNPJ(value);

    return this.optional(element) || retorno;

}, 'Informe um CPF/CNPJ válido');

$('[name="state"]').on('change', function(){
    loadCities($('[name="city"]'), $(this).val());
});
