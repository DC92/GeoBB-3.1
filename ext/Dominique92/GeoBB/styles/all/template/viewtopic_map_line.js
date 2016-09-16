control.geocoder.addTo(map);
control.fileload.addTo(map);
control.fileget.addTo(map);
control.print.addTo(map);

function hover(post_id, e) {
	var found = false;
	for (l in gis._layers)
		for (l2 in gis._layers[l]._layers)
			if (gis._layers[l]._layers[l2].options.post_id == post_id) {
				if (!gis._layers[l]._layers[l2].options.baseColor) // Mémorise 1 fois
					gis._layers[l]._layers[l2].options.baseColor = gis._layers[l]._layers[l2].options.color;

				gis._layers[l]._layers[l2].setStyle({
					color: e == 'mouseout' ? gis._layers[l]._layers[l2].options.baseColor : 'red'
				});
				found = true;
			}

	var pel = document.getElementById('p' + post_id);
	if (pel && found)
		pel.className = pel.className
			.replace(/\shovered/g, '')
			+ (e == 'mouseout' ? '' : ' hovered');
}

gis.on('mouseover mouseout', function(e) {
	hover (e.layer.options.post_id, e.type);
});

var pel;
<!-- BEGIN postrow -->
	pel = document.getElementById('p{postrow.POST_ID}');
	pel.onmouseover =
	pel.onmouseout =
		function(e) {
			hover({postrow.POST_ID}, e.type);
		};
<!-- END postrow -->
