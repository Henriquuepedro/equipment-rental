<script>
    let targetRental;
    let markerRental;
    // Where you want to render the map.
    let elementRental = document.getElementById('mapRental');
    // Create Leaflet map on map element.
    let mapRental = L.map(elementRental, {
        // fullscreenControl: true,
        // OR
        fullscreenControl: {
            pseudoFullscreen: false // if true, fullscreen to page width and height
        }
    });
    // Add OSM tile leayer to the Leaflet map.
    L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapRental);

    $(() => {
        $('[name="cep"]').mask('00.000-000');
        getLocationRental();
    });

    $(document).on('change', 'select[name="client"]', function() {
        let client_id = $(this).val();

        if (client_id == 0) {
            $('.show-address').slideUp('slow');
            $('.alert-mark-map').slideUp('slow');
            //setTimeout( () => { $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500) }, 750);
            return false;
        }

        loadAddresses(client_id);
    });

    $(document).on('change', 'select[name="name_address"]', function() {
        let address_id  = parseInt($(this).val());
        let client_id   = parseInt($('select[name="client"]').val());

        if (!address_id || !client_id) {
            return false;
        }

        loadAddress(address_id, client_id);
    });

    $(document).on('click', '#confirmAddressMap', function (){
        let verifyAddress = verifyAddressCompleteRental();
        if (!verifyAddress[0]) {
            Toast.fire({
                icon: 'warning',
                title: `Complete o cadastro do endereço, para confirmar.`
            });
            return false;
        }

        if ($('#formRental input[name="lat"]').val() === '') {
            setTimeout(() => {
                updateLocationRental($('#formRental'));
            }, 250);
        } else {
            setTimeout(() => {
                locationLatLngRental($('#formRental [name="lat"]').val(), $('#formRental [name="lng"]').val());
            }, 250);
        }

        $('#confirmAddressRental').modal();
    });

    $(document).on('click', '#updateLocationMapRental', function (){
        updateLocationRental($('#formRental'));
    });

    $("#confirmAddressRental").on("hidden.bs.modal", function () {
        if ($('[name="lat"]').val() !== '' && $('[name="lng"]').val() !== '')
            $('.alert-mark-map').slideUp('slow');
    });

    $(document).on('change', 'select[name="state"]', function(){
        loadCities($('select[name="city"]'), $(this).val());
    });

    const loadAddresses = (client_id = null) => {

        let can_selected = !$('[name="rental_id"]').length;
        $('.show-address').css('display', 'flex');
        //$('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500);
        disabledFieldAddress();
        $('select[name="name_address"]').empty().append('<option>Carregando ...</option>');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{ route('ajax.address.get-addresses') }}' + `/${client_id}`,
            dataType: 'json',
            success: response => {
                let selected;
                let nameAddress;
                let countNameAddr = 1;
                enabledFieldAddress();

                $('select[name="name_address"]').empty().append('<option value="0">Não selecionado</option>');
                $.each(response.data, function( index, value ) {
                    selected = can_selected && (value.id === client_id || response.data.length === 1) ? 'selected' : '';
                    nameAddress = value.name ?? 'Endereço #' + countNameAddr++;
                    $('select[name="name_address"]').append(`<option value='${value.id}' ${selected}>${nameAddress}</option>`);
                });

                if (can_selected) {
                    $('select[name="name_address"]').trigger('change');
                }

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
            type: 'GET',
            url: '{{ route('ajax.address.get-address') }}' + `/${client_id}/${address_id}`,
            dataType: 'json',
            success: response => {

                enabledFieldAddress();

                $('.show-address input[name="cep"]').val(response.cep ?? '');
                $('.show-address input[name="address"]').val(response.address ?? '');
                $('.show-address input[name="number"]').val(response.number ?? '');
                $('.show-address input[name="complement"]').val(response.complement ?? '');
                $('.show-address input[name="reference"]').val(response.reference ?? '');
                $('.show-address input[name="neigh"]').val(response.neigh ?? '');
                //$('.show-address select[name="city"]').val(response.city ?? '');
                //$('.show-address select[name="state"]').val(response.state ?? '');
                $('input[name="lat"]').val(response.lat ?? '');
                $('input[name="lng"]').val(response.lng ?? '');
                checkLabelAnimate();
                $('[name="cep"]').unmask().mask('00.000-000');

                if (response.address != null && response.lat == null) {
                    $('.alert-mark-map').slideDown('slow');
                }

                loadStates($('.show-address select[name="state"]'), response.state ?? '');
                loadCities($('.show-address select[name="city"]'), response.state ?? '', response.city ?? '');

                //setTimeout( () => { $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500) }, 750);

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
        $('.show-address input').each(function () {
            if (!$('[name="first_load_page"]').val()) {
                $(this).val('').attr('disabled', true).parent().removeClass('label-animate').find('label').html('Aguarde... <i class="fa fa-spinner fa-spin"></i>');
            }
        });
        $('.alert-mark-map').slideUp('slow');
    }

    const enabledFieldAddress = () => {

        $('select[name="name_address"], select[name="client"]').attr('disabled', false);
        $('.show-address input[name="cep"]').attr('disabled', false).parent().find('label').text('CEP');
        $('.show-address input[name="address"]').attr('disabled', false).parent().find('label').html('Endereço <sup>*</sup>');
        $('.show-address input[name="number"]').attr('disabled', false).parent().find('label').html('Número <sup>*</sup>');
        $('.show-address input[name="complement"]').attr('disabled', false).parent().find('label').text('Complemento');
        $('.show-address input[name="reference"]').attr('disabled', false).parent().find('label').text('Referência');
        $('.show-address input[name="neigh"]').attr('disabled', false).parent().find('label').html('Bairro <sup>*</sup>');
        $('.show-address select[name="city"]').attr('disabled', false).parent().find('label').html('Cidade <sup>*</sup>');
        $('.show-address select[name="state"]').attr('disabled', false).parent().find('label').html('Estado <sup>*</sup>');
    }

    const getLocationRental = () => {
        mapRental.on('locationfound', onLocationFoundRental);
        mapRental.on('locationerror', onLocationErrorRental);
        mapRental.locate({setView: true, maxZoom: 12});
    }

    const onLocationFoundRental = e => {
        startMarkerRental(e.latlng);
    }

    async function onLocationErrorRental(e){
        if(e.code == 1){
            const address = await deniedLocationRental();
            if(address){
                $.get(`https://dev.virtualearth.net/REST/v1/Locations?query=${address}&key=ApqqlD_Jap1C4pGj114WS4WgKo_YbBBY3yXu1FtHnJUdmCUOusnx67oS3M6UGhor`, latLng => {
                    latLng = latLng.resourceSets[0].resources[0].geocodePoints[0].coordinates;
                    latCenter = latLng[0];
                    lngCenter = latLng[1];

                    const center = L.latLng(latCenter, lngCenter);
                    startMarkerRental(center);
                });
            } else {
                startMarkerRental(L.latLng(0, 0));
            }
        }
    }

    async function deniedLocationRental(){
        return false;
        const recusouLocalizacao = true;
        const rsLocation = await $.getJSON('...',{ recusouLocalizacao }); // obter endereço empresa
        if(rsLocation != null){
            let address = rsLocation[0].address;
            address += ` - ${rsLocation[0].zipcode}`;
            address += ` - ${rsLocation[0].neigh}`;
            address += ` - ${rsLocation[0].city}`;
            address += ` - ${rsLocation[0].state}`;
            return address;
        }
        if(rsLocation == null){
            Swal.fire(
                'Localização não encontrada',
                'A solicitação para obter a localização atual foi negada pelo navegador ou occoreu um problema para encontra-la. \n\nPara obter a localização você precisa finalizar seu cadastro com o endereço da empresa para iniciarmos o mapa.',
                'warning'
            )
            return false;
        }
    }

    const startMarkerRental = latLng => {
        targetRental  = latLng;
        // icon    = L.icon({
        //     iconUrl: 'dist/img/marcadores/cacamba.png',
        //     iconSize: [40, 40],
        // });
        // marker = L.marker(target, { draggable:'true', icon }).addTo(map);
        markerRental = L.marker(targetRental, { draggable:'true' }).addTo(mapRental);
        markerRental.on('dragend', () => {
            const position = markerRental.getLatLng();
            const element = $('#formRental');
            element.find('[name="lat"]').val(position.lat);
            element.find('[name="lng"]').val(position.lng);
        });
        mapRental.setView(targetRental, 13);
        setTimeout(() => {
            mapRental.invalidateSize();
        }, 1000);
    }

    const verifyAddressCompleteRental = () => {
        cleanBorderAddressRental();

        let existError = false;

        if (!$(`[name="address"]`).val().length) {
            $(`[name="address"]`).css('border', '1px solid red');
            existError = true;
        }
        if (!$(`[name="number"]`).val().length) {
            $(`[name="number"]`).css('border', '1px solid red');
            existError = true;
        }
        if (!$(`[name="neigh"]`).val().length) {
            $(`[name="neigh"]`).css('border', '1px solid red');
            existError = true;
        }
        if (!$(`[name="city"]`).val()) {
            $(`[name="city"]`).css('border', '1px solid red');
            existError = true;
        }
        if (!$(`[name="state"]`).val()) {
            $(`[name="state"]`).css('border', '1px solid red');
            existError = true;
        }
        if (existError) return [false];

        return [true];
    }

    const cleanBorderAddressRental = () => {
        $('[name="address"]').removeAttr('style');
        $('[name="number"]').removeAttr('style');
        $('[name="neigh"]').removeAttr('style');
        $('[name="city"]').removeAttr('style');
        $('[name="state"]').removeAttr('style');
    }

    const updateLocationRental = (findDiv) => {
        const address   = findDiv.find('[name="address"]').val();
        const number    = findDiv.find('[name="number"]').val();
        const zipcode   = findDiv.find('[name="cep"]').val().replace(/[^0-9]/g, "");
        const neigh     = findDiv.find('[name="neigh"]').val();
        const city      = findDiv.find('[name="city"]').val();
        const state     = findDiv.find('[name="state"]').val();

        loadAddressMapRental(`${address},${number}-${zipcode}-${neigh}-${city}-${state}`, findDiv);
    }

    // CONSULTA LAT E LNG PELO ENDEREÇO E DEPOIS JOGA O ENDEREÇO CORRETO NO MAPA
    const loadAddressMapRental = (address, findDiv) => {
        let lat;
        let lng;
        $.get(`https://dev.virtualearth.net/REST/v1/Locations?query=${address}&key=ApqqlD_Jap1C4pGj114WS4WgKo_YbBBY3yXu1FtHnJUdmCUOusnx67oS3M6UGhor`, latLng => {
            if (!latLng.resourceSets[0].resources.length) {
                return locationLatLngRental(0,0);
            }

            latLng = latLng.resourceSets[0].resources[0].geocodePoints[0].coordinates;
            lat = latLng[0];
            lng = latLng[1];

            locationLatLngRental(lat, lng);

            findDiv.find('[name="lat"]').val(lat);
            findDiv.find('[name="lng"]').val(lng);
        });
    }

    // Atualiza mapa com a nota localização.
    const locationLatLngRental = (lat, lng) => {
        const newLatLng = new L.LatLng(lat, lng);
        markerRental.setLatLng(newLatLng);
        mapRental.setView(newLatLng, 15);
        mapRental.invalidateSize();
    }

</script>
