<script>
    // Validar dados
    $("#formCreateResidueModal").validate({
        rules: {
            name: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Informe um nome para o resíduo'
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
            let getForm = $('#formCreateResidueModal');

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

                    console.log(response);

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

                    $('#newResidueModal').modal('hide');
                    cleanFormResidueModal();
                    checkLabelAnimate();
                    @if(\Request::route()->getName() == 'rental.create')
                        loadResidues(response.residue_id, "div[id^='collapseEquipament-'].collapse.show [name='residue[]']");

                        $('#equipaments-selected [id^=collapseEquipament-]').each(function(){
                            if ($("div[id^='collapseEquipament-'].collapse.show").attr('id-equipament') !== $(this).attr('id-equipament')) {
                                loadResidues($('[name="residue[]"]', this).val(), `#collapseEquipament-${$(this).attr('id-equipament')} [name="residue[]"]`);
                            }
                        });
                    @else
                        loadResidues(response.residue_id, null);
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

    const cleanFormResidueModal = () => {
        $('#newResidueModal [name="name"]').val('');
    }

    const loadResidues = (residue_id = null, el = null) => {

        $(el).empty().append('<option>Carregando ...</option>');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{ route('ajax.residue.get-residues') }}',
            dataType: 'json',
            success: response => {

                let selected;
                let residue_id_selected = residue_id ?? response.lastId;

                $(el).empty().append('<option>Selecione ...</option>');
                $.each(response.data, function( index, value ) {
                    selected = value.id === parseInt(residue_id_selected) ? 'selected' : '';
                    $(el).append(`<option value='${value.id}' ${selected}>${value.name}</option>`);
                });

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
</script>
