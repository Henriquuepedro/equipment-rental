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
    }
    showHideTableAddress();
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
        const element = $('[data-map-active="true"]').parents('td, .box-body');
        element.find('[name="lat[]"], #lat_new').val(position.lat);
        element.find('[name="lng[]"], #lng_new').val(position.lng);
    });
    map.setView(target, 13);
    setTimeout(() => {
        map.invalidateSize();
    }, 1000);
}
const updateLocation = (findDiv) => {
    const endereco  = findDiv.find('[name="address[]"], #address_new').val();
    const numero    = findDiv.find('[name="number[]"], #number_new').val();
    const cep       = findDiv.find('[name="cep[]"], #cep_new').val().replace(/[^0-9]/g, "");
    const bairro    = findDiv.find('[name="neigh[]"], #neigh_new').val();
    const cidade    = findDiv.find('[name="city[]"], #city_new').val();
    const estado    = findDiv.find('[name="state[]"], #state_new').val();

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

        findDiv.find('[name="lat[]"], #lat_new').val(lat);
        findDiv.find('[name="lng[]"], #lng_new').val(lng);
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

$(document).on('blur', '[name="cep[]"], #cep_new', function (){
    const cep = $(this).val().replace(/\D/g, '');
    let el;
    if ($(this).closest('#new-addressses').length)
        el = $(this).closest('.box, td');
    else
        el = $(this).closest('.card-body, td');

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
            if(dados.logradouro !== '') el.find('[name^="address"], #address_new').val(dados.logradouro).parent().addClass("label-animate");
            if(dados.bairro !== '')     el.find('[name="neigh[]"], #neigh_new').val(dados.bairro).parent().addClass("label-animate");
            if(dados.localidade !== '') el.find('[name="city[]"], #city_new').val(dados.localidade).parent().addClass("label-animate");
            if(dados.uf !== '')         el.find('[name="state[]"], #state_new').val(dados.uf).parent().addClass("label-animate");
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
    if (!verifyAddress[0] || $('.new-address-show').length || $('table tr td[colspan="5"]:visible').length) {
        Toast.fire({
            icon: 'warning',
            title: `Finalize o cadastro/alteração do endereço, para adicionar um novo.`
        });
        return false;
    }
    // esconde os outros endereços
    $('.box-body:visible').parent().find('.box-header .btn-address').trigger('click');

    $('#new-addressses').append(`
        <div class="box box-primary display-none mt-3 new-address-show">
            <div class="box-header">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span>
                        Novo Endereço
                    </span>
                    <div class="col-md-2 text-right no-padding">
                        <button type="button" class="btn btn-danger remove-new-address"><i class="fa fa-times"></i> Cancelar</button>
                    </div>
                </h5>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>Identificação do Endereço</label>
                        <input type="text" class="form-control" id="name_address_new" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>CEP</label>
                        <input type="text" class="form-control" id="cep_new" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Endereço</label>
                        <input type="text" class="form-control" id="address_new" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Número</label>
                        <input type="text" class="form-control" id="number_new" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Complemento</label>
                        <input type="text" class="form-control" id="complement_new" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Referência</label>
                        <input type="text" class="form-control" id="reference_new" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Bairro</label>
                        <input type="text" class="form-control" id="neigh_new" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Cidade</label>
                        <input type="text" class="form-control" id="city_new" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Estado</label>
                        <input type="text" class="form-control" id="state_new" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group d-flex justify-content-between flex-wrap col-md-12 mt-2">
                        <button type="button" class="btn btn-link confirm-map text-center"><i class="fas fa-map-marked-alt"></i> Confirmar Endereço no Mapa</button>
                        <button type="button" class="btn btn-success text-center save-new-address"><i class="fa fa-plus"></i> Adicionar</button>
                    </div>
                </div>
                <input type="hidden" id="lat_new" />
                <input type="hidden" id="lng_new" />
            </div>
        </div>
    `).find('.box').slideDown('slow');

    $('[id="cep_new"]').mask('00.000-000');
});

$(document).on('click', '.edit-address', function(){

    if (!verifyAddressComplet()[0]) return false;

    if ($(this).closest('tr').next().is(':not(:visible)')) {
        if ($('table tr td[colspan="5"]:visible').length) {
            Toast.fire({
                icon: 'warning',
                title: `Retorne o cadastro de endereço aberto para editar um próximo.`
            });
            return false;
        }
    }

    $(this).closest('tr').next().toggle('slow').find('.address').slideToggle('slow');
})
$(document).on('click', '.save-address', function(){
    if (!verifyAddressComplet()[0]) return false;

    $(this).closest('tr').toggle('slow').find('.address').slideToggle('slow');
})

$(document).on('click', '.save-address, .edit-address', function(event){

    let el;

    // linha com o formulario
    if (!$(event.target).is('.save-address')) el = $(this).closest('tr').next();
    else el = $(this).closest('tr');

    let verifyAddress = verifyAddressComplet();
    if (!verifyAddress[0]) {
        Toast.fire({
            icon: 'warning',
            title: `Complete o cadastro do endereço para retornar.`
        });
        return false;
    }


    const name_control = el.find('[name="name_address[]"]').val();
    const cep = el.find('[name="cep[]"]').val();
    const address = el.find('[name="address[]"]').val();
    const number = el.find('[name="number[]"]').val();
    const neigh = el.find('[name="neigh[]"]').val();
    const city = el.find('[name="city[]"]').val();
    const state = el.find('[name="state[]"]').val();

    el = el.prev(); // volta a linha da tabela
    el.find('td:eq(0)').text(name_control);
    el.find('td:eq(1)').text(cep);
    el.find('td:eq(2)').text(`${address}, ${number} - ${neigh}`);
    el.find('td:eq(3)').text(`${city} - ${state}`);
});

$(document).on('click', '.save-new-address', function(event){

    let el = $(this).closest('.box');

    let verifyAddress = verifyAddressComplet();
    if (!verifyAddress[0]) {
        Toast.fire({
            icon: 'warning',
            title: `Complete o cadastro do endereço para retornar.`
        });
        return false;
    }


    const name_control = el.find('#name_address_new').val();
    const cep = el.find('#cep_new').val();
    const address = el.find('#address_new').val();
    const number = el.find('#number_new').val();
    const complement = el.find('#complement_new').val();
    const reference = el.find('#reference_new').val();
    const neigh = el.find('#neigh_new').val();
    const city = el.find('#city_new').val();
    const state = el.find('#state_new').val();
    const lat = el.find('#lat_new').val();
    const lng = el.find('#lng_new').val();

    createNewAddress(name_control, cep, address, number, complement, reference, neigh, city, state, lat, lng);
    el.find('.remove-new-address').trigger('click');

    checkLabelAnimate();
    showHideTableAddress();
});

$(document).on('click', '.remove-new-address', function (){
    $(this).closest('.box').slideUp(500);
    setTimeout(() => { $(this).closest('.box').remove(); }, 500);
});
$(document).on('click', '.remove-address', function (){
    $(this).closest('tr').hide('slow').next().find('.address').slideUp('slow');
    setTimeout(() => {
        $(this).closest('tr').next().remove();
        $(this).closest('tr').remove();
        showHideTableAddress();
    }, 500);
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

    if ($(this).parents('td, .box-body').find('[name="lat[]"], #lat_new').val() === "") {
        setTimeout(() => { updateLocation($(this).parents('td, .box-body')) }, 500);
    } else {
        setTimeout(() => { locationLatLng($(this).parents('td, .box-body').find('[name="lat[]"], #lat_new').val(), $(this).parents('td, .box-body').find('[name="lng[]"], #lng_new').val()) }, 500);
    }
    $('#confirmAddress').modal();
    $(this).attr('data-map-active','true');
});

$('#confirmAddress').on('hidden.bs.modal', function () {
    $('[data-map-active="true"]').removeAttr('data-map-active');
});

$('#updateLocationMap').click(function (){
    const element = $('[data-map-active="true"]').parents('td, .box-body');
    updateLocation(element);
})

const verifyAddressComplet = () => {
    cleanBorderAddress();

    const addrCount = $('.address').length;
    let existError = false;

    //erros novo endereço
    if ($('#address_new').val() !== undefined && !$('#address_new').val().length) {
        $('#address_new').css('border', '1px solid red');
        existError = true;
    }
    if ($('#number_new').val() !== undefined && !$('#number_new').val().length) {
        $('#number_new').css('border', '1px solid red');
        existError = true;
    }
    if ($('#neigh_new').val() !== undefined && !$('#neigh_new').val().length) {
        $('#neigh_new').css('border', '1px solid red');
        existError = true;
    }
    if ($('#city_new').val() !== undefined && !$('#city_new').val().length) {
        $('#city_new').css('border', '1px solid red');
        existError = true;
    }
    if ($('#state_new').val() !== undefined && !$('#state_new').val().length) {
        $('#state_new').css('border', '1px solid red');
        existError = true;
    }
    if (existError) return [false, 1];

    for (let countAddr = 0; countAddr < addrCount; countAddr++) {

        // if (!$(`[name="name_address[]"]:eq(${countAddr})`).val().length) {
        //     $(`[name="name_address[]"]:eq(${countAddr})`).css('border', '1px solid red');
        //     existError = true;
        // }
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
    // $('[name="name_address[]"]').removeAttr('style');
    $('[name="address[]"], #address_new').removeAttr('style');
    $('[name="number[]"], #number_new').removeAttr('style');
    $('[name="neigh[]"], #neigh_new').removeAttr('style');
    $('[name="city[]"], #city_new').removeAttr('style');
    $('[name="state[]"], #state_new').removeAttr('style');
}

const createNewAddress = (name_address, cep, address, number, complement, reference, neigh, city, state, lat, lng) => {
    $('table tbody').append(`
    <tr>
        <td>${name_address}</td>
        <td>${cep}</td>
        <td>${address}, ${number} - ${neigh}</td>
        <td>${city} - ${state}</td>
        <td>
            <button type="button" class="btn btn-primary edit-address btn-sm btn-rounded btn-action pull-left"><i class="fa fa-edit"></i></button>
            <button type="button" class="btn btn-danger remove-address btn-sm btn-rounded btn-action"><i class="fa fa-trash"></i></button>
        </td>
    </tr>
    <tr class="display-none">
        <td colspan="5">
            <div class="address display-none">
                <div class="row mt-3">
                    <div class="form-group col-md-12">
                        <label>Identificação do Endereço</label>
                        <input type="text" class="form-control" name="name_address[]" autocomplete="nope" value="${name_address}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>CEP</label>
                        <input type="text" class="form-control" name="cep[]" autocomplete="nope" value="${cep}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Endereço</label>
                        <input type="text" class="form-control" name="address[]" autocomplete="nope" value="${address}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Número</label>
                        <input type="text" class="form-control" name="number[]" autocomplete="nope" value="${number}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Complemento</label>
                        <input type="text" class="form-control" name="complement[]" autocomplete="nope" value="${complement}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Referência</label>
                        <input type="text" class="form-control" name="reference[]" autocomplete="nope" value="${reference}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Bairro</label>
                        <input type="text" class="form-control" name="neigh[]" autocomplete="nope" value="${neigh}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Cidade</label>
                        <input type="text" class="form-control" name="city[]" autocomplete="nope" value="${city}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Estado</label>
                        <input type="text" class="form-control" name="state[]" autocomplete="nope" value="${state}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group d-flex justify-content-between flex-wrap col-md-12 mt-2">
                        <button type="button" class="btn btn-primary confirm-map text-center"><i class="fas fa-map-marked-alt"></i> Confirmar Endereço no Mapa</button>
                        <button type="button" class="btn btn-secondary text-center save-address"><i class="fa fa-arrow-up"></i> Retornar</button>
                    </div>
                </div>
                <input type="hidden" name="lat[]" value="${lat}"/>
                <input type="hidden" name="lng[]" value="${lng}"/>
            </div>
        </td>
    </tr>
    `);
}

const showHideTableAddress = () => {
    if ($('table tbody tr').length) $('table').slideDown('slow');
    else  $('table').slideUp('slow');
}
