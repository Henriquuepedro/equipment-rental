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
            } else if (currentIndex === 2) {
                if (debug) {
                    changeStepPosAbsolute();
                    return true;
                }
                let dateDelivery = $('input[name="date_delivery"]').val();
                let dateWithdrawal = $('input[name="date_withdrawal"]').val();

                if (dateDelivery.length < 16) arrErrors.push('Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy.');
                if (dateWithdrawal.length < 16) arrErrors.push('Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy.');

                if (arrErrors.length === 0) {
                    let dateDeliveryTime = new Date(transformDateForEn(dateDelivery)).getTime();
                    let dateWithdrawalTime = new Date(transformDateForEn(dateWithdrawal)).getTime();

                    if (dateDeliveryTime >= dateWithdrawalTime) arrErrors.push('Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada.');
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

            changeStepPosAbsolute();
            return true;
        },
        onStepChanged: function (event, currentIndex, priorIndex)
        {
            changeStepPosUnset();
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
    var validationForm = $("#example-validation-form");
    validationForm.val({
        errorPlacement: function errorPlacement(error, element) {
            element.before(error);
        },
        rules: {
            confirm: {
                equalTo: "#password"
            }
        }
    });
    validationForm.children("div").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        onStepChanging: function (event, currentIndex, newIndex) {
            validationForm.val({
                ignore: [":disabled", ":hidden"]
            })
            return validationForm.val();
        },
        onFinishing: function (event, currentIndex) {
            validationForm.val({
                ignore: [':disabled']
            })
            return validationForm.val();
        },
        onFinished: function (event, currentIndex) {
            alert("Submitted!");
        }
    });
    var verticalForm = $("#example-vertical-wizard");
    verticalForm.children("div").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        stepsOrientation: "vertical",
        onFinished: function (event, currentIndex) {
            alert("Submitted!");
        }
    });
})(jQuery);

const changeStepPosAbsolute = () => {
    $('.wizard > .content > .body').css('position', 'absolute');
}

const changeStepPosUnset = () => {
    setTimeout(() => { $('.wizard > .content > .body').css('position', 'unset') }, 100);
}
