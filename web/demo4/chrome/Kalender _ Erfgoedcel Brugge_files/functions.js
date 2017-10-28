$(document).ready(function()
{
	/**
	 *	Standaard alle links met # false laten returnen zodat er niet naar boven gegaan wordt
	 */

	$("a[href=#]").click(function()
	{
		return false;
	});

	$("a[rel=external]").click(function()
	{
		$(this).attr("target", "_blank");
	});
});

//-- Check voor mobiele telefoon
if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
	var msViewportStyle = document.createElement('style')
	msViewportStyle.appendChild(
			document.createTextNode(
					'@-ms-viewport{width:auto!important}'
					)
			)
	document.querySelector('head').appendChild(msViewportStyle)
}

function warningFunction()
{


	$('section.warning span.icon').css('height', $('section.warning').height());

}

$(window).load(warningFunction);
$(window).resize(warningFunction);

$(document).ready(function()
{
	/*
	 * Replace all SVG images with inline SVG
	 */
	jQuery('img.svg').each(function() {
		var $img = jQuery(this);
		var imgID = $img.attr('id');
		var imgClass = $img.attr('class');
		var imgURL = $img.attr('src');

		jQuery.get(imgURL, function(data) {
			// Get the SVG tag, ignore the rest
			var $svg = jQuery(data).find('svg');

			// Add replaced image's ID to the new SVG
			if (typeof imgID !== 'undefined') {
				$svg = $svg.attr('id', imgID);
			}
			// Add replaced image's classes to the new SVG
			if (typeof imgClass !== 'undefined') {
				$svg = $svg.attr('class', imgClass + ' replaced-svg');
			}

			// Remove any invalid XML tags as per http://validator.w3.org
			$svg = $svg.removeAttr('xmlns:a');

			// Replace image with new SVG
			$img.replaceWith($svg);

		}, 'xml');

	});

	// svg fallback
	if (!Modernizr.svg) {
		var imgs = document.getElementsByTagName('img');
		var svgExtension = /.*\.svg$/
		var l = imgs.length;
		for (var i = 0; i < l; i++) {
			if (imgs[i].src.match(svgExtension)) {
				imgs[i].src = imgs[i].src.slice(0, -3) + 'png';
				console.log(imgs[i].src);
			}
		}
	}

	// MOBILE LINK MENU
	$('a.mobile-link').click(function() {
		$('body').toggleClass('menu-push');
		$(this).toggleClass('open');
		$('.mobile-menu-push').toggleClass('open');
		$('.overlay').toggleClass('visible');
	});
});