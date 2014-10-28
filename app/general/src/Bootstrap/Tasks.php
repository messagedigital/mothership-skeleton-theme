<?php

namespace App\General\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use App\General\Task;

class Tasks implements TasksInterface
{
	public function registerTasks($tasks)
	{
		// Porting
		$tasks->add(new Task\Porting('rsar:port-data'), 'Ports products from spreadsheet');
		$tasks->add(new Task\FixPageNames('rsar:fix-page-names'), 'Adds colour to product pages');
	}
}