<?php

namespace App\General\ShippingMethod;

use Message\Mothership\Commerce\Order\Order;

class RowSmall extends AbstractMethod
{
	protected $_max = 999;

	public function getName()
	{
		return 'row_small';
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
		return 17;
	}
}