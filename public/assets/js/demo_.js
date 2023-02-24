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
        $(document).on('click, focus', ".form-control", function() {
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
const transformDateForBr = date => {
    if(date == null) return false;

    const length = date.length;

    if (length !== 10 && length !== 16 && length !== 19) return false;

    if (length === 16 || length === 19) {
        if (!moment(date, "YYYY-MM-DD HH:mm").isValid()) return false;
        return moment(date, "YYYY-MM-DD HH:mm").format("DD/MM/YYYY HH:mm");
    }

    if (!moment(date, "YYYY-MM-DD").isValid()) return false;
    return moment(date, "YYYY-MM-DD").format("DD/MM/YYYY");
}
// Formata data dd/mm/yyyy -> yyyy-mm-dd
const transformDateForEn = date => {
    if(date == null) return false;

    const length = date.length;

    if (length !== 10 && length !== 16 && length !== 19) return false;

    if (length === 16 || length === 19) {
        if (!moment(date, "DD/MM/YYYY HH:mm").isValid()) return false;
        return moment(date, "DD/MM/YYYY HH:mm").format("YYYY-MM-DD HH:mm");
    }

    if (!moment(date, "DD/MM/YYYY").isValid()) return false;
    return moment(date, "DD/MM/YYYY").format("YYYY-MM-DD");
}

const getTodayDateBr = (returnTime = true, seconds = true) => {
    if (returnTime && seconds) return moment().format("DD/MM/YYYY HH:mm:ss");
    else if (returnTime && !seconds) return moment().format("DD/MM/YYYY HH:mm");
    return moment().format("DD/MM/YYYY");
}

const getTodayDateEn = (returnTime = true) => {
    if (returnTime) return moment().format("YYYY-MM-DD HH:mm:ss");
    return moment().format("YYYY-MM-DD");
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
    return moment().add(days, 'd').format("YYYY-MM-DD");
}

const calculateDays = (date1, date2) => {
    moment.locale('pt-br');
    const data1 = moment(date1,'YYYY-MM-DD');
    const data2 = moment(date2,'YYYY-MM-DD');
    return data2.diff(data1, 'days');
}

const sumMonthsDateNow = months => {
    return moment().add(months, 'M').format("YYYY-MM-DD");
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.substr(1);
}

const getOptionsForm = async (type, el, selected = null) => {

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
        default:
            return el.empty().append(options);
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
            format: 'DD/MM/YYYY',
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
