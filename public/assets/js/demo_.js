var ChartColor = ["#5D62B4", "#54C3BE", "#EF726F", "#F9C446", "rgb(93.0, 98.0, 180.0)", "#21B7EC", "#04BCCC"];
var primaryColor = getComputedStyle(document.body).getPropertyValue('--primary');
var secondaryColor = getComputedStyle(document.body).getPropertyValue('--secondary');
var successColor = getComputedStyle(document.body).getPropertyValue('--success');
var warningColor = getComputedStyle(document.body).getPropertyValue('--warning');
var dangerColor = getComputedStyle(document.body).getPropertyValue('--danger');
var infoColor = getComputedStyle(document.body).getPropertyValue('--info');
var darkColor = getComputedStyle(document.body).getPropertyValue('--dark');
var lightColor = getComputedStyle(document.body).getPropertyValue('--light');
var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});
var FORMAT_DATETIME_INTERNATIONAL = 'YYYY-MM-DD HH:mm:ss';
var FORMAT_DATETIME_INTERNATIONAL_NO_SECONDS = 'YYYY-MM-DD HH:mm';
var FORMAT_DATE_INTERNATIONAL = 'YYYY-MM-DD';
var FORMAT_DATETIME_BRAZIL = 'DD/MM/YYYY HH:mm:ss';
var FORMAT_DATETIME_BRAZIL_NO_SECONDS = 'DD/MM/YYYY HH:mm';
var FORMAT_DATE_BRAZIL = 'DD/MM/YYYY';

(function ($) {
    'use strict';
    $(function () {
        var body = $('body');
        var contentWrapper = $('.content-wrapper');
        var scroller = $('.container-scroller');
        var footer = $('.footer');
        var sidebar = $('#sidebar');

        //Add active class to nav-link based on url dynamically
        $('.nav-item.active').find('a:first').attr('aria-expanded',true);
        $('.nav-item.active').find('.collapse').addClass('show');

        //Close other submenu in sidebar on opening any
        $("#sidebar > .nav > .nav-item > a[data-toggle='collapse']").on("click", function () {
            $("#sidebar > .nav > .nav-item").find('.collapse.show').collapse('hide');
        });

        //checkbox and radios
        $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');

        setTimeout(() => { $('.block-screen-load').hide() }, 500 );

        $(".form-control").click(function() {
            $(this).parent().addClass("label-animate");
        });

        $(window).click(function(event) {
            if (!$(event.target).is('.form-control')) {
                $(".form-control").each(function() {
                    if ($(this).val() === '') {
                        $(this).parent().removeClass("label-animate");
                    }
                });
            }
        });
        $(document).on('click, focus change', ".form-control", function() {
            $(".form-control").each(function() {
                if ($(this).val() === '') {
                    $(this).parent().removeClass("label-animate");
                }
            });
            $(this).parent().addClass("label-animate").find("label").addClass('label-focus');
        });

        $(document).on('blur', ".form-control", function(event) {
            $('.label-focus').removeClass('label-focus');
        });

        if ($('.alert-animate.alert-success').length) {
            Toast.fire({
                icon: 'success',
                title: $('.alert-animate.alert-success').text()
            });
        }

        if ($('.alert-animate.alert-warning ol li').length) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: $('.alert-animate.alert-warning').html()
            })
        }
        if ($('.alert-animate.alert-danger').length) {
            Toast.fire({
                icon: 'error',
                title: $('.alert-animate.alert-danger').text()
            });
        }

        $('.dropdown-toggle').dropdown();

        checkLabelAnimate();

        if ($('[data-toggle="tooltip"]').length)
            $('[data-toggle="tooltip"]').tooltip();

        if ($('.select2').length)
            $('.select2').select2();

    });
})(jQuery);

$(document).on('click', '[data-widget="collapse"]', function (){
    $(this).closest('.box').find('.box-body').toggle('slow');
});

const checkLabelAnimate = () => {
    $(".form-control").each(function() {
        if ($(this).val() !== '')
            $(this).parent().addClass("label-animate");
        else
            $(this).parent().removeClass("label-animate");
    });
}

const validCNPJ = cnpj => {
    var b = [ 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 ]
    var c = String(cnpj).replace(/[^\d]/g, '')

    if(c.length !== 14)
        return false

    if(/0{14}/.test(c))
        return false

    for (var i = 0, n = 0; i < 12; n += c[i] * b[++i]);
    if(c[12] != (((n %= 11) < 2) ? 0 : 11 - n))
        return false

    for (var i = 0, n = 0; i <= 12; n += c[i] * b[i++]);
    if(c[13] != (((n %= 11) < 2) ? 0 : 11 - n))
        return false

    return true
}
const validCPF = cpf => {
    cpf = cpf.replace(/[^\d]+/g,'');

    if(cpf === '') return false;

    if (cpf.length !== 11)
        return false;

    if (cpf.length !== 11 ||
        cpf === "00000000000" ||
        cpf === "11111111111" ||
        cpf === "22222222222" ||
        cpf === "33333333333" ||
        cpf === "44444444444" ||
        cpf === "55555555555" ||
        cpf === "66666666666" ||
        cpf === "77777777777" ||
        cpf === "88888888888" ||
        cpf === "99999999999")
        return false;

    add = 0;

    for (i = 0; i < 9; i++)
        add += parseInt(cpf.charAt(i)) * (10 - i);
    rev = 11 - (add % 11);
    if (rev === 10 || rev === 11)
        rev = 0;
    if (rev !== parseInt(cpf.charAt(9)))
        return false;
    add = 0;
    for (i = 0; i < 10; i++)
        add += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if (rev === 10 || rev === 11)
        rev = 0;
    return rev === parseInt(cpf.charAt(10));
}

const validCPFCNPJ = cpf_cnpj => {
    cpf_cnpj = cpf_cnpj.replace(/[^\d]+/g,'');

    if(cpf_cnpj === '') return false;

    if (cpf_cnpj.length !== 11 && cpf_cnpj.length !== 14) return false;

    if (cpf_cnpj.length === 11) return validCPF(cpf_cnpj);
    else if (cpf_cnpj.length === 14) return validCNPJ(cpf_cnpj);
    else return false;
}

const inArray = (needle, haystack) => {
    const length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(typeof haystack[i] == 'object') {
            if(arrayCompare(haystack[i], needle)) return true;
        } else {
            if(haystack[i] == needle) return true;
        }
    }
    return false;
}

// Formata data yyyy-mm-dd -> dd/mm/yyyy
const transformDateForBr = (date, message_default = false) => {
    if (date == null) {
        return message_default;
    }

    const length = date.length;

    if (length !== 10 && length !== 16 && length !== 19) {
        return message_default;
    }

    if (length === 16 || length === 19) {
        if (!moment(date, FORMAT_DATETIME_INTERNATIONAL_NO_SECONDS).isValid()) {
            return message_default;
        }
        return moment(date, FORMAT_DATETIME_INTERNATIONAL_NO_SECONDS).format(FORMAT_DATETIME_BRAZIL_NO_SECONDS);
    }

    if (!moment(date, FORMAT_DATE_INTERNATIONAL).isValid()) {
        return message_default;
    }
    return moment(date, FORMAT_DATE_INTERNATIONAL).format(FORMAT_DATE_BRAZIL);
}
// Formata data dd/mm/yyyy -> yyyy-mm-dd
const transformDateForEn = date => {
    if (date == null) {
        return false;
    }

    const length = date.length;

    if (length !== 10 && length !== 16 && length !== 19) {
        return false;
    }

    if (length === 16 || length === 19) {
        if (!moment(date, FORMAT_DATETIME_BRAZIL_NO_SECONDS).isValid()) {
            return false;
        }
        return moment(date, FORMAT_DATETIME_BRAZIL_NO_SECONDS).format(FORMAT_DATETIME_INTERNATIONAL_NO_SECONDS);
    }

    if (!moment(date, FORMAT_DATE_BRAZIL).isValid()) {
        return false;
    }
    return moment(date, FORMAT_DATE_BRAZIL).format(FORMAT_DATE_INTERNATIONAL);
}

const getTodayDateBr = (returnTime = true, returnSeconds = true) => {
    if (returnTime && returnSeconds) {
        return moment().format(FORMAT_DATETIME_BRAZIL);
    } else if (returnTime && !returnSeconds) {
        return moment().format(FORMAT_DATETIME_BRAZIL_NO_SECONDS);
    }
    return moment().format(FORMAT_DATE_BRAZIL);
}

const getTodayDateEn = (returnTime = true, returnSeconds = true) => {
    if (returnTime && returnSeconds) {
        return moment().format(FORMAT_DATETIME_INTERNATIONAL);
    } else if (returnTime && !returnSeconds) {
        return moment().format(FORMAT_DATETIME_INTERNATIONAL_NO_SECONDS);
    }

    return moment().format(FORMAT_DATE_INTERNATIONAL);
}

// converte valor de Float -> R$
const numberToReal = numero => {
    numero = parseFloat(numero);
    numero = numero.toFixed(2).split('.');
    numero[0] = numero[0].split(/(?=(?:...)*$)/).join('.');
    return numero.join(',');
}

// converte valor de R$ -> Float
const realToNumber = numero => {
    if(numero === undefined) return false;
    numero = numero.toString();
    numero = numero.replace(/\./g, "").replace(/,/g, ".");
    return parseFloat(numero);
}
// Soma dias de acordo com a data de hoje
const sumDaysDateNow = days => {
    return moment().add(days, 'd').format(FORMAT_DATE_INTERNATIONAL);
}

// Soma minutos de acordo com a data de hoje.
const sumMinutesDateNow = (minutes, returnTime = true, returnSeconds = true) => {
    let format = FORMAT_DATE_INTERNATIONAL;
    if (returnTime && returnSeconds) {
        format = FORMAT_DATETIME_INTERNATIONAL;
    } else if (returnTime && !returnSeconds) {
        format = FORMAT_DATETIME_INTERNATIONAL_NO_SECONDS;
    }

    return moment().add(minutes, 'm').format(format);
}

const calculateDays = (date1, date2) => {
    moment.locale('pt-br');
    const data1 = moment(date1,FORMAT_DATE_INTERNATIONAL);
    const data2 = moment(date2,FORMAT_DATE_INTERNATIONAL);
    return data2.diff(data1, 'days');
}

const sumMonthsDateNow = months => {
    return moment().add(months, 'M').format(FORMAT_DATE_INTERNATIONAL);
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.substr(1);
}

const getOptionsForm = async (type, el, selected = null, select_default = null) => {

    const base_uri = $('[name="base_url"]').val() + '/ajax';
    let options = '<option value="0">Selecione ...</option>';
    let endpoint = '';
    let field_id;
    let field_text;
    let data_search = null;

    switch (type) {
        case 'nationality':
            endpoint = `${base_uri}/nacionalidade`;
            field_id = 'id';
            field_text = 'gentile';
            break;
        case 'marital_status':
            endpoint = `${base_uri}/estado-civil`;
            field_id = 'id';
            field_text = 'name';
            break;
        case 'form-of-payment':
            endpoint = `${base_uri}/forma-de-pagamento`;
            field_id = 'id';
            field_text = 'name';
            options = '<option value="">Selecione ...</option>';
            break;
        case 'providers':
            endpoint = `${base_uri}/fornecedor/visualizar-fornecedores`;
            field_id = 'id';
            field_text = 'name';
            options = '<option value="">Selecione ...</option>';
            data_search = 'data';
            break;
        case 'clients':
            endpoint = `${base_uri}/cliente/visualizar-clientes`;
            field_id = 'id';
            field_text = 'name';
            options = '<option value="">Selecione ...</option>';
            data_search = 'data';
            break;
        default:
            return el.empty().append(options);
    }

    if (select_default) {
        options = select_default;
    }

    const response = await fetch(endpoint);
    if (!response.ok) {
        return el.empty().append(options);
    }
    let results = await response.json();

    // Os dados estão dentro de um vetor.
    if (data_search !== null) {
        results = results[data_search];
    }

    let prop_text = '';
    await $(results).each(await function (key, value) {
        prop_text = '';
        if (selected && parseInt(selected) === parseInt(value[field_id])) {
            prop_text = 'selected';
        }
        options += `<option value="${value[field_id]}" ${prop_text}>${value[field_text].capitalize()}</option>`;
    });

    return el.empty().append(options);
}

const availableStock = (el, id = null) => {
    let url = $('[name="base_url"]').val() + `/ajax/equipamento/estoque-disponivel`;
    if (id !== null) {
        url += `/${id}`;
    }

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url,
        success: response => {
            el.text(response.total_equipment);
        }, error: e => {
            console.log(e);
            el.text(0);
        }
    });
}

const loadDaterangePickerInput = (el, event) => {
    el.daterangepicker({
        locale: {
            format: FORMAT_DATE_BRAZIL,
            separator: " - ",
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "De",
            toLabel: "Até",
            customRangeLabel: "Custom",
            daysOfWeek: [
                "Dom",
                "Seg",
                "Ter",
                "Qua",
                "Qui",
                "Sex",
                "Sáb"
            ],
            monthNames: [
                "Janeiro",
                "Fevereiro",
                "Março",
                "Abril",
                "Maio",
                "Junho",
                "Julho",
                "Agosto",
                "Setembro",
                "Outubro",
                "Novembro",
                "Dezembro"
            ],
            firstDay: 0
        }
    }, event);
}

const loadStates = async (el, id = null, name_value_default = 'Selecione ...') => {
    $.ajax({
        type: 'GET',
        url: 'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome',
        success: async response => {

            let options = `<option value="0">${name_value_default}</option>`;
            let selected = '';

            await $(response).each(await function (key, value) {
                selected = id === value.sigla ? 'selected' : '';
                options += '<option data-id="' + value.id + '" value="' + value.sigla + '" '+selected+'>' + value.nome + '</option>';
            })

            el.select2('destroy').empty().html(options).select2();
            checkLabelAnimate();
        }, error: e => {
            console.log(e);
        }
    });
}

const loadCities = async (el, state, id = null, name_value_default = 'Selecione ...') => {
    $.ajax({
        type: 'GET',
        url: `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${state}/municipios?orderBy=nome`,
        success: async response => {

            let options = `<option value="0">${name_value_default}</option>`;
            let selected = '';

            await $(response).each(await function (key, value) {
                selected = id === value.nome ? 'selected' : '';
                options += '<option data-id="' + value.id + '" value="' + value.nome + '" '+selected+'>' + value.nome + '</option>';
            })

            el.select2('destroy').empty().html(options).select2();
            checkLabelAnimate();
        }, error: e => {
            console.log(e);
        }
    });
}

const getDataRegister = async (type, id) => {

    const base_uri = $('[name="base_url"]').val() + '/ajax';
    let endpoint = '';

    switch (type) {
        case 'client':
            endpoint = `${base_uri}/cliente/visualizar-cliente/${id}`;
            break;
        case 'driver':
            endpoint = `${base_uri}/motorista/visualizar-motorista/${id}`;
            break;
        case 'vehicle':
            endpoint = `${base_uri}/veiculo/visualizar-veiculo/${id}`;
            break;
        case 'residue':
            endpoint = `${base_uri}/residuo/visualizar-residuo/${id}`;
            break;
        default:
            return [];
    }

    const response = await fetch(endpoint);
    if (!response.ok) {
        return [];
    }

    return await response.json();
}

const formatDate = (date, format_out, message_default = null) => {
    if (date == null || date === '') {
        return message_default;
    }

    return moment(date).format(format_out);
}
