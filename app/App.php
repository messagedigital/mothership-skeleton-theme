<?php

class App extends \Message\Cog\Application\Loader
{
	protected function _registerModules()
	{
		return array(
			'Message\\ImageResize',
			'Message\\User',
			'Message\\Mothership\\User',
			'Message\\Mothership\\ControlPanel',
			'Message\\Mothership\\FileManager',
			'Message\\Mothership\\CMS',
			'Message\\Mothership\\Commerce',
			'Message\\Mothership\\Ecommerce',
			'Message\\Mothership\\OrderReturn',
			'App\\General'
		);
	}
}