/**
 * Navigation functionality
 *
 * This plugin corseponds with the Off canvas basket for Mothership
 *
 * This is a *private* plugin, and should only be used by Message Digital Design.
 *
 * @author Message Digital Design <dev@message.co.uk>
 * @author Richard McCartney <richard@message.co.uk>
 */

jQuery(document).ready(function($) {

	var container = $('.container'),
		navigation    = $('.nav-offcanvas'),
		link      = $('.nav-open'),
		close     = $('.close'),
		offSet    = -300,
		open      = false,
		mobile    = false;

	// Open off canvas
	function openCanvas() {

		open = true;

		var body = $('body');

		// This only counts for navigation off canvas
		if (mobile === true) {
			navigation.css('margin-left', 0);
			container.css('left', 300);
		}

	}

	// Close off canvas
	function closeCanvas() {

		open = false;

		if (mobile === true) {
			container.css('left', 0);
			navigation.css('margin-left', offSet);
        }
	}

	// Open and close from off canvas link
	link.on('click', function(event) {
		event.preventDefault();
		/* Act on the event */

		var canvasTarget = $(this).data('target'),
			canvasDir    = $(this).data('direction');

		if (open === false) {
			openCanvas(canvasTarget, canvasDir);
		} else {
			closeCanvas();
		}

	});

	// close canvas
	close.on('click', function(event) {
		event.preventDefault();

		closeCanvas();
	});

		// Swipe to close navigation
	$(function() {
		//Enable swiping...
		$('.nav-offcanvas').swipe( {

			//Generic swipe handler for all directions
			swipeRight:function(event, direction, distance, duration) {
				if (open === true) {
					closeCanvas();
				}
			},
			//Default is 75px, set to 0 for demo so any distance triggers swipe
			threshold: 100
		});
	});

	$(window).on('resize', function() {
		closeCanvas();

		// Check if the site is below 768px width
		if ($('.container').css('max-width') == '768px') {
			mobile = true;
			navigation.css('margin-left', offSet);
		} else {
			mobile = false;
			navigation.css('margin-left', 0);
		}

	}).trigger('resize');

});