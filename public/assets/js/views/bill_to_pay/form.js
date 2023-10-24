$(function(){
    new Quill('#descriptionDiv', {
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link']
            ]
        },
        theme: 'snow' // or 'bubble'
    });
    getOptionsForm('form-of-payment', $('#formCreateBillsToPay [name="form_payment"]'), $('#form_payment').val());
    $('#value').maskMoney({thousands: '.', decimal: ',', allowZero: true});
});

// Validar dados
$("#formCreateBillsToPay").validate({
    rules: {
        provider: {
            required: true
        },
        value: {
            required: true
        }
    },
    messages: {
        name: {
            required: 'Informe um fornecedor'
        },
        phone_1: {
            required: "Informe um valor para a compra"
        }
    },
    invalidHandler: function(event, validator) {
        $('html, body').animate({scrollTop:0}, 400);
        let arrErrors = [];
        $.each(validator.errorMap, function (key, val) {
            arrErrors.push(val);
        });
        setTimeout(() => {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
            });
        }, 500);
    },
    submitHandler: function(form) {
        $('#formCreateBillsToPay [type="submit"]').attr('disabled', true);

        $("#description").val($("#descriptionDiv .ql-editor").html());

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $('#formCreateBillsToPay').attr('action'),
            data: $('#formCreateBillsToPay').serialize(),
            success: response => {
                console.log(response,'pedro');

                if (!response.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>' + response.message + '</li></ol>'
                    });
                } else {
                    if (response.hasOwnProperty("show_alert_update_payment") && response.show_alert_update_payment) {
                        Swal.fire({
                            title: "Alteração de pagamento",
                            html: `Existem alterações de pagamentos, caso exista parcela paga, deve ser realizado a ação novamente. <br>Deseja atualizar?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#19d895',
                            cancelButtonColor: '#bbb',
                            confirmButtonText: 'Sim, atualizar',
                            cancelButtonText: 'Não atualizar',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('[name="confirm_update_payment"]').val(1);
                                $('#formCreateBillsToPay button[type="submit"]').trigger('click')
                            }
                        })
                    } else {
                        Swal.fire({
                            title: 'Concluído',
                            html: `<h4>${response.message}</h4>`,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#2196f3',
                            cancelButtonColor: '#15b67d',
                            confirmButtonText: 'Voltar para a listagem',
                            cancelButtonText: 'Gerar novo pagamento',
                            reverseButtons: true,
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = $('#back_page').attr('href');
                            } else {
                                window.location.reload()
                            }
                        });
                    }
                }

            }, error: e => {
                console.log(e);

                let arrErrors = []

                $.each(e.responseJSON.errors, function( index, value ) {
                    arrErrors.push(value);
                });

                if (!arrErrors.length && e.responseJSON.message !== undefined) {
                    arrErrors.push('Não foi possível identificar o motivo do erro, recarregue a página e tente novamente!');
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                });
            },
            complete: function(e) {
                if (e.status === 403) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Você não tem permissão para fazer essa operação!'
                    });
                }
                $('#formCreateBillsToPay [type="submit"]').attr('disabled', false);
            }
        });
    }
});

$('#parcels').on('keyup change', '[name="due_day[]"]', function(){
    let days = parseInt($(this).val());
    const el = $(this).closest('.form-group');

    el.find('[name="due_date[]"]').val(sumDaysDateNow(days));
});

$('#parcels').on('blur', '[name="due_date[]"]', function(){
    const dataVctoInput = $(this).val();
    if (dataVctoInput === '') return false;

    const diasVcto = calculateDays(getTodayDateEn(false), dataVctoInput);
    const el = $(this).closest('.form-group');

    el.find('[name="due_day[]"]').val(diasVcto);
});

$('#add_parcel').click(function(){
    const parcels = $('#parcels .parcel').length;

    if (parcels === 24) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            html: '<ol><li>É permitido adicionar até 24 vencimentos.</li></ol>'
        });
        return false;
    }

    $('#parcels').show().append(
        createParcel(parcels)
    ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({thousands: '.', decimal: ',', allowZero: true}).closest('.parcel').find('.remove-payment').tooltip();

    recalculeParcels();
});

$('#automatic_parcel_distribution').change(function(){
    const check = $(this).is(':checked');

    if (check) {
        $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', true);
        recalculeParcels();
    } else {
        $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', false);
    }
});

$('[name="value"]').on('keyup', function(){
    recalculeParcels();
});

$(document).on('click', '.remove-payment', function(){
    const parcels = $('#parcels .parcel').length;

    if (parcels === 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            html: '<ol><li>Deve conter no mínimo uma linha de pagamento.</li></ol>'
        });
        return;
    }

    $('#parcels').find('.remove-payment').tooltip('dispose');

    $(this).closest('.parcel').remove();

    $('#parcels').find('.remove-payment').tooltip();

    recalculeParcels();
});

const createParcel = (last_day = null, due_date = null, value_parcel = null) => {
    const disabledValue = $('#automatic_parcel_distribution').is(':checked') ? 'disabled' : '';

    if (last_day === null) {
        last_day = parseInt($('#parcels .parcel:last [name="due_day[]"]').val());

        if (isNaN(last_day)) {
            last_day = 0;
        } else {
            last_day += 30
        }
    }

    if (due_date === null) {
        due_date = sumDaysDateNow(last_day);
    }

    if (value_parcel === null) {
        value_parcel = '0,00';
    }

    return `<div class="form-group mt-1 parcel">
            <div class="d-flex align-items-center justify-content-between">
                <div class="input-group col-md-12 no-padding">
                    <input type="text" class="form-control col-md-3 text-center" name="due_day[]" value="${last_day}">
                    <input type="date" class="form-control col-md-4 text-center" name="due_date[]" value="${due_date}">
                    <div class="input-group-prepend col-md-1 no-padding">
                        <span class="input-group-text pl-3 pr-3 col-md-12"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-3 no-border-radius text-center" name="value_parcel[]" value="${value_parcel}" ${disabledValue}>
                    <div class="input-group-prepend stock-Equipment-payment col-md-1 no-padding">
                        <button type="button" class="btn btn-danger btn-flat w-100 remove-payment" title="Excluir Pagamento"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>`
}

const recalculeParcels = () => {
    if ($('#automatic_parcel_distribution').is(':checked')) {
        const parcels = $('#parcels .form-group').length;
        const netValue = realToNumber($('#value').val());

        let valueSumParcel = parseFloat(0.00);
        let valueParcel = netValue / parcels;

        for (let count = 0; count < parcels; count++) {

            if((count + 1) === parcels) valueParcel = netValue - valueSumParcel;

            valueSumParcel += parseFloat((netValue / parcels).toFixed(2));
            $(`#parcels .form-group [name="value_parcel[]"]:eq(${count})`).val(numberToReal(valueParcel));
        }
    }
}


const getPayments = (payment_id, callback) => {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: `${$('[name="base_url"]').val()}/ajax/contas-a-pagar/pagamentos/${payment_id}`,
        async: true,
        success: response => {
            callback(response);
        }, error: e => { console.log(e) }
    });
}
