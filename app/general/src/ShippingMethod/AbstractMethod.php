<?php

namespace App\General\ShippingMethod;

use Message\Mothership\Commerce\Shipping\MethodInterface;
use Message\Mothership\Commerce\Order\Order;
use Message\Cog\Location\CountryList;

abstract class AbstractMethod implements MethodInterface
{
	protected $_min = 0;
	protected $_max = 99999999999999999;

	/**
	 * @var \Message\Cog\Location\CountryList
	 */
	protected $_countryList;

	public function __construct(CountryList $countryList)
	{
		$this->_countryList = $countryList;
	}

	protected function _inRange(Order $order)
	{
		$weight = $this->_getWeight($order);

		return ($weight >= $this->_min) && ($weight <= $this->_max);
	}

	private function _getWeight(Order $order)
	{
		$weight = 0;

		foreach ($order->items as $item) {
			$weight += $item->weight;
		}

		return $weight;
	}
}