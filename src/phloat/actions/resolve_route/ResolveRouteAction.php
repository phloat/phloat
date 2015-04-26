<?php

namespace phloat\actions\resolve_route;

use phloat\actions\http_request\RequestAction;
use phloat\common\Action;
use phloat\common\DependableAction;
use phloat\common\Event;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class ResolveRouteAction extends Action implements DependableAction
{

	public function run(Event $event)
	{
		// TODO: Implement run() method.
	}

	/**
	 * @return string[]
	 */
	public static function dependsOn()
	{
		return array(
			RequestAction::class
		);
	}
}