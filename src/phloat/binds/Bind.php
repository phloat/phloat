<?php

namespace phloat\binds;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
abstract class Bind
{
	/**
	 * @param string $event
	 * @return bool
	 */
	public abstract function bindsTo($event);
}