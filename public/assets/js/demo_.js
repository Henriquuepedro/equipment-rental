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

    cnpj = cnpj.replace(/[^\d]+/g,'');

    if(cnpj === '') return false;

    if (cnpj.length !== 14)
        return false;

    // Elimina CNPJs invalidos conhecidos
    if (cnpj === "00000000000000" ||
        cnpj === "11111111111111" ||
        cnpj === "22222222222222" ||
        cnpj === "33333333333333" ||
        cnpj === "44444444444444" ||
        cnpj === "55555555555555" ||
        cnpj === "66666666666666" ||
        cnpj === "77777777777777" ||
        cnpj === "88888888888888" ||
        cnpj === "99999999999999")
        return false;

    // Valida DVs
    tamanho = cnpj.length - 2
    numeros = cnpj.substring(0,tamanho);
    digitos = cnpj.substring(tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado !== digitos.charAt(0))
        return false;

    tamanho = tamanho + 1;
    numeros = cnpj.substring(0,tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    return resultado === digitos.charAt(1);
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
const transformDateForBr = data => {
    if(data == null) return false;

    const length = data.length;
    let splitDateTime = [data];

    if (length !== 10 && length !== 16 && length !== 19) return false;

    if(data.includes('T')) splitDateTime = data.split("T");
    if(data.includes(' ')) splitDateTime = data.split(" ");

    const splitData = splitDateTime[0].split("-");

    const dia = splitData[2];
    const mes = splitData[1];
    const ano = splitData[0];

    let dataFormatada = '';

    dataFormatada += `${dia}/${mes}/${ano}`;

    if (length === 16 || length === 19) {
        const splitTime = splitDateTime[1].split(":");

        const min = splitTime[1];
        const hr  = splitTime[0];

        dataFormatada += ` ${hr}:${min}`;
    }
    return dataFormatada;
}
// Formata data dd/mm/yyyy -> yyyy-mm-dd
const transformDateForEn = data => {
    if(data == null) return false;

    const length = data.length;

    if (length !== 10 && length !== 16) return false;

    const splitDateTime = data.split(' ');
    const splitData = splitDateTime[0].split('/');

    const ano = splitData[2];
    const mes = splitData[1];
    const dia = splitData[0];

    let dataFormatada = '';

    dataFormatada += `${ano}-${mes}-${dia}`;

    if (length === 16) {
        const splitTime = splitDateTime[1].split(":");

        const min = splitTime[1];
        const hr  = splitTime[0];

        dataFormatada += ` ${hr}:${min}`;
    }
    return dataFormatada;
}

const getTodayDateBr = (returnTime = true) => {
    let dateTime = new Date();
    let dd = dateTime.getDate();
    let mm = dateTime.getMonth() + 1;
    let YYYY = dateTime.getFullYear();
    let HH = dateTime.getHours();
    let ii = dateTime.getMinutes();

    dd = dd < 10 ? `0${dd}` : dd;
    mm = mm < 10 ? `0${mm}` : mm;
    HH = HH < 10 ? `0${HH}` : HH;
    ii = ii < 10 ? `0${ii}` : ii;

    let currentDate = `${dd}/${mm}/${YYYY}`;

    if (returnTime) return currentDate+=` ${HH}:${ii}`;

    return currentDate;
}

const getTodayDateEn = (returnTime = true) => {
    let dateTime = new Date();
    let dd = dateTime.getDate();
    let mm = dateTime.getMonth() + 1;
    let YYYY = dateTime.getFullYear();
    let HH = dateTime.getHours();
    let ii = dateTime.getMinutes();

    dd = dd < 10 ? `0${dd}` : dd;
    mm = mm < 10 ? `0${mm}` : mm;
    HH = HH < 10 ? `0${HH}` : HH;
    ii = ii < 10 ? `0${ii}` : ii;

    let currentDate = `${YYYY}-${mm}-${dd}`;

    if (returnTime) return currentDate+=` ${HH}:${ii}`;

    return currentDate;
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
    let dateNow = new Date();
    dateNow.setDate(dateNow.getDate() + days);

    const year = dateNow.getFullYear();
    const month = dateNow.getMonth() + 1 <= 9 ? "0" + (dateNow.getMonth() + 1) : (dateNow.getMonth() + 1);
    const day = dateNow.getDate() <= 9 ? "0" + dateNow.getDate() : dateNow.getDate();
    return `${year}-${month}-${day}`;
}

const calculateDays = (date1, date2) => {
    moment.locale('pt-br');
    const data1 = moment(date1,'YYYY-MM-DD');
    const data2 = moment(date2,'YYYY-MM-DD');
    const diff  = data2.diff(data1, 'days');
    return diff;
}
