<?php

namespace phloat\actions\log;

use phloat\common\Action;
use phloat\common\Event;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class LogAction extends Action
{
	public function run(Event $event)
	{
		echo 'Event invoked: ' , get_class($event) , PHP_EOL;
	}
}