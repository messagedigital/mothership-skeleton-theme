/**
 * Basket functionality
 *
 * This plugin corseponds with the Off canvas basket for Mothership.
 *
 * This is a *private* plugin, and should only be used by Message Digital Design.
 *
 * @author Message Digital Design <dev@message.co.uk>
 * @author Richard McCartney <richard@message.co.uk>
 */

jQuery(document).ready(function($) {

	var container = $('.inner-container'),
		header    = $('.header'),
		offCanvas = $('.off-canvas'),
		link      = $('.open-canvas'),
		close     = $('.close'),
		offSet    = 300,
		open      = false,
		mobile    = false;

	// Open off canvas
	function openCanvas(target, direction) {

		open = true;

		var body = $('body');

		// This only counts for navigation off canvas
		if (direction === 'left') {
			container.css('margin-left', offSet);
			header.css('margin-left', offSet);
		}

		// Basket offcanvas direct and offset for mobile
		else if (direction === 'right' && mobile === true) {
			container.css('margin-left', -offSet);
			header.css('margin-left', -offSet);
		}

		// Desktop off canvas
		else if (direction === 'right' && mobile === false) {
			container.css('margin-left', -offSet);
		}
	}

	// Close off canvas
	function closeCanvas() {

		open = false;

		container.css('margin', 0);
		header.css('margin', 0);

	}

	// Open and close from off canvas link
	link.on('click', function(event) {
		event.preventDefault();
		/* Act on the event */

		var canvasTarget = $(this).data('target'),
			canvasDir    = $(this).data('direction');

		console.log(canvasDir);

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

		// Swipe to close basket
	$(function() {
		//Enable swiping...
		$('.basket').swipe( {

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

		// Swipe to close basket
	$(function() {
		//Enable swiping...
		$('.mobile-nav, .navigation').swipe( {

			//Generic swipe handler for all directions
			swipeLeft:function(event, direction, distance, duration) {
				if (open === true) {
					closeCanvas();
				}
			},
			//Default is 75px, set to 0 for demo so any distance triggers swipe
			threshold: 75
		});
	});

	$(window).on('resize', function() {
		closeCanvas();

		// Check if the site is below 768px width
		if (header.css('position') === 'fixed') {
			mobile = true;
		} else {
			mobile = false;
		}

	}).trigger('resize');
});