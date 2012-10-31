<!-- location template state -->
<script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script>
/* initialize defaults for the map */
function initialize() {
    var mapOptions = {
        zoom: 3,
        center: new google.maps.LatLng({$latitude},{$longitude}),
        mapTypeId: google.maps.MapTypeId.HYBRID,
        mapTypeControl: false,
        streetViewControl: false,
        panControl: false,
        zoomControlOptions: {
            position: google.maps.ControlPosition.LEFT_BOTTOM
        }
    }
    /* display the map */
    map = new google.maps.Map(document.getElementById('map'), mapOptions);
	var marker = new google.maps.Marker({
		position: new google.maps.LatLng({$latitude},{$longitude}),
		map: map,
		title:"User location",
		draggable: true
	});
	var geocoder = new google.maps.Geocoder();
		function geocodePosition(pos) {
			geocoder.geocode({
			latLng: pos
		}, function(responses) {
			if (responses && responses.length > 0) {
				map.setCenter(responses[0].geometry.location);
				updateMarkerAddress(responses[0].formatted_address);
				var x = responses[0].formatted_address.split(', ');
				document.getElementById('localityName').value = x[1];
				var y = x[2].split(' ');
				document.getElementById('stateOrProvinceName').value = y[0];
				document.getElementById('countryName').value = x[3];
			} else {
				updateMarkerAddress('Cannot determine address at this location.');
			}
		});
	}
	function updateMarkerStatus(str) {
		document.getElementById('markerStatus').innerHTML = str;
	}
	function updateMarkerPosition(latLng) {
		document.getElementById('info').innerHTML = [
			latLng.lat(),
			latLng.lng()
		].join(', ');
	}
	function updateMarkerAddress(str) {
		document.getElementById('address').innerHTML = str;
	}
	google.maps.event.addListener(marker, 'dragstart', function() {
		updateMarkerAddress('Dragging...');
	});
	google.maps.event.addListener(marker, 'drag', function() {
		updateMarkerStatus('Dragging...');
		updateMarkerPosition(marker.getPosition());
	});
	google.maps.event.addListener(marker, 'dragend', function() {
		updateMarkerStatus('Drag ended');
		geocodePosition(marker.getPosition());
	});
}
google.maps.event.addDomListener(window, 'load', initialize);

$(function () {
 var msie6 = $.browser == 'msie' && $.browser.version < 7;
 if (!msie6) {
  var top = $('#float').offset().top - parseFloat($('#float').css('margin-top').replace(/auto/, 0));
  $(window).scroll(function (event) {
   var y = $(this).scrollTop();
   if (y >= top) {
    $('#float').addClass('fixed');
   } else {
    $('#float').removeClass('fixed');
   }
  });
 }
});
</script>
<div id="floatWrapper">
 <div id="float">
  <div id="form" class="rounder gradient">
   <h2>Location</h2>
   <p>Drag the marker to your location if it does not auto-matically populate the correct address information</p>
   <div id="map" style="width:95%; height:400px"></div>
   <div id="infoPanel" class="small">
    <b>Marker status:</b>
    <div id="markerStatus"><i>Click and drag the marker.</i></div>
    <b>Current position:</b>
    <div id="info"></div>
    <b>Closest matching address:</b>
    <div id="address"></div>
   </div>
  </div>
 </div>
</div>
<!-- location template end -->