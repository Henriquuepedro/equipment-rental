<script>
    $(() => {
        $('#newEquipamentModal [name="cpf"]').mask('000.000.000-00');
        $('#newEquipamentModal [name="phone"]').mask('(00) 000000000');
        $('#newEquipamentModal [name="rg"], #newEquipamentModal [name="cnh"]').mask('0#');
    });

    $("#formEquipament").validate({
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
                required: "Informe uma referência/código/numeração para seu equipamento"
            }
        },
        invalidHandler: function(event, validator) {
            $('#newEquipamentModal').animate({scrollTop:0}, 200);
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
            let getForm = $('#formEquipament');

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

                    $('#newEquipamentModal').modal('hide');
                    cleanFormEquipamentModal();
                    searchEquipamentOld = '';
                    $('#searchEquipament').trigger('blur');
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


    $('#formEquipament [name="type_equipament"]').on('change', function(){
        $('#formEquipament [type="submit"]').show('slow');
    });

    const cleanFormEquipamentModal = () => {
        $('#formEquipament .remove-period').trigger('click');
        $('#formEquipament [name="volume"]').val('Selecione ...');
        $('#formEquipament [name="reference"]').val('');
        $('#formEquipament [name="manufacturer"]').val('');
        $('#formEquipament [name="value"]').val('');
        $('#formEquipament [name="stock"]').val('');
        $('#formEquipament [name="type_equipament"]').prop('checked', false);
        $('#formEquipament .card.display-none').hide();
        $('#formEquipament [type="submit"]').hide();
    }
</script>
