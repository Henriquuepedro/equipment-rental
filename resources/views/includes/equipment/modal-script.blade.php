<script>
    $(() => {
        $('#newEquipmentModal [name="cpf"]').mask('000.000.000-00');
        $('#newEquipmentModal [name="phone"]').mask('(00) 000000000');
        $('#newEquipmentModal [name="rg"], #newEquipmentModal [name="cnh"]').mask('0#');
    });

    $("#formEquipment").validate({
        rules: {
            name: {
                name_valid: true
            },
            volume: {
                volume: true
            },
            reference: {
                required: true
            }
        },
        messages: {
            reference: {
                required: "Informe uma referência/código/numeração para seu Equipmento"
            }
        },
        invalidHandler: function(event, validator) {
            $('#newEquipmentModal').animate({scrollTop:0}, 200);
            let arrErrors = [];
            $.each(validator.errorMap, function (key, val) {
                arrErrors.push(val);
            });
            setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                })
            }, 250);
        },
        submitHandler: function(form) {
            let verifyPeriod = verifyPeriodComplet();
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
            let getForm = $('#formEquipment');

            getForm.find('button[type="submit"]').attr('disabled', true);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: getForm.attr('action'),
                data: getForm.serialize(),
                dataType: 'json',
                success: response => {

                    getForm.find('button[type="submit"]').attr('disabled', false);

                    if (!response.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>' + response.message + '</li></ol>'
                        });
                        return false;
                    }

                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });

                    $('#newEquipmentModal').modal('hide');
                    $('#searchEquipment').val(getForm.find('input[name="reference"]').val()).trigger('blur');
                    cleanFormEquipmentModal();
                }, error: e => {
                    getForm.find('button[type="submit"]').attr('disabled', false);
                    let arrErrors = [];

                    $.each(e.responseJSON.errors, function( index, value ) {
                        arrErrors.push(value);
                    });

                    if (!arrErrors.length && e.responseJSON.message !== undefined)
                        arrErrors.push('Você não tem permissão para fazer essa operação!');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                    });
                }
            });
        }
    });


    $('#formEquipment [name="type_equipment"]').on('change', function(){
        $('#formEquipment [type="submit"]').show('slow');
    });

    const cleanFormEquipmentModal = () => {
        $('#formEquipment .remove-period').trigger('click');
        $('#formEquipment [name="volume"]').val('Selecione ...');
        $('#formEquipment [name="reference"]').val('');
        $('#formEquipment [name="manufacturer"]').val('');
        $('#formEquipment [name="value"]').val('');
        $('#formEquipment [name="stock"]').val('');
        $('#formEquipment [name="type_equipment"]').prop('checked', false);
        $('#formEquipment .card.display-none').hide();
        $('#formEquipment [type="submit"]').hide();
    }
</script>
