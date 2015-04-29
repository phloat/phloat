<?php

namespace phloat\binds;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class BindNoneOf extends Bind
{
	protected $eventBlackList = array();

	/**
	 * @param string[] $eventBlackList
	 */
	public function __construct(array $eventBlackList)
	{
		$this->eventBlackList = $eventBlackList;
	}

	/**
	 * @param string $event
	 * @return bool
	 */
	public function bindsTo($event)
	{
		return !in_array($event, $this->eventBlackList);
	}
}