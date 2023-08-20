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

                    const el_select_el = $('#newVehicleModal [name="element_to_load"]').val();

                    cleanFormVehicleModal();
                    checkLabelAnimate();

                    if (el_select_el) {
                        loadVehicles(response.vehicle_id, `[name='${el_select_el}']`);
                        $('#equipments-selected [id^=collapseEquipment-] [name^="vehicle_"]').each(function () {
                            if (el_select_el != $(this).prop('name')) {
                                loadVehicles($(this).val(), `[id^="collapseEquipment-"] [name^="vehicle_"]`);
                            }
                        });

                        $('#equipments-selected [name^=withdrawal_equipment_actual_vehicle_]').each(function () {
                            if (el_select_el != $(this).prop('name')) {
                                loadVehicles($(this).val(), `[name="${$(this).prop('name')}"]`);
                            }
                        });
                    } else {
                        @if(\Request::route()->getName() == 'rental.create' || \Request::route()->getName() == 'rental.exchange' || \Request::route()->getName() == 'rental.update')
                            loadVehicles(response.vehicle_id, "div[id^='collapseEquipment-'].collapse.show [name^='vehicle_']");

                            $('#equipments-selected [id^=collapseEquipment-]').each(function () {
                                if ($("div[id^='collapseEquipment-'].collapse.show").attr('id-equipment') !== $(this).attr('id-equipment')) {
                                    loadVehicles($('[name^="vehicle_"]', this).val(), `#collapseEquipment-${$(this).attr('id-equipment')} [name^="vehicle_"]`);
                                }
                            });

                            $('#equipments-selected [name^=withdrawal_equipment_actual_vehicle_]').each(function () {
                                loadVehicles($(this).val(), `[name="${$(this).prop('name')}"]`);
                            });
                        @else
                            loadVehicles(response.vehicle_id, null);
                        @endif
                    }

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

    const loadVehicles = (vehicle_id = null, el = null, use_last_id = true) => {

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
                let vehicle_id_selected = vehicle_id ?? (use_last_id ? response.lastId : 0);

                $(el).empty().append('<option value="0">Selecione ...</option>');
                $.each(response.data, function( index, value ) {
                    selected = value.id === parseInt(vehicle_id_selected) ? 'selected' : '';
                    $(el).append(`<option value='${value.id}' ${selected}>${value.name}</option>`);
                });
                // so executo o trigger se for um equipamento que está ativo
                if ($(el).closest('[id^=collapseEquipment-]').hasClass('show')) $(el).trigger('change');

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
