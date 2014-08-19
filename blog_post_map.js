function initMap(mapId, markers) {

	L.mapbox.accessToken = 'pk.eyJ1IjoidGJpbm5hIiwiYSI6InpqcW8tT1kifQ.bTs8e9ttuX0jGmNpGobTqg';

	var map = L.mapbox.map(mapId, "tbinna.i80746eh").setView([47.529, 8.54], 2);

	// add additional map controls (plugins)
	L.control.fullscreen().addTo(map);
	L.control.locate().addTo(map);

	// hide the feature layer on load
	map.featureLayer.setFilter(function() { return false; });

	map.on("zoomend", function() {
	    if (map.getZoom() >=13) {
	        map.featureLayer.setFilter(function() { return true; });
	    } else {
	        map.featureLayer.setFilter(function() { return false; });
	    }
	});

	// center map to feature on click
	map.featureLayer.on("click", function(e) {
        map.panTo(e.layer.getLatLng());
    });

	for (var i = markers.length - 1; i >= 0; i--) {
		var marker = markers[i];

		L.marker(marker.latLon, {
			icon: L.mapbox.marker.icon({
				'marker-size': 'large'
			})
		})
		.bindPopup('<h5>' + marker.title + '</h5><p><img src=\"' + marker.postThumbnailUrl + '\" /></p><a href=\"' + marker.permalink + '\">' + marker.permalink + '</a>')
		.addTo(map);
	}
}