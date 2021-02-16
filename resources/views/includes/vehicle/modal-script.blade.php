<script>
    // Validar dados
    $("#formCreateVehicleModal").validate({
        rules: {
            name: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Informe um nome para o veículo'
            }
        },
        invalidHandler: function(event, validator) {
            $('html, body').animate({scrollTop:0}, 100);
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
            }, 150);
        },
        submitHandler: function(form) {
            let getForm = $('#formCreateVehicleModal');

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

                    $('#newVehicleModal').modal('hide');
                    cleanFormVehicleModal();
                    checkLabelAnimate();
                    @if(\Request::route()->getName() == 'rental.create')
                        loadVehicles(response.vehicle_id, "div[id^='collapseEquipament-'].collapse.show [name^='vehicle_']");

                        $('#equipaments-selected [id^=collapseEquipament-]').each(function(){
                            if ($("div[id^='collapseEquipament-'].collapse.show").attr('id-equipament') !== $(this).attr('id-equipament')) {
                                loadVehicles($('[name^="vehicle_"]', this).val(), `#collapseEquipament-${$(this).attr('id-equipament')} [name^="vehicle_"]`);
                            }
                        });
                    @else
                        loadVehicles(response.vehicle_id, null);
                    @endif

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

    const cleanFormVehicleModal = () => {
        $('#newVehicleModal [name="name"]').val('');
        $('#newVehicleModal [name="reference"]').val('');
        $('#newVehicleModal [name="driver"]').val('0');
        $('#newVehicleModal [name="brand"]').val('');
        $('#newVehicleModal [name="model"]').val('');
        $('#newVehicleModal [name="board"]').val('');
        $('#newVehicleModal [name="observation"]').val('');
    }

    const loadVehicles = (vehicle_id = null, el = null) => {

        $(el).attr('disabled', true).empty().append('<option>Carregando ...</option>');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{ route('ajax.vehicle.get-vehicles') }}',
            dataType: 'json',
            success: response => {

                let selected;
                let vehicle_id_selected = vehicle_id ?? response.lastId;

                $(el).empty().append('<option value="0">Selecione ...</option>');
                $.each(response.data, function( index, value ) {
                    selected = value.id === parseInt(vehicle_id_selected) ? 'selected' : '';
                    $(el).append(`<option value='${value.id}' ${selected}>${value.name}</option>`);
                });
                // so executo o trigger se for um equipamento que está ativo
                if ($(el).closest('[id^=collapseEquipament-]').hasClass('show')) $(el).trigger('change');

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
            },
            complete: () => {
                $(el).attr('disabled', false);
            }
        });
    }
</script>
