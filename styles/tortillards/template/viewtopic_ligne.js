var key = {
		ign: window.location.host == 'localhost'
			? 'u71tqebfror0c2nn3nppcbk2' // localhost 31/03/2016 http://api.ign.fr
			: 'o6owv8ubhn3vbz2uj8jq5j0z', // dominique92.github.io http://pro.ign.fr/api-web
		bing: 'ArLngay7TxiroomF7HLEXCS7kTWexf1_1s1qiF7nbTYs2IkD3XLcUnvSlKbGRZxt', // https://www.bingmapsportal.com
		os: 'CBE047F823B5E83CE0405F0ACA6042AB' // http://www.ordnancesurvey.co.uk/business-and-government/products/os-openspace/
	},
	map = new L.Map('map');

// Baselayer
L.TileLayer.collection('OSM-FR').addTo(map);

var gis = new L.GeoJSON.Ajax({
	urlGeoJSON: ext_dir+'gis.php',
	bbox: true,
	style: function(feature) {
		return {
			url: 'viewtopic.php?t=' + feature.properties.id,
			degroup: 12,
			weight: feature.properties.post_id == post_id ? 5 : 3,
			title: feature.properties.nom,
			iconUrl: feature.properties.icone,
			iconAnchor: [8, 8]
		};
	}
}).addTo(map);
gis.on('mousemove mouseout', function(e) {
	hover (e.layer.options.post_id, e.type);
});

<!-- IF GEO_AFF_LIGNE -->
	map.setView([45, 5], 7);
<!-- ENDIF -->

// Controls
new L.Control.Scale().addTo(map);
new L.Control.Fullscreen().addTo(map);
new L.Control.Coordinates().addTo(map);

new L.Control.Gps()
	.addTo(map)
	.on('gpslocated', function(e) {
		e.target._map.setView(e.latlng, 16, {
			reset: true
		});
		if (curseur)
			curseur.setLatLng(e.latlng);
	});

new L.Control.Permalink.Cookies({
	text: null, // Le contrôle n'apparait pas sur la carte car ça n'a pas de sens pour une page qui positionne elle même la carte
	layers: new L.Control.Layers.overflow(L.TileLayer.collection()).addTo(map)
}).addTo(map);

map.fitBounds(bounds, {
	animate: false
});

function hover(post_id, mouse) {
	var pel = document.getElementById('p' + post_id);
	if (pel)
		pel.className = pel.className
			.replace(/\shovered/g, '')
			+ (mouse == 'mousemove' ? ' hovered' : '');

	for (l in gis._layers)
		for (l2 in gis._layers[l]._layers)
			if (gis._layers[l]._layers[l2].options.post_id == post_id) {
				gis._layers[l]._layers[l2].setStyle({
					color: mouse == 'mousemove' ? 'red' : 'black'
				});
			}
}