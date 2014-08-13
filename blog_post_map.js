function initMap(mapId, markers) {
	var map = L.mapbox.map(mapId, "tbinna.i80746eh").setView([47.529, 8.54], 2);

	// center map to feature on click
	map.featureLayer.on("click", function(e) {
        map.panTo(e.layer.getLatLng());
    });

	// hide the feature layer on load
	map.featureLayer.setFilter(function() { return false; });

	//L.control.fullscreen().addTo(map);
	//L.control.locate().addTo(map);

	map.on("zoomend", function() {
	    if (map.getZoom() >=13) {
	        map.featureLayer.setFilter(function() { return true; });
	    } else {
	        map.featureLayer.setFilter(function() { return false; });
	    }
	});

	for (var i = markers.length - 1; i >= 0; i--) {
		var markerN = markers[i];

		L.marker(markerN.latLon, {
			icon: L.mapbox.marker.icon({
				'marker-symbol': 'star',
				'marker-size': 'large'
			})
		})
		.bindPopup('<b>' + markerN.title + '</b><br><a href=\"' + markerN.permalink + '\">' + markerN.permalink + '</a>')
		.addTo(map);
	}
}