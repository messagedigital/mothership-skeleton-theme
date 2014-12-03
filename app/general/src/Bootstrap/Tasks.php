<?php

namespace Mothership\Site\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Mothership\Site\Task;

class Tasks implements TasksInterface
{
	public function registerTasks($tasks)
	{
		// Porting
		$tasks->add(new Task\Porting('app:port-data'), 'Ports products from spreadsheet');
		$tasks->add(new Task\FixPageNames('app:fix-page-names'), 'Adds colour to product pages');
	}
}