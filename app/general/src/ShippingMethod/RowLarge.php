<?php

namespace Mothership\Site\ShippingMethod;

use Message\Mothership\Commerce\Order\Order;

class RowLarge extends AbstractMethod
{
	protected $_min = 1000;

	public function getName()
	{
		return 'row_large';
	}

	public function getDisplayName()
	{
		return 'Signed and tracked';
	}

	public function isAvailable(Order $order)
	{
		$countryID = $order->addresses->getByType('delivery')->countryID;

		if ($this->_countryList->isInEU($countryID)) {
			return false;
		}

		return $this->_inRange($order);
	}

	public function getPrice()
	{
		return 22;
	}
}