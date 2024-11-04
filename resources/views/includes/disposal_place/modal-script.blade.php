<script>
    const loadDisposalPlaces = (disposal_place = null, el = null, use_last_id = true) => {

        $(el).attr('disabled', true).empty().append('<option>Carregando ...</option>');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{ route('ajax.disposal_place.get-disposal-places') }}',
            dataType: 'json',
            success: response => {
                let selected;
                let disposal_place_selected = disposal_place ?? (use_last_id ? response.lastId : 0);

                $(el).empty().append('<option value="0">Selecione ...</option>');
                $.each(response.data, function( index, value ) {
                    selected = value.id === parseInt(disposal_place_selected) ? 'selected' : '';
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
                $(el).attr('disabled', false);
            }
        });
    }
</script>
