<?php

namespace phloat\binds;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class BindAnyOf extends Bind
{
	protected $eventWhiteList = array();

	/**
	 * @param string[] $eventList List of events to which this action should be bound
	 */
	public function __construct(array $eventList)
	{
		$this->eventWhiteList = $eventList;
	}

	/**
	 * @param string $event
	 * @return bool
	 */
	public function bindsTo($event)
	{
		return in_array($event, $this->eventWhiteList);
	}
}