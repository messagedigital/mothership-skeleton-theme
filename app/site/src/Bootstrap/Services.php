<?php

namespace Mothership\Site\Bootstrap;

use Mothership\Site\PageType;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Mothership\Commerce\Product;
use Message\Mothership\OrderReturn;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services->extend('cms.page.types', function($collection, $c) {
			$collection
				->add(new PageType\Home)
				->add(new PageType\Generic)
				->add(new PageType\Product)
				->add(new PageType\ProductListing)
				->add(new PageType\OurStory)
				->add(new PageType\RedirectToFirstChild)
			;

			return $collection;
		});

		$services['app.form.subscribe'] = function($c) {
			return new \Mothership\Site\Form\Subscribe;
		};

		// CMS
		$services['app.shop.product_page_loader'] = function($c) {
			return new \Mothership\Site\Shop\ProductPageLoader($c['cms.page.loader'], $c['cms.page.content_loader']);
		};
	}
}