<?php

namespace phloat\common;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
interface DependableAction
{
	/**
	 * @return string[]
	 */
	public static function dependsOn();
}