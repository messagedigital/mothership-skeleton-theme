/**
 * Navigation pushdown menu
 *
 * This plugin corseponds with the navigation menu for Mothership sites
 *
 * This is a *private* plugin, and should only be used by Message Digital Design.
 *
 * @author Message Digital Design <dev@message.co.uk>
 * @author Richard McCartney <richard@message.co.uk>
 */

jQuery(document).ready(function($) {

	// Set all required variables
	var navigation = $('.navigation'),
		navLink    = navigation.find('ul > li > a'),
		open 	   = false;

	// Insert the subnavigation wrapper
	navigation.after('<div class="subnav-wrapper"></div>');
	var wrapper = $('.subnav-wrapper');

	// Offset the wrapper
	wrapper.css({
		marginTop: '-70px'
	});

	// Click on main navigation
	navLink.on('click', function() {

		var self = $(this),
			subnav = self.parent().find('.sub-navigation ul');

		// Allow clicks on links without sub navigation
		if(!self.siblings('.sub-navigation').length) {
			return;
		}

		// Open navigation
		function openNav() {

			open = true;

			wrapper.html(subnav.clone());
			wrapper.children().show();

			wrapper.css({
				marginTop: '0px'
			});
		}

		// Close navigation
		function closeNav() {

			open = false;

			wrapper.css({
				marginTop: '-70px'
			});

			wrapper.children('.sub-navigation').remove();
		}

		// Open and close if statement
		if (open == true) {
			closeNav();
		} else if (open == false) {
			openNav();
		}

		return false;

	});

});