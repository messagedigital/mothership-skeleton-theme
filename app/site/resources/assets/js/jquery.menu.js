/**
 * Navigation pushdown menu
 *
 * This plugin corseponds with the navigation menu for Stoneham.
 *
 * This is a *private* plugin, and should only be used by Message Digital Design.
 *
 * @author Message Digital Design <dev@message.co.uk>
 * @author Richard McCartney <richard@message.co.uk>
 */

jQuery(document).ready(function($) {

	// Set all required variables
	var navigation = $('.navigation'),
		navLink    = navigation.find('ol > li > a'),
		backLink   = $('.mobile-back'),
		open 	   = false;

	// Insert the subnavigation wrapper
	navigation.after('<div class="subnav-wrapper nav-offcanvas"></div>');
	var wrapper = $('.subnav-wrapper');

	backLink.css({
		zIndex: '-9999'
	});

	// Click on main navigation
	navLink.on('click', function() {

		var self = $(this),
			subnav = self.parent().find('.sub-navigation ol');

		// Allow clicks on links without sub navigation
		if(!self.siblings('.sub-navigation').length) {
			return;
		}

		if($('.navigation li').hasClass('current')) {
			$('.navigation li').removeClass('current')
		}

		// Open navigation
		function openNav(callback) {
			wrapper.html(subnav.clone());
			wrapper.slideDown(500, callback);

			wrapper.children().show();

			open = self.data('pushid');

			backLink.css({
				zIndex: '999999'
			})

		}

		// Close navigation
		function closeNav(callback) {

			wrapper.children('.sub-navigation').remove();

			wrapper.slideUp(500, callback);

			open = false;

		}

		// Open and close if statement
		if (open !== false) {
			if (open !== self.data('pushid')){
				closeNav(openNav);
				$('.navigation li a').removeClass('current');
				self.addClass('current');
			}else {
				closeNav();
				self.removeClass('current');
			}
		} else if (open === false) {
			openNav();
			self.addClass('current');
		}


		return false;

	});

	$('.mobile-back').on('click', function() {
		wrapper.children('.sub-navigation').remove();
		wrapper.slideUp(500);
		open = false;

		backLink.css({
			zIndex: '-9999'
		});
	});

});