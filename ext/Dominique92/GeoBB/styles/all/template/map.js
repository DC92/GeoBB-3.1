<!-- IF MAP_KEYS -->
	var key = {MAP_KEYS};
<!-- ENDIF -->

L.Polyline.prototype.options.weight = L.Polygon.prototype.options.weight = 3;
L.Polyline.prototype.options.opacity = L.Polygon.prototype.options.opacity = 1;

var map = new L.Map('map', {
	layers: [L.TileLayer.collection('OSM-FR')]
});

// Resize
$(function() {
	$('#map').resizable ({
		handles: 's,w,sw',
		resize: function (event,ui) {
			ui.position.left = ui.originalPosition.left;
		}
	});
});

// Controls
var control = {
	layers: new L.Control.Layers.overflow(L.TileLayer.collection()).addTo(map),
	scale: new L.Control.Scale().addTo(map),
	fullscreen: new L.Control.Fullscreen().addTo(map),
	coordinates: new L.Control.Coordinates().addTo(map),
	gps: new L.Control.Gps().addTo(map),
	geocoder: new L.Control.OSMGeocoder({
		position: 'topleft'
	}),
	fileload: new L.Control.FileLayerLoad()
};

// Default position
<!-- IF GEO_BBOX_MINY -->
	map.fitBounds([
		[{GEO_BBOX_MINY}, {GEO_BBOX_MINX}],
		[{GEO_BBOX_MAXY}, {GEO_BBOX_MAXX}]
	], {
		animate: false
	});
<!-- ELSE -->
	map.setView([46.9, 1.7], 6); // France
<!-- ENDIF -->

new L.Control.Permalink.Cookies({
	<!-- IF GEO_BBOX_MINY -->
		move: false, // N'utilise pas la position
	<!-- ELSE -->
		move: true, // Utilise la position
	<!-- ENDIF -->
	<!-- IF SCRIPT_NAME != 'index' -->
		text: null, // N'affiche pas le permalink
	<!-- ENDIF -->
	layers: control.layers
}).addTo(map);

// Controle secondaire pour les couches vectorielles
var lc2 = new L.Control.Layers.args({},{
	<!-- BEGIN map_overlays -->
		'{map_overlays.NAME}': {k: '{map_overlays.KEY}', v: '{map_overlays.VALUE}'},
	<!-- END map_overlays -->
}).addTo(map);

var args = lc2.args();
<!-- IF FORUM_ID --> // Priorité aux éléments du forum affiché
	args.priority = {FORUM_ID};
<!-- ENDIF -->

// Chem POI
var gis = new L.GeoJSON.Ajax({
	urlGeoJSON: '{EXT_DIR}gis.php',
	argsGeoJSON: args,
	bbox: true,
<!-- IF POST_ID -->
	filter: function(feature) {
		return feature.properties.post_id != {POST_ID};
	},
<!-- ENDIF -->
	style: function(feature) {
		var s = {
			popup: '<a href="viewtopic.php?t='+feature.properties.id+'" class="lien-noir">'+feature.properties.nom+'</a>',
			remanent: true,
<!-- IF SCRIPT_NAME != 'posting' -->
			url: feature.properties.id ? 'viewtopic.php?t='+feature.properties.id : null,
<!-- ENDIF -->
			iconUrl: feature.properties.icone,
			iconAnchor: [8, 8],
			popupAnchor: [0, -8],
			weight: <!-- IF TOPIC_ID --> feature.properties.id == {TOPIC_ID} ? 3 : <!-- ENDIF --> 2,
			degroup: 12
		};

		if (feature.properties.url) {
			var parser = document.createElement('a');
			parser.href = feature.properties.url;

			var deblinkref = [
				'<a href="posting.php?mode=post',
				'sid={SESSION_ID}',
				'f='+feature.properties.type_id,
				'url='+encodeURI(feature.properties.url),
			].join('&');

			var finlink = [
				'nom='+encodeURI(feature.properties.nom),
				'lon='+feature.geometry.coordinates[0],
				'lat='+feature.geometry.coordinates[1],
			].join('&');

			var popup = [
				'<b>'+feature.properties.nom+'</b>',
				'<a target="_blank" href="'+feature.properties.url+'">Voir sur '+parser.hostname.replace('www.','')+'</a>',
				<!-- IF IS_MODERATOR -->
					deblinkref+'&'+finlink+'">Créer une fiche</a>',
					<!-- IF TOPIC_ID and GEO_MAP_TYPE == 'point' -->
						deblinkref+"&t={TOPIC_ID}&nt={TOPIC_ID}\">Lier à \"{TOPIC_TITLE}\"</a>",
					<!-- ENDIF -->
				<!-- ENDIF -->
			];
			s.popup = ('<p>' + popup.join('</p><p>') + '</p>').replace(/<p>\s*<\/p>/ig, '');
			s.popupClass = 'map-reference';
		}
		return s;
	}
}).addTo(map);

map.on('clickLayersArgs', function() {
	gis.options.argsGeoJSON = lc2.args();
	gis.reload();
});
