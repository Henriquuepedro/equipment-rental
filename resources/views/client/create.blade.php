@extends('adminlte::page')

@section('title', 'Cadastro de Cliente')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Cliente</h1>
@stop

@section('css')
@stop

@section('js')
<script>
    $(() => {
        $('[name="cep"]').mask('00.000-000');
        $('[name="phone_1"],[name="phone_2"]').mask('(00) 000000000');
        $('[name="rg_ie"]').mask('0#');
        if ($('[name="type_person"]:checked').length) {
            $('[name="type_person"]:checked').trigger('change');
            $(".form-control").each(function() {
                if ($(this).val() != '')
                    $(this).parent().addClass("label-animate");
            });
        }
        getLocation();
    });

    $('[name="type_person"]').on('change', function(){
        const type = $(this).val();

        if (type === 'pf') {
            $('label[for="name_client"]').html('Nome do Cliente <sup>*</sup>');
            $('#name_client').closest('.form-group').removeClass('col-md-6').addClass('col-md-12');
            $('label[for="cpf_cnpj"]').text('CPF');
            $('label[for="rg_ie"]').text('RG');
            $('#fantasy_client').val('').closest('.form-group').addClass('d-none');
            $('[name="cpf_cnpj"]').mask('000.000.000-00');
        }
        else if (type === 'pj') {
            $('label[for="name_client"]').html('Razão Social <sup>*</sup>');
            $('#name_client').closest('.form-group').removeClass('col-md-12').addClass('col-md-6');
            $('label[for="cpf_cnpj"]').text('CNPJ');
            $('label[for="rg_ie"]').text('IE');
            $('#fantasy_client').closest('.form-group').removeClass('d-none');
            $('[name="cpf_cnpj"]').mask('00.000.000/0000-00');
        }

        $(".card").each(function() {
            $(this).slideDown('slow');
        });
    });

    $(document).on('blur', '[name="cep[]"]', function (){
        const cep = $(this).val().replace(/\D/g, '');
        let el;
        if ($(this).closest('#new-addressses').length)
            el = $(this).closest('.box');
        else
            el = $(this).closest('.card-body');

        if (cep.length === 0) return false;
        if (cep.length !== 8) {
            Toast.fire({
                icon: 'error',
                title: 'CEP não encontrado'
            });
            return false;
        }
        $.getJSON("https://viacep.com.br/ws/"+ cep +"/json/", function(dados) {

            if (!("erro" in dados)) {
                if(dados.logradouro !== '') el.find('[name^="address"]').val(dados.logradouro).parent().addClass("label-animate");
                if(dados.bairro !== '')     el.find('[name="neigh[]"]').val(dados.bairro).parent().addClass("label-animate");
                if(dados.localidade !== '') el.find('[name="city[]"]').val(dados.localidade).parent().addClass("label-animate");
                if(dados.uf !== '')         el.find('[name="state[]"]').val(dados.uf).parent().addClass("label-animate");
            } //end if.
            else {
                Toast.fire({
                    icon: 'error',
                    title: 'CEP não encontrado'
                })
            }
        });
    })

    // Validar dados
    const container = $("div.error-form");
    // validate the form when it is submitted
    $("#formCreateClient").validate({
        errorContainer: container,
        errorLabelContainer: $("ol", container),
        wrapper: 'li',
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
            $('html, body').animate({scrollTop:0}, 'slow');
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

            $('#formCreateClient [type="submit"]').attr('disabled', true);
            form.submit();
        }
    });

    jQuery.validator.addMethod("cpf_cnpj", function(value, element) {
        value = jQuery.trim(value);

        let retorno =  $('[name="type_person"]:checked').val() === 'pf' ? validCPF(value) : validCNPJ(value);

        return this.optional(element) || retorno;

    }, $('[name="type_person"]:checked').val() === 'pf' ? 'Informe um CPF válido' : 'Informe um CNPJ válido');

    $('#add-new-address').on('click', function () {

        const verifyAddress = verifyAddressComplet();
        if (!verifyAddress[0]) {
            Toast.fire({
                icon: 'warning',
                title: `Finalize o cadastro do ${verifyAddress[1]}º endereço, para finalizar o cadastro.`
            });
            return false;
        }
        // esconde os outros endereços
        $('.box-body:visible').parent().find('.box-header .btn-address').trigger('click');

        let countAddress = 0;
        countAddress = $('#new-addressses [name="name_address[]"]').length + 1;

        $('#new-addressses').append(`
        <div class="box box-primary display-none">
            <div class="box-header">
                <h5 class="mb-0 d-flex justify-content-between">
                    <button class="btn btn-link btn-address" type="button" data-widget="collapse">
                        <i class="fa fa-caret-right"></i> ${countAddress}º Novo Endereço
                    </button>
                    <button type="button" class="btn btn-danger remove-address"><i class="fa fa-trash"></i></button>
                </h5>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>Nome de Controle</label>
                        <input type="text" class="form-control" name="name_address[]" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>CEP</label>
                        <input type="text" class="form-control" name="cep[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Endereço</label>
                        <input type="text" class="form-control" name="address[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Número</label>
                        <input type="text" class="form-control" name="number[]" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Complemento</label>
                        <input type="text" class="form-control" name="complement[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Referência</label>
                        <input type="text" class="form-control" name="reference[]" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Bairro</label>
                        <input type="text" class="form-control" name="neigh[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Cidade</label>
                        <input type="text" class="form-control" name="city[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Estado</label>
                        <input type="text" class="form-control" name="state[]" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12 text-center">
                        <button type="button" class="btn btn-link confirm-map text-center"><i class="fas fa-map-marked-alt"></i> Confirmar Endereço no Mapa</button>
                    </div>
                </div>
                <input type="hidden" name="lat[]" />
                <input type="hidden" name="lng[]" />
            </div>
        </div>
        `).find('.box').slideDown('slow');

        if ($('.box').length !== 0) $('#no-have-address').slideUp(500);
        $('[name="cep[]"]').mask('00.000-000');
    });

    $(document).on('click', '.remove-address', function (){
        $(this).closest('.box').slideUp(500);
        setTimeout(() => { if ($('.box:visible').length === 0) $('#no-have-address').slideDown(500) }, 600);
        setTimeout(() => { $(this).closest('.box').remove() }, 500);
    });

    $(document).on('click', '.confirm-map', function (){
        let verifyAddress = verifyAddressComplet();
        if (!verifyAddress[0]) {
            Toast.fire({
                icon: 'warning',
                title: `Complete o cadastro do ${verifyAddress[1]}º endereço, para confirmar seu endereço.`
            });
            return false;
        }

        if ($(this).parents('.box-body').find('[name="lat[]"]').val() === "") {
            setTimeout(() => { updateLocation($(this).parents('.box-body')) }, 500);
        } else {
            setTimeout(() => { locationLatLng($(this).parents('.box-body').find('[name="lat[]"]').val(), $(this).parents('.box-body').find('[name="lng[]"]').val()) }, 500);
        }
        $('#confirmAddress').modal();
        $(this).attr('data-map-active','true');
    });

    $('#confirmAddress').on('hidden.bs.modal', function () {
        $('[data-map-active="true"]').removeAttr('data-map-active');
    });

    $('#updateLocationMap').click(function (){
        const element = $('[data-map-active="true"]').parents('.box-body');
        updateLocation(element);
    })

    const verifyAddressComplet = () => {
        cleanBorderAddress();

        const addrCount = $('.box').length;
        let existError = false;
        for (let countAddr = 0; countAddr < addrCount; countAddr++) {


            if (!$(`[name="name_address[]"]:eq(${countAddr})`).val().length) {
                $(`[name="name_address[]"]:eq(${countAddr})`).css('border', '1px solid red');
                existError = true;
            }
            if (!$(`[name="address[]"]:eq(${countAddr})`).val().length) {
                $(`[name="address[]"]:eq(${countAddr})`).css('border', '1px solid red');
                existError = true;
            }
            if (!$(`[name="number[]"]:eq(${countAddr})`).val().length) {
                $(`[name="number[]"]:eq(${countAddr})`).css('border', '1px solid red');
                existError = true;
            }
            if (!$(`[name="neigh[]"]:eq(${countAddr})`).val().length) {
                $(`[name="neigh[]"]:eq(${countAddr})`).css('border', '1px solid red');
                existError = true;
            }
            if (!$(`[name="city[]"]:eq(${countAddr})`).val().length) {
                $(`[name="city[]"]:eq(${countAddr})`).css('border', '1px solid red');
                existError = true;
            }
            if (!$(`[name="state[]"]:eq(${countAddr})`).val().length) {
                $(`[name="state[]"]:eq(${countAddr})`).css('border', '1px solid red');
                existError = true;
            }
            if (existError) return [false, (countAddr + 1)];
        }
        return [true];
    }

    const cleanBorderAddress = () => {
        $('[name="name_address[]"]').removeAttr('style');
        $('[name="address[]"]').removeAttr('style');
        $('[name="number[]"]').removeAttr('style');
        $('[name="neigh[]"]').removeAttr('style');
        $('[name="city[]"]').removeAttr('style');
        $('[name="state[]"]').removeAttr('style');
    }

    var latlng;
    var map;
    var marker;
    var target;
    var icon;
    var element;
    // Where you want to render the map.
    element = document.getElementById('map');
    // Create Leaflet map on map element.
    map = L.map(element, {
        fullscreenControl: true,
        // OR
        fullscreenControl: {
            pseudoFullscreen: false // if true, fullscreen to page width and height
        }
    });
    // Add OSM tile leayer to the Leaflet map.
    L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    // VERIFICAR SE HAVERÁ ALGUM ERRO
    const getLocation = () => {
        map.on('locationfound', onLocationFound);
        map.on('locationerror', onLocationError);
        map.locate({setView: true, maxZoom: 12});
    }
    // Callback success getLocation
    const onLocationFound = e => {
        startMarker(e.latlng);
    }
    // Callback error getLocation
    async function onLocationError(e){
        if(e.code == 1){
            const address = await deniedLocation();
            if(address){
                $.get(`https://dev.virtualearth.net/REST/v1/Locations?query=${address}&key=ApqqlD_Jap1C4pGj114WS4WgKo_YbBBY3yXu1FtHnJUdmCUOusnx67oS3M6UGhor`, latLng => {
                    latLng = latLng.resourceSets[0].resources[0].geocodePoints[0].coordinates;
                    latCenter = latLng[0];
                    lngCenter = latLng[1];

                    const center = L.latLng(latCenter, lngCenter);
                    startMarker(center);
                });
            }
        }
    }
    // MOSTRAR MAP APÓS NEGAÇÃO DO BROWSER
    async function deniedLocation(){
        const recusouLocalizacao = true;
        const rsLocation = await $.getJSON('...',{ recusouLocalizacao }); // obter endereço empresa
        if(rsLocation != null){
            let endereco = rsLocation[0].CENDERECO;
            endereco += ` - ${rsLocation[0].NCEP}`;
            endereco += ` - ${rsLocation[0].CBAIRRO}`;
            endereco += ` - ${rsLocation[0].CCIDADE}`;
            endereco += ` - ${rsLocation[0].CESTADO}`;
            return endereco;
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
    const startMarker = latLng => {
        target  = latLng;
        // icon    = L.icon({
        //     iconUrl: 'dist/img/marcadores/cacamba.png',
        //     iconSize: [40, 40],
        // });
        // marker = L.marker(target, { draggable:'true', icon }).addTo(map);
        marker = L.marker(target, { draggable:'true' }).addTo(map);
        marker.on('dragend', () => {

            const position = marker.getLatLng();
            const element = $('[data-map-active="true"]').parents('.box-body');
            element.find('[name="lat[]"]').val(position.lat);
            element.find('[name="lng[]"]').val(position.lng);
        });
        map.setView(target, 13);
        setTimeout(() => {
            map.invalidateSize();
        }, 1000);
    }
    const updateLocation = (findDiv) => {
        const endereco  = findDiv.find('[name="address[]"]').val();
        const numero    = findDiv.find('[name="number[]"]').val();
        const cep       = findDiv.find('[name="cep[]"]').val().replace(/[^0-9]/g, "");
        const bairro    = findDiv.find('[name="neigh[]"]').val();
        const cidade    = findDiv.find('[name="city[]"]').val();
        const estado    = findDiv.find('[name="state[]"]').val();

        loadAddressMap(`${endereco},${numero}-${cep}-${bairro}-${cidade}-${estado}`, findDiv);
    }
    // Atualiza mapa com a nota localização
    const locationLatLng = (lat, lng) => {
        const newLatLng = new L.LatLng(lat, lng);
        marker.setLatLng(newLatLng);
        map.setView(newLatLng, 15);
        map.invalidateSize();
    }
    // CONSULTA LAT E LNG PELO ENDEREÇO E DEPOIS JOGA O ENDEREÇO CORRETO NO MAPA
    const loadAddressMap = (address, findDiv) => {
        let lat;
        let lng;
        $.get(`https://dev.virtualearth.net/REST/v1/Locations?query=${address}&key=ApqqlD_Jap1C4pGj114WS4WgKo_YbBBY3yXu1FtHnJUdmCUOusnx67oS3M6UGhor`, latLng => {
            if (!latLng.resourceSets[0].resources.length) return locationLatLng(0,0);

            latLng = latLng.resourceSets[0].resources[0].geocodePoints[0].coordinates;
            lat = latLng[0];
            lng = latLng[1];

            locationLatLng(lat, lng);

            findDiv.find('[name="lat[]"]').val(lat);
            findDiv.find('[name="lng[]"]').val(lng);
        });
    }

    $(document).on('keydown', function(e){
        if(e.keyCode == 13){
            return false;
        }
    });
</script>
@stop

@section('content')

    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    <div class="error-form alert alert-warning {{ count($errors) == 0 ? 'display-none' : '' }}">
                        <h5>Existem erros no envio do formulário, veja abaixo para corrigi-los.</h5>
                        <ol>
                            @foreach($errors->all() as $error)
                                <li><label id="name-error" class="error">{{ $error }}</label></li>
                            @endforeach
                        </ol>
                    </div>
                    <form action="{{ route(('client.insert')) }}" method="POST" enctype="multipart/form-data" id="formCreateClient">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_person" value="pf" @if(old('type_person') === 'pf') checked @endif> Pessoa Física <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_person" value="pj" @if(old('type_person') === 'pj') checked @endif> Pessoa Jurídica <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Cliente</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações do novo cliente </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="name_client">Nome do Cliente <sup>*</sup></label>
                                        <input type="text" class="form-control" id="name_client" name="name_client" autocomplete="nope" value="{{ old('name_client') }}" required>
                                    </div>
                                    <div class="form-group col-md-6 d-none">
                                        <label for="fantasy_client">Fantasia</label>
                                        <input type="text" class="form-control" id="fantasy_client" name="fantasy_client" autocomplete="nope" value="{{ old('fantasy_client') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="cpf_cnpj">CPF</label>
                                        <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope" value="{{ old('cpf_cnpj') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="rg_ie">RG</label>
                                        <input type="text" class="form-control" id="rg_ie" name="rg_ie" autocomplete="nope" value="{{ old('rg_ie') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_1">Telefone Principal</label>
                                        <input type="text" class="form-control" id="phone_1" name="phone_1" autocomplete="nope" value="{{ old('phone_1') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="contact">Contato</label>
                                        <input type="text" class="form-control" id="contact" name="contact" autocomplete="nope" value="{{ old('contact') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_2">Telefone Secundário</label>
                                        <input type="text" class="form-control" id="phone_2" name="phone_2" autocomplete="nope" value="{{ old('phone_2') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email">Endereço de E-mail</label>
                                        <input type="email" class="form-control" id="email" name="email" autocomplete="nope" value="{{ old('email') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="observation">Observação</label>
                                        <textarea class="form-control" id="observation" name="observation" rows="3">{{ old('observation') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Endereço</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações de endereço </p>
                                </div>
                                <div class="accordion form-group" id="accordionAddress">
                                    @if (old('name_address') && count(old('name_address')))
                                        @for($addr = 0; $addr < count(old('name_address')); $addr++)
                                            @php
                                                $numberNewAddress = $addr + 1;
                                            @endphp
                                            <div class="box collapsed-box box-primary">
                                                <div class="box-header">
                                                    <h5 class="mb-0 d-flex justify-content-between">
                                                        <button class="btn btn-link" type="button" data-widget="collapse">
                                                            <i class="fa fa-caret-right"></i> {{ empty(old('name_address')[$addr]) ? $numberNewAddress.'º Novo Endereço' : old('name_address')[$addr] }}
                                                        </button>
                                                        <button type="button" class="btn btn-danger remove-address"><i class="fa fa-trash"></i></button>
                                                    </h5>
                                                </div>
                                                <div class="box-body display-none">
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label>Nome de Controle</label>
                                                            <input type="text" class="form-control" name="name_address[]" autocomplete="nope" value="{{ old('name_address')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-3">
                                                            <label>CEP</label>
                                                            <input type="text" class="form-control" name="cep[]" autocomplete="nope" value="{{ old('cep')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Endereço</label>
                                                            <input type="text" class="form-control" name="address[]" autocomplete="nope" value="{{ old('address')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label>Número</label>
                                                            <input type="text" class="form-control" name="number[]" autocomplete="nope" value="{{ old('number')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label>Complemento</label>
                                                            <input type="text" class="form-control" name="complement[]" autocomplete="nope" value="{{ old('complement')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Referência</label>
                                                            <input type="text" class="form-control" name="reference[]" autocomplete="nope" value="{{ old('reference')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label>Bairro</label>
                                                            <input type="text" class="form-control" name="neigh[]" autocomplete="nope" value="{{ old('neigh')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Cidade</label>
                                                            <input type="text" class="form-control" name="city[]" autocomplete="nope" value="{{ old('city')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Estado</label>
                                                            <input type="text" class="form-control" name="state[]" autocomplete="nope" value="{{ old('state')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-12 text-center">
                                                            <button type="button" class="btn btn-link confirm-map text-center"><i class="fas fa-map-marked-alt"></i> Confirmar Endereço no Mapa</button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lat[]" value="{{ old('lat')[$addr] }}"/>
                                                    <input type="hidden" name="lng[]" value="{{ old('lng')[$addr] }}"/>
                                                </div>
                                            </div>
                                        @endfor
                                    @endif
                                    <div class="alert alert-warning {{old('name_address')?'display-none':''}}" id="no-have-address"><h4 class="text-center">Não existem endereços ainda.</h4></div>
                                    <div id="new-addressses"></div>
                                </div>
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-primary" id="add-new-address">Adicionar Novo Endereço</button>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('client.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="confirmAddress">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Endereço no Mapa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 form-group text-center">
                                <button type="button" class="btn btn-primary" id="updateLocationMap"><i class="fas fa-sync-alt"></i> Atualizar Localização</button>
                            </div>
                        </div>
                        <div class="row">
                            <div id="map" style="height: 400px"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary col-md-3" data-dismiss="modal">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
