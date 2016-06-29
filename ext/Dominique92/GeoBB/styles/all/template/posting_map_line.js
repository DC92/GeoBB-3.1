control.geocoder.addTo(map);
control.fileload.addTo(map);

var editor = new L.Control.Draw.Plus({
	draw: {
		polyline: true
	},
	edit: {
		remove: true
	},
	editType: 'MultiPolyline',
	entry: 'edit-json',
	changed: 'edit-change'
}).addTo(map);

gis.addTo(editor.snapLayers);
control.fileload.loader.on('data:loaded', function(e) {
	e.layer.addTo(editor);
}, control.fileload);
