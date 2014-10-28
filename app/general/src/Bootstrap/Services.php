<?php

namespace App\General\Bootstrap;

use App\General\PageType;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Mothership\Commerce\Product;
use Message\Mothership\OrderReturn;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services->extend('cms.page.types', function($collection, $c) {
			$collection
				->add(new PageType\Generic)
				->add(new PageType\Product)
				->add(new PageType\ProductListing)
				->add(new PageType\Home)
				->add(new PageType\OurStory)
				->add(new PageType\RedirectToFirstChild)
			;

			return $collection;
		});

		// Commerce
		$services['product.types'] = function($c) {
			return new Product\Type\Collection([
				new Product\Type\ApparelProductType($c['db.query']),
			]);
		};

		$services->extend('product.image.types', function($types) {

			return $types;
		});

		$services->extend('stock.locations', function($locations) {
			$locations->add(new Product\Stock\Location\Location('web',  'Web'));
			$locations->add(new Product\Stock\Location\Location('bin',  'Bin'));
			$locations->add(new Product\Stock\Location\Location('hold', 'Hold'));

			$locations->setRoleLocation($locations::SELL_ROLE, 'web');
			$locations->setRoleLocation($locations::BIN_ROLE,  'bin');
			$locations->setRoleLocation($locations::HOLD_ROLE, 'hold');

			return $locations;
		});

		$services['app.form.subscribe'] = function($c) {
			return new \App\General\Form\Subscribe;
		};

		$services->extend('shipping.methods', function($methods, $c) {
			$methods->add(new \App\General\ShippingMethod\UkSmall($c['country.list']));
			$methods->add(new \App\General\ShippingMethod\UkLarge($c['country.list']));
			$methods->add(new \App\General\ShippingMethod\EuSmall($c['country.list']));
			$methods->add(new \App\General\ShippingMethod\EuLarge($c['country.list']));
			$methods->add(new \App\General\ShippingMethod\RowSmall($c['country.list']));
			$methods->add(new \App\General\ShippingMethod\RowLarge($c['country.list']));

			return $methods;
		});

		$services->extend('order.dispatch.methods', function($methods) {
			$methods->add(new \App\General\DispatchMethod\Manual);

			return $methods;
		});

		$services->extend('order.dispatch.method.selector', function($selector) {
			$selector->setFunction(function($order) {
				return 'manual';
			});

			return $selector;
		});

		// Extend reasons collection
		$services->extend('return.reasons', function($c) {
			return new OrderReturn\Collection\Collection(array(
				new OrderReturn\Collection\Item('wrong-colour', 'Wrong colour'),
				new OrderReturn\Collection\Item('doesnt-suit-me', 'Doesn\'t suit me'),
				new OrderReturn\Collection\Item('wrong-item-sent', 'Wrong item sent'),
				new OrderReturn\Collection\Item('ordered-two-sizes-for-fit-returning-one', 'Ordered two sizes for fit, returning one'),
				new OrderReturn\Collection\Item('doesnt-fit-me', 'Doesn\'t fit me'),
				new OrderReturn\Collection\Item('not-as-expected', 'Not as expected'),
			));
		});

		// CMS
		$services['app.shop.product_page_loader'] = function($c) {
			return new \App\General\Shop\ProductPageLoader($c['cms.page.loader'], $c['cms.page.content_loader']);
		};
	}
}