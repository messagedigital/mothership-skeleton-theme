<?php
<<<<<<< HEAD

=======
>>>>>>> e4c26e5b331de3bff71afe98a4fb42cc4efeec2f
class App extends \Message\Cog\Application\Loader
{
	protected function _registerModules()
	{
<<<<<<< HEAD
		return array(
=======
		return [
>>>>>>> e4c26e5b331de3bff71afe98a4fb42cc4efeec2f
			'Message\\ImageResize',
			'Message\\User',
			'Message\\Mothership\\User',
			'Message\\Mothership\\ControlPanel',
			'Message\\Mothership\\FileManager',
			'Message\\Mothership\\CMS',
			'Message\\Mothership\\Commerce',
			'Message\\Mothership\\Ecommerce',
			'Message\\Mothership\\OrderReturn',
<<<<<<< HEAD
			'Mothership\\Site'
		);
=======
			'Message\\Mothership\\Voucher',
			'Message\\Mothership\\Discount',
			'Message\\Mothership\\Report',
			'Mothership\\Site',
		];
>>>>>>> e4c26e5b331de3bff71afe98a4fb42cc4efeec2f
	}
}