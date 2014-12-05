<?php

namespace App\General\ShippingMethod;

use Message\Mothership\Commerce\Order\Order;

class UkSmall extends AbstractMethod
{
	protected $_max = 999;

	public function getName()
	{
		return 'uk_small';
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
		return 4;
	}
}