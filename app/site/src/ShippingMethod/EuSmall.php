<?php

namespace Mothership\Site\ShippingMethod;

use Message\Mothership\Commerce\Order\Order;

class EuSmall extends AbstractMethod
{
	protected $_max = 999;

	public function getName()
	{
		return 'eu_small';
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
		return 13;
	}
}