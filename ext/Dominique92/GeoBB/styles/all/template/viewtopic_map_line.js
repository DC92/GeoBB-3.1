function hover(post_id, evt) {
	var found = false;
	for (l in gis._layers)
		for (l2 in gis._layers[l]._layers)
			if (gis._layers[l]._layers[l2].options.post_id == post_id) {
				gis._layers[l]._layers[l2].setStyle({
					color: evt == 'mouseout' ? L.Polyline.prototype.options.color : 'red'
				});
				found = true;
			}

	var pel = document.getElementById('p' + post_id);
	if (pel && found)
		pel.className = pel.className
			.replace(/\shovered/g, '')
			+ (evt == 'mouseout' ? '' : ' hovered');
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
