<script>
    $(() => {
        $('[name="cep"]').mask('00.000-000');
    });

    $('select[name="client"]').on('change', function() {
        let client_id = $(this).val();

        if (client_id == 0) {
            $('.show-address').slideUp('slow');
            $('.alert-mark-map').slideUp('slow');
            setTimeout( () => { $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500) }, 750);
            return false;
        }

        loadAddresses(client_id);
    });

    $('select[name="name_address"]').on('change', function() {
        let address_id = $(this).val();
        let client_id = $('select[name="client"]').val();

        if (!address_id || !client_id) return false;

        loadAddress(address_id, client_id);
    });

    const loadAddresses = (client_id = null) => {

        $('.show-address').css('display', 'flex');
        $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500);
        disabledFieldAddress();
        $('select[name="name_address"]').empty().append('<option>Carregando ...</option>');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: '{{ route('ajax.address.get-addresses') }}',
            data: { client_id },
            dataType: 'json',
            success: response => {
                let selected;
                let nameAddress;
                let countNameAddr = 1;
                enabledFieldAddress();

                $('select[name="name_address"]').empty().append('<option value="0">Não selecionado</option>');
                $.each(response.data, function( index, value ) {
                    selected = value.id === client_id ? 'selected' : '';
                    nameAddress = value.name ?? 'Endereço #' + countNameAddr++;
                    $('select[name="name_address"]').append(`<option value='${value.id}' ${selected}>${nameAddress}</option>`);
                });

            }, error: e => {
                enabledFieldAddress();
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

    const loadAddress = (address_id, client_id) => {

        disabledFieldAddress();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: '{{ route('ajax.address.get-address') }}',
            data: { address_id, client_id },
            dataType: 'json',
            success: response => {

                enabledFieldAddress();

                $('input[name="cep"]').val(response.cep ?? '');
                $('input[name="address"]').val(response.address ?? '');
                $('input[name="number"]').val(response.number ?? '');
                $('input[name="complement"]').val(response.complement ?? '');
                $('input[name="reference"]').val(response.reference ?? '');
                $('input[name="neigh"]').val(response.neigh ?? '');
                $('input[name="city"]').val(response.city ?? '');
                $('input[name="state"]').val(response.state ?? '');
                checkLabelAnimate();
                $('[name="cep"]').unmask().mask('00.000-000');

                if (response.address != null && response.lat == null) $('.alert-mark-map').slideDown('slow');

                setTimeout( () => { $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500) }, 750);

            }, error: e => {

                enabledFieldAddress();

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

    const disabledFieldAddress = () => {
        $('select[name="name_address"], select[name="client"]').attr('disabled', true);
        $('.show-address input').each(function (){
            $(this).val('').attr('disabled', true).parent().removeClass('label-animate').find('label').html('Aguarde... <i class="fa fa-spinner fa-spin"></i>');
        });
        $('.alert-mark-map').slideUp('slow');
    }

    const enabledFieldAddress = () => {

        $('select[name="name_address"], select[name="client"]').attr('disabled', false);
        $('input[name="cep"]').attr('disabled', false).parent().find('label').text('CEP');
        $('input[name="address"]').attr('disabled', false).parent().find('label').text('Endereço');
        $('input[name="number"]').attr('disabled', false).parent().find('label').text('Número');
        $('input[name="complement"]').attr('disabled', false).parent().find('label').text('Complemento');
        $('input[name="reference"]').attr('disabled', false).parent().find('label').text('Referência');
        $('input[name="neigh"]').attr('disabled', false).parent().find('label').text('Bairro');
        $('input[name="city"]').attr('disabled', false).parent().find('label').text('Cidade');
        $('input[name="state"]').attr('disabled', false).parent().find('label').text('Estado');
    }

</script>
