var curseur = new L.Marker(
	map.getCenter(), // Valeur par défaut
	{
		draggable: true,
		riseOnHover: true,
		icon: L.icon({
			iconUrl: '{EXT_DIR}styles/all/theme/images/curseur.png',
			iconAnchor: [15, 15]
		})
	}
);
curseur.coordinates('edit'); // Affiche / saisi les coordonnées
curseur.addTo(map);
map.setView(curseur._latlng, 13); // Centre la carte sur ce point

control.geocoder.addTo(map);

function gotogps () {
	control.gps.deactivate()
	control.gps.on('gpslocated', function(e) {
		e.target._map.setView(e.latlng, 16, {
			reset: true
		});
		curseur.setLatLng(e.latlng);
	});
	control.gps.activate()
}
