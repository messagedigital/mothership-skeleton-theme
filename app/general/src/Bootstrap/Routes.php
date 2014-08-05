<?php

namespace RSAR\General\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

class Routes implements RoutesInterface, ContainerAwareInterface
{
	protected $_services;

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function registerRoutes($router)
	{
//		$this->enableSSL($router);

		$router->add('rsar.subscribe.action', '/mailing-list/subscribe', 'RSAR:General::Controller:Module:Subscribe#subscribeAction')
			->setMethod('POST');
	}

//	public function enableSSL($router)
//	{
//		// Skip if not in live or dev environment
//		// TODO: remove dev from this list once the PR is approved
//		if (!in_array($this->_services['environment']->get(), ['live'])) {
//			return false;
//		}
//
//		$router->getDefault()->setSchemes(['https']);
//
//		foreach ($router as $key => $collection) {
//			$router[$key]->setSchemes(['https']);
//		}
//	}
}