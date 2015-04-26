<?php


namespace phloat\common;


/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
interface ConditionalAction
{
	/**
	 * A closure/callback which returns true or false whether the action should be executed or not
	 *
	 * @return callable
	 */
	public function getConditionClosure();
}