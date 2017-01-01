var l = $(window).width(), h = $(window).height(),
	u = Math.round (Math.min (l/4, h/3)),
	vars = document.getElementsByTagName('var'),
	d = new Array (
		['./styles/chemineur/theme/diapo/escape.png', '', '', '']
	);
	
	for (i = 0; i < vars.length; i++)
		d.push ([
			'download/file.php?id='+vars[0].id+'&s='+u*4,
			'', '',
			vars[i].parentElement.firstElementChild.innerHTML + // Commentaire du post
			vars[i].innerHTML // Commentaire de la diapo
		]);

var mygallery=new simpleGallery({
	wrapperid: 'diaporama', //ID of main gallery container,
	title: document.getElementsByTagName('title')[0].vars,
	dimensions: [u*4, u*3], //width/height of gallery in pixels. Should reflect dimensions of the images exactly
	shift: [(l-u*4)/2, (h-u*3)/2],
	imagearray: d,
	autoplay: [true, 5000, 2], //[auto_play_boolean, delay_btw_slide_millisec, cycles_before_stopping_int]
	persist: false, //remember last viewed slide and recall within same session?
	fadeduration: 500, //transition duration (milliseconds)
	returnlink: '{U_VIEW_TOPIC}'
})
