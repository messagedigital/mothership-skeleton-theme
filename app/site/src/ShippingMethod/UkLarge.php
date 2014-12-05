<?php

namespace App\General\ShippingMethod;

use Message\Mothership\Commerce\Order\Order;

class UkLarge extends AbstractMethod
{
	protected $_min = 1000;

	public function getName()
	{
		return 'uk_large';
	}

	public function getDisplayName()
	{
		return 'First class';
	}

	public function isAvailable(Order $order)
	{
		return ($order->addresses->getByType('delivery')->countryID == 'GB') && $this->_inRange($order);
	}

	public function getPrice()
	{
		return 6.5;
	}
}