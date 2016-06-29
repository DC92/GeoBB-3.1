var l = $(window).width(), h = $(window).height(),
	u = Math.round (Math.min (l/4, h/3)),
	d = new Array (['./styles/chemineur/theme/diapo/escape.png', '', '', '']),
	text = document.getElementsByTagName('var'),
	t = 0;

<!-- BEGIN postrow -->
	var comment = text[t++].innerHTML;
	<!-- IF postrow.S_HAS_ATTACHMENTS -->
		<!-- BEGIN attachment -->
			{postrow.attachment.DISPLAY_ATTACHMENT}
		<!-- END attachment -->
	<!-- ENDIF -->
<!-- END postrow -->

var mygallery=new simpleGallery({
	wrapperid: 'diaporama', //ID of main gallery container,
	title: document.getElementsByTagName('title')[0].text,
	dimensions: [u*4, u*3], //width/height of gallery in pixels. Should reflect dimensions of the images exactly
	shift: [(l-u*4)/2, (h-u*3)/2],
	imagearray: d,
	autoplay: [true, 5000, 2], //[auto_play_boolean, delay_btw_slide_millisec, cycles_before_stopping_int]
	persist: false, //remember last viewed slide and recall within same session?
	fadeduration: 500, //transition duration (milliseconds)
	returnlink: '{U_VIEW_TOPIC}'
})
