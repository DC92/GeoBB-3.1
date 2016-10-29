<!-- IF MAP_KEYS -->
	var key = {MAP_KEYS};
<!-- ENDIF -->

var map = new L.Map('map', {
	layers: [L.TileLayer.collection('OSM-FR')]
});

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
var control_layers = new L.Control.Layers(L.TileLayer.collection()).addTo(map),

	control = {
		permalink: new L.Control.Permalink.Cookies({
			position: 'bottomright',
			<!-- IF GEO_BBOX_MINY -->
				move: false, // N'utilise pas la position
			<!-- ELSE -->
				move: true, // Utilise la position
			<!-- ENDIF -->
			<!-- IF SCRIPT_NAME != 'index' -->
				text: null, // N'affiche pas le permalink
			<!-- ENDIF -->
			layers: control_layers
		}).addTo(map),

		scale: new L.Control.Scale().addTo(map),
		coordinates: new L.Control.Coordinates({
			position:'bottomleft'
		}).addTo(map),

		scale: new L.Control.Fullscreen().addTo(map),

		gps: new L.Control.Gps().addTo(map),

		geocoder: new L.Control.OSMGeocoder({
			position: 'topleft'
		}),

		fileload: new L.Control.FileLayerLoad(),

		fileget: new L.Control.Click(
			function () {
				return gis._getUrl() + '&format=gpx';
			}, {
				title: "Obtenir les élements de la carte dans un fichier GPX\n"+
						"Pour le charger sur un GARMIN, utlisez Basecamp\n"+
						"Atention: le fichier peut être gros pour une grande carte",
				label: '&#8659;'
			}
		),

		print: new L.Control.Click(
			function () {
				window.print();
			}, {
				title: "Imprimer la carte",
				label: '&#x1f5b6;'
			}
		)
	};

// Couches vertorielles
var gis = new L.GeoJSON.Ajax({
	urlGeoJSON: '{EXT_DIR}gis.php',
	argsGeoJSON: {},
	bbox: true,
<!-- IF POST_ID -->
	filter: function(feature) {
		return feature.properties.post_id != {POST_ID};
	},
<!-- ENDIF -->
	style: function(feature) {
		var popup = [
			'<a href="viewtopic.php?t='+feature.properties.id+'">'+feature.properties.nom+'</a>'
		];
		<!-- IF IS_MODERATOR and TOPIC_ID and GEO_MAP_TYPE == 'point' -->
			if (feature.properties.type_id == {FORUM_ID} && // Uniquement entre points de même type
				feature.properties.id != {TOPIC_ID}) // Pas le point avec lui même
				popup.push ('<a href="' + [
					'mcp.php?i=main&mode=forum_view&action=merge_topic',
					'f={FORUM_ID}',
					't='+feature.properties.id,
					'to_topic_id={TOPIC_ID}',
					'redirect=viewtopic.php?t={TOPIC_ID}',
				].join('&') + '" title="CETTE OPERATION EST IRREVERSIBLE">Fusionner avec "{% autoescape 'js' %}{TOPIC_TITLE}{% endautoescape %}"</a>');
		<!-- ENDIF -->
		var s = {
			popup: ('<p>' + popup.join('</p><p>') + '</p>').replace(/<p>\s*<\/p>/ig, ''),
<!-- IF SCRIPT_NAME != 'posting' -->
			url: feature.properties.id ? 'viewtopic.php?t='+feature.properties.id : null,
			degroup: 12,
<!-- ENDIF -->
			iconUrl: feature.properties.icone,
			iconAnchor: [8, 8],
			popupAnchor: [0, -8],
			weight: <!-- IF TOPIC_ID --> feature.properties.id == {TOPIC_ID} ? 3 : <!-- ENDIF --> 2
		};

		if (feature.properties.url && 
			feature.properties.url != 'this') {
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
						deblinkref+'&t={TOPIC_ID}&nt={TOPIC_ID}">Lier à "{% autoescape 'js' %}{TOPIC_TITLE}{% endautoescape %}"</a>',
					<!-- ENDIF -->
				<!-- ENDIF -->
			];
			s.popup = ('<p>' + popup.join('</p><p>') + '</p>').replace(/<p>\s*<\/p>/ig, '');
			s.popupClass = 'map-reference';
		}
		return s;
	}
}).addTo(map);

// Controle secondaire pour les couches vectorielles
var lc2 = new L.Control.Layers.argsGeoJSON(
	gis,
	{
	<!-- BEGIN map_overlays -->
		'{map_overlays.NAME}': {l: gis, p: '{map_overlays.PAR}', v: '{map_overlays.VALUE}'},
	<!-- END map_overlays -->
	}
).addTo(map);
