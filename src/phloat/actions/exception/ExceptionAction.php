<?php

namespace phloat\actions\exception;

use phloat\common\Action;
use phloat\common\Event;
use phloat\events\ExceptionThrownEvent;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class ExceptionAction extends Action
{
	public function run(Event $event)
	{
		if(!$event instanceof ExceptionThrownEvent)
			return;

		echo 'Flow terminated with exception "' . $event->getException()->getMessage() . '"', PHP_EOL;
	}
}