<script>
    $(() => {
        loadClients();
    });

    $('#confirmAddress').on('hidden.bs.modal', function(e){
        $("body").addClass("modal-open");
    });

    $("#formCreateClientModal").validate({
        rules: {
            name_client: {
                required: true
            },
            phone_1: {
                rangelength: [13, 14]
            },
            phone_2: {
                rangelength: [13, 14]
            },
            cpf_cnpj: {
                cpf_cnpj: true
            }
        },
        messages: {
            name_client: {
                required: 'Informe um nome/razão social para o cliente'
            },
            phone_1: {
                rangelength: "O número de telefone principal está inválido, informe um válido. (99) 999..."
            },
            phone_2: {
                rangelength: "O número de telefone secundário está inválido, informe um válido. (99) 999..."
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
            let verifyAddress = verifyAddressComplet();
            if (!verifyAddress[0]) {
                Toast.fire({
                    icon: 'warning',
                    title: `Finalize o cadastro do ${verifyAddress[1]}º endereço, para finalizar o cadastro.`
                });
                return false;
            }

            let getForm = $('#formCreateClientModal');

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

                    $('#newClientModal').modal('hide');
                    cleanFormClientModal();
                    checkLabelAnimate();
                    loadClients(response.client_id);
                }, error: e => {
                    console.log(e);
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

    const cleanFormClientModal = () => {
        $('#newClientModal .form-control').each(function(){
            $(this).val('').parent().removeClass('label-animate');
        });
        $('#newClientModal table').each(function(){
            $('.remove-address', this).trigger('click');
        });
        $('#newClientModal .display-none').removeAttr('style');
        $('input[name="type_person"]').prop('checked', false);
    }

    const loadClients = (client_id = null) => {

        $('.client-load [name="client"]').empty().append('<option>Carregando ...</option>');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{ route('ajax.client.get-clients') }}',
            dataType: 'json',
            success: response => {

                let selected;

                $('.client-load [name="client"]').empty().append('<option value="0">Selecione ...</option>');
                $.each(response.data, function( index, value ) {
                    selected = value.id === client_id ? 'selected' : '';
                    $('.client-load [name="client"]').append(`<option value='${value.id}' ${selected}>${value.name}</option>`);
                });

                if (client_id !== null) {
                    loadAddresses(client_id);
                }

            }, error: e => {
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

    $('select[name="client"]').on('change', function() {
        let client_id = parseInt($(this).val());

        if (client_id === 0) return false;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{ route('ajax.client.get-client') }}',
            data: { client_id },
            dataType: 'json',
            success: response => {

                if (response.observation) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Observação do Cliente',
                        html: '<hr><h5>'+response.observation+'</h5>'
                    });
                }



            }, error: e => {
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
    });
</script>
