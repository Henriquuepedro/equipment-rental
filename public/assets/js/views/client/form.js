var latlng;
var map;
var marker;
var target;
var icon;
var element;

$(() => {
    $('[name="cep[]"]').mask('00.000-000');
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
// Where you want to render the map.
element = document.getElementById('map');
// Create Leaflet map on map element.
map = L.map(element, {
    // fullscreenControl: true,
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

jQuery.validator.addMethod("cpf_cnpj", function(value, element) {
    value = jQuery.trim(value);

    let retorno =  $('[name="type_person"]:checked').val() === 'pf' ? validCPF(value) : validCNPJ(value);

    return this.optional(element) || retorno;

}, 'Informe um CPF/CNPJ válido');

$('#add-new-address').on('click', function () {

    const verifyAddress = verifyAddressComplet();
    if (!verifyAddress[0]) {
        Toast.fire({
            icon: 'warning',
            title: `Finalize o cadastro do ${verifyAddress[1]}º endereço, para adicionar um novo.`
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
                    <button class="btn btn-link btn-address no-sublime" type="button">
                        Novo Endereço
                    </button>
                    <div class="col-md-2 text-right no-padding">
                        <button type="button" class="btn btn-primary edit-address display-none"><i class="fa fa-edit"></i></button>
                        <button type="button" class="btn btn-danger remove-address"><i class="fa fa-trash"></i></button>
                    </div>
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
                    <div class="form-group d-flex justify-content-between flex-wrap col-md-12">
                        <button type="button" class="btn btn-link confirm-map text-center"><i class="fas fa-map-marked-alt"></i> Confirmar Endereço no Mapa</button>
                        <button type="button" class="btn btn-success text-center save-address"><i class="fa fa-save"></i> Salvar</button>
                    </div>
                </div>
                <input type="hidden" name="lat[]" />
                <input type="hidden" name="lng[]" />
            </div>
        </div>
        `).find('.box').slideDown('slow');

    $('[name="cep[]"]').mask('00.000-000');
});

$(document).on('click', '.edit-address', function(){
    if ($('.box').find('.box-body:visible').length) {
        Toast.fire({
            icon: 'warning',
            title: `Salve o cadastro de endereço em aberto para editar um próximo.`
        });
        return false;
    }
    $(this).closest('.box').find('.box-body').slideDown('slow');
})

$(document).on('click', '.save-address', function(){
    let verifyAddress = verifyAddressComplet();
    if (!verifyAddress[0]) {
        Toast.fire({
            icon: 'warning',
            title: `Complete o cadastro do novo endereço para salvar.`
        });
        return false;
    }

    const el = $(this).closest('.box');

    const name_control = el.find('[name="name_address[]"]').val();
    const cep = el.find('[name="cep[]"]').val();
    const address = el.find('[name="address[]"]').val();
    const number = el.find('[name="number[]"]').val();
    const neigh = el.find('[name="neigh[]"]').val();
    const city = el.find('[name="city[]"]').val();
    const state = el.find('[name="state[]"]').val();
    el.find('.edit-address').show();
    el.find('.box-body').slideUp('slow');
    el.find('.btn-address').html(`<i class="fa fa-caret-right"></i> <strong>${name_control} |</strong> ${cep} - ${address}, ${number} - ${neigh} - ${city} - ${state}`);
});

$(document).on('click', '.remove-address', function (){
    $(this).closest('.box').slideUp(500);
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
