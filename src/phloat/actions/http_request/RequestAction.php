<?php

namespace phloat\actions\http_request;

use phloat\common\Action;
use phloat\events\StartUpEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class RequestAction extends Action
{
	protected $requestBridge;
	
	public function __construct(RequestBridge $requestBridge)
	{
		$this->requestBridge = $requestBridge;
	}
	
	/**
	 * @return callable
	 */
	public function getRunClosure()
	{
		return function(StartUpEvent $event) {
			$this->performAction($event);
		};
	}

	protected function performAction(StartUpEvent $event)
	{
		echo 'try to create request' , PHP_EOL;

		if(php_sapi_name() === 'cli')
			throw new \Exception('Could not create request because this runs on CLI');

		$this->flow->dispatch(new RequestCreationSucceededEvent($this->requestBridge->createFromGlobals()));
	}
}