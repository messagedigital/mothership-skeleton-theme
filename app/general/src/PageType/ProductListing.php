<?php

namespace RSAR\General\PageType;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Cog\Field\Factory as FieldFactory;

class ProductListing implements PageTypeInterface
{
	public function getName()
	{
		return 'product_listing';
	}

	public function getDisplayName()
	{
		return 'Product listing';
	}

	public function getDescription()
	{
		return 'A page for listing products';
	}

	public function allowChildren()
	{
		return true;
	}

	public function getViewReference()
	{
		return 'RSAR:General::page_type:product_listing';
	}

	public function setFields(FieldFactory $factory)
	{}
}