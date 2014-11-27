/**
 * Basket functionality
 *
 * This plugin corseponds with the ading to basket.
 *
 * This is a *private* plugin, and should only be used by Message Digital Design.
 *
 * @author Message Digital Design <dev@message.co.uk>
 * @author Richard McCartney <richard@message.co.uk>
 */

jQuery(document).ready(function($) {

$(function() {
	$(document).bind('ajaxStart', function() {
		$('html').addClass('loading');
	}).bind('ajaxStop', function() {
		$('html').removeClass('loading');
	});

	$('form[action*="basket"]').on('submit', function() {
		var self = $(this),
			selectBox = self.find('select'),
			value = selectBox.val();

		// Check if a product selectbox has a value
		if (selectBox.is(':visible') && selectBox.val() == null) {
			selectBox
				.animate({ left: "-10px" }, 100).animate({ left: "10px" }, 100)
				.animate({ left: "-10px" }, 100).animate({ left: "10px" }, 100)
				.animate({ left: "0px" }, 100);

			$('.error-msg').fadeOut(200);
			$('.error-msg').delay(400).queue(function(n) {
				$(this).remove();
				n();
			});

			selectBox.after('<div class="error-msg"><p>Please select an option</p></div>');
			$('.error-msg').hide();
			$('.error-msg').fadeIn(600);

			return false;

		} else {

			$('.error-msg').fadeOut(200);

		$.ajax({
				url       : self.attr('action'),
				data      : self.serialize(),
				method    : 'POST',
				dataType  : 'html',
				beforeSend: function() {
					self.find('input, textarea, button, select')
							.attr('disabled', true);
					self.find('button').css({opacity: 0.1}).after('<span class="spinner"></span>');
				},
				success   : function(data) {
					// Get new basket HTML
					var contents = $('.basket .contents', data),
						icon   = $('.basket-icon span', data);

					// Swap out the basket HTML
					$('.basket .contents').replaceWith(contents);
					$('.basket-icon span').replaceWith(icon);

					// Icon animation
					$('.basket-icon span').addClass('pop');

					self.find('button').animate({
						color: '#207A34 !important',
						borderColor: '#207A34 !important'});
					self.find('button').text('success');

				},
				complete  : function(data) {

					self.find('input, textarea, button, select')
							.removeAttr('disabled');
					self.find('button').css({opacity: 1});
					$('.spinner').remove();


					// Edit functionality to restore the  original button colour

					self.find('button').delay(2000).queue(function(n) {
						$(this).text('Add to basket')
							.animate({
								color: '#141F6C',
								borderColor: '#141F6C'
							});
						n();
					});
				},
			});

			return false;
		}

	});
});

});