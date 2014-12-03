/**
 * Real Stars are Rare jQuery
 *
 * All general site scripts are to be contained here
 *
 * @author Richard McCartney <richard@message.co.uk>
 */

jQuery(document).ready(function($) {

    // Set JS class
    $('.no-js').removeClass('no-js').addClass('js');

	// Scroll to top button
	$('.top').on('click', function(){
		$('html, body').animate({scrollTop : 0},800);
		return false;
	});

	// Insets the tabs menu
	$('.tabs').prepend('<nav class="tab-menu"><ul><li class="details"><a href="#details">Details</a></li></ul></nav>');

	if ($('.fitting').is(':visible')) {
		$('.tab-menu ul').append('<li class="fitting"><a href="#fitting">Fitting guide</a></li>');
	}

	if ($('.shipping').is(':visible')) {
		$('.tab-menu ul').append('<li class="shipping"><a href="#shipping">Shipping &amp; returns</a></li>');
	}

	// product page tab
	(function(){
		$('.tab-menu a').click(function(){
			if($(this).hasClass('current')) {
				return false;
			}

			$('.tab-menu .current').removeClass('current');
			$('.tabs > div').hide();
			$('.tabs div.'+$(this).parent().attr('class')).show();
			$(this).parent().addClass('current');

			return false;
		});
		// select the first tab
		$('.tab-menu li:first-child a').click();
	})();

});