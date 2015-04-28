<?php

namespace phloat\events;

use phloat\common\Event;
use phloat\common\Flow;
use phloat\exceptions\FlowException;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
abstract class FlowEvent extends Event
{
	public function __construct()
	{
		if(($calledClass = debug_backtrace()[1]['class']) !== Flow::class && is_subclass_of($calledClass, Flow::class) === false)
			throw new FlowException(__CLASS__ . ' is an internal event and can only be called by the flow itself');
	}
}