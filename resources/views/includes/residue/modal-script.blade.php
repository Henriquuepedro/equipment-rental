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
                        loadResidues(response.residue_id, '.container-residues select[name="residues"]');
                    @elseif(\Request::route()->getName() == 'residue.index')
                        reloadTable();
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

        const valuesSelected = $(el).val();

        $(el).attr('disabled', true).empty();

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

                $.each(response.data, function( index, value ) {
                    selected = value.id === parseInt(residue_id_selected) || valuesSelected.includes((value.id).toString()) ? 'selected' : '';
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
            },
            complete: () => {
                $(el).attr('disabled', false).select2('destroy').select2();
            }
        });
    }
</script>
