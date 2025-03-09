let targetRental;
let markerRental;
let elementForm = $('#formRental');
let draggableMap = true;
let gestureHandlingMap = false;
let elementRental;
let mapRental;

$(function(){
    initMap();
})

const initMap = () => {
    if (typeof mapRental !== "undefined") {
        let container = L.DomUtil.get('mapRental'); //here I first check if map is loaded to DOM
        if(container != null){
            container._leaflet_id = null;
        }
        mapRental.invalidateSize();
    }

    // Where you want to render the map.
    elementRental = document.getElementById('mapRental');
    // Create Leaflet map on map element.
    mapRental = L.map(elementRental, {
        // fullscreenControl: true,
        // OR
        fullscreenControl: {
            pseudoFullscreen: false // if true, fullscreen to page width and height
        },
        gestureHandling: gestureHandlingMap
    });
    // Add OSM tile leayer to the Leaflet map.
    L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapRental);
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

const updateLocationRental = (findDiv) => {
    const address   = findDiv.find('[name="address"]').val();
    const number    = findDiv.find('[name="number"]').val();
    const zipcode   = findDiv.find('[name="cep"]').val().replace(/[^0-9]/g, "");
    const neigh     = findDiv.find('[name="neigh"]').val();
    const city      = findDiv.find('[name="city"]').val();
    const state     = findDiv.find('[name="state"]').val();

    loadAddressMapRental(`${address},${number}-${zipcode}-${neigh}-${city}-${state}`, findDiv);
}

const startMarkerRental = (latLng) => {
    targetRental  = latLng;
    // icon    = L.icon({
    //     iconUrl: 'dist/img/marcadores/cacamba.png',
    //     iconSize: [40, 40],
    // });
    // marker = L.marker(target, { draggable:'true', icon }).addTo(map);
    markerRental = L.marker(targetRental, { draggable:draggableMap }).addTo(mapRental);
    markerRental.on('dragend', () => {
        const position = markerRental.getLatLng();
        elementForm.find('[name="lat"]').val(position.lat);
        elementForm.find('[name="lng"]').val(position.lng);
    });
    mapRental.setView(targetRental, 13);
    setTimeout(() => {
        mapRental.invalidateSize();
    }, 1000);
}

const getLocationRental = (reload_map = false) => {
    if (reload_map) {
        initMap();
    }
    mapRental.on('locationfound', onLocationFoundRental);
    mapRental.on('locationerror', onLocationErrorRental);
    mapRental.locate({setView: true, maxZoom: 12});
}

const onLocationFoundRental = e => {
    startMarkerRental(e.latlng);
}

async function onLocationErrorRental(e){
    if (!draggableMap && parseInt(e.code) === 1) {
        const address = await deniedLocation();

        const center = L.latLng(address.lat, address.lng);
        startMarkerRental(center);
    }
}
