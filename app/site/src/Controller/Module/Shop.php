<?php

namespace Mothership\Site\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Mothership\CMS\Page\Page;
use Message\Cog\Field\RepeatableContainer;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\FieldType\Productoption;

class Shop extends Controller
{
	const PRODUCT_TYPE = 'product';

	public function productBlocks(Page $page)
	{
		return $this->render('Mothership:Site::module:shop:product_blocks', [
			'pages'  => $this->get('app.shop.product_page_loader')->getProductPages($page),
			'perRow' => 4,
		]);
	}

	public function productBlock($product, ProductOption $option = null, Page $page)
	{
		if ($product instanceof Product) {
			// skip if no option set
			if($option && $option->name){
				$unit = null;
				$units = $product->getUnits();
				// loop through units
				foreach ($units as $xUnit) {

					// try catch as no way of actually checking
					// if option is set on unit
					try {
						$opt = $xUnit->getOption($option->name);

						if ($opt === $option->value) {
							$unit = $xUnit;
							break;
						}
					} catch (\InvalidArgumentException $e) {
						// continue
					}
				}
				/**
				 * @todo Give warning if !$unit
				 */
				if(!$unit) {
					$this->get('log.errors')->warn("Unit with '$option->name' of '$option->value' could not be found for product id $product->id");
				}


			} else {$unit = null;}

		} else if ($product instanceof Unit) {
			$unit = $product;
			$product = $unit->getProduct();
		}

		$option = ($option->name ? [ $option->name => $option->value ] : null);

		return $this->render('Mothership:Site::module:shop:product_block', [
			'image'           => $page->getContent()->gallery->all()[0]->image ?: $page->getContent()->product->product->product->image,
			'productName'     => $page->title,
			'productSlug'     => $page->slug,
			'variablePricing' => $product->hasVariablePricing('retail', null, $option?:[]),
			'retailPrice'     => ${$unit?'unit':'product'}->getPrice(),
			'rrpPrice'        => ${$unit?'unit':'product'}->getPrice('rrp'),
			'multipleUnits'   => (count($product->getVisibleUnits()) > 1),
		]);
	}

	public function basket()
	{
		$basket = $this->get('basket')->getOrder();
		$totalListPrice = 0;

		foreach ($basket->items as $item) {
			$totalListPrice += $item->listPrice;
		}

		return $this->render('Mothership:Site::module:shop:basket', [
			'basket'         => $basket,
			'totalListPrice' => $totalListPrice,
		]);
	}

	public function crossSell(RepeatableContainer $crossSell)
	{
		$mapper = $this->get('product.page_mapper.option_criteria');

		$mapper->setValidFieldNames([
			'product',

		]);
		$mapper->setValidGroupNames([
			'product',
		]);

		return $this->render('Mothership:Site::module:shop:cross_sell', [
			'mapper'     => $mapper,
			'cross_sell' => $crossSell,
		]);
	}

}