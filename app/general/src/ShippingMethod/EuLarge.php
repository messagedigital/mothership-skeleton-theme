<?php

namespace Mothership\Site\ShippingMethod;

use Message\Mothership\Commerce\Order\Order;

class EuLarge extends AbstractMethod
{
	protected $_min = 1000;

	public function getName()
	{
		return 'eu_large';
	}

	public function getDisplayName()
	{
		return 'Tracked';
	}

	public function isAvailable(Order $order)
	{
		$countryID = $order->addresses->getByType('delivery')->countryID;

		if ($countryID == 'GB') {
			return false;
		}

		if (!$this->_countryList->isInEU($countryID)) {
			return false;
		}

		return $this->_inRange($order);
	}

	public function getPrice()
	{
		return 16;
	}
}