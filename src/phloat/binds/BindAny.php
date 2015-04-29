<?php

namespace phloat\binds;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class BindAny extends Bind
{
	/**
	 * @param string $event
	 * @return bool
	 */
	public function bindsTo($event)
	{
		return true;
	}
}