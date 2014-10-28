<?php

namespace App\General\Bootstrap;

use App\General\EventListener;

use Message\Cog\Bootstrap\EventsInterface;

class Events implements EventsInterface
{
	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new EventListener\Frontend);
	}
}