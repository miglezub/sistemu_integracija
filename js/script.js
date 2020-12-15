let map;
var ip = false;
document.addEventListener("load", getLocation());
var markerArray = [];
var markerData = [];
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 54.899541041224374, lng: 23.885363638510803 },
        zoom: 15,
    });
    getMarkerArray();
}

function getLocation() {
    ip = false;
    // var x = document.getElementById("position");
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, getLocationByIp);
    } else {
        // x.innerHTML = "Nepavyko gauti informacijos apie buvimo vietą";
    }
}
function showPosition(position) {
    // var x = document.getElementById("position");
    var currentPos = {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
    };
    if(!ip) {
        // x.innerHTML = "Nustatyta vieta:</br>Platuma: " + position.coords.latitude + ", Ilguma: " + position.coords.longitude;
    }
    // map.setCenter(currentPos);
}
function getLocationByIp() {
    // var x = document.getElementById("position");
    x.innerHTML = "Geolokacijos aptikimas išjungtas arba naršyklė nepalaiko šios funkcijos.</br>Bandoma gauti vietos informaciją pagal IP adresą.";
    $.get("https://ipinfo.io/json", function (response) {
        // x.innerHTML = "IP: " + response.ip + " Vieta: " + response.city + ", " + response.region;
        var location = response.loc;
        var position = {coords: {latitude: parseFloat(location.split(",")[0]), longitude: parseFloat(location.split(",")[1])}};
        ip = true;
        showPosition(position);
    }, "jsonp");
}
function getMarkerArray() {
    markerArray = [];
    markerData = [];
    var bounds = new google.maps.LatLngBounds();
    var infowindow = new google.maps.InfoWindow();
    var marker, i;
    $.ajax({
        url: "include/get_markers.php",
        type: "post",
        success: function(data) {
            for (index = 0; index < data.length; ++index) {
                console.log(data[index]);
                var location = data[index].coordinates.split(", ");
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(location[0], location[1]),
                    map: map,
                    title: data[index].place,

                });
                google.maps.event.addListener(marker, 'click', (function(marker, i) {
                    return function() {
                        console.log(markerArray.indexOf(marker));
                        infowindow.setContent(markerData[markerArray.indexOf(marker)].place);
                        infowindow.open(map, marker);
                    }
                })(marker, i));
                marker.setMap(map);
                markerArray.push(marker);
                markerData.push(data[index]);
                bounds.extend(marker.getPosition());
            }
            console.log(markerArray);
            console.log(markerData);
            map.fitBounds( bounds );
        }
    });
}