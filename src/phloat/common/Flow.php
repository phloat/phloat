<?php

namespace phloat\common;

use phloat\events\ExceptionThrownEvent;
use phloat\events\ShutdownEvent;
use phloat\events\StartUpEvent;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class Flow implements \ArrayAccess
{
	/** @var Action[] */
	protected $actions = array();
	protected $persistence = array();
	protected $eventLog = array();
	protected $booted = false;

	public function boot()
	{
		if($this->booted === true)
			return;

		$this->booted = true;

		try {
			$this->invokeEvent(new StartUpEvent());
			$this->invokeEvent(new ShutdownEvent());
		} catch(\Exception $e) {
			$this->invokeEvent(new ExceptionThrownEvent($e));
		}
	}

	public function add(Action $action)
	{
		$this->actions[] = $action;

		$action->setFlow($this);

		return $this;
	}

	public function invokeEvent(Event $event)
	{
		$eventClassName = get_class($event);
		$this->eventLog[$eventClassName][] = $event;

		$reactingActions = array();

		foreach($this->actions as $action) {
			if(($weight = $action->reactsTo($eventClassName)) === 0 && ($weight = $action->reactsTo(Event::ANY)) === 0)
				continue;

			$reactingActions[] = array(
				'action' => $action, 'weight' => $weight
			);
		}

		uasort($reactingActions, function($a, $b) {
			if($a['weight'] === $b['weight'])
				return 0;

			return ($a['weight'] < $b['weight']) ? -1 : 1;
		});

		foreach($reactingActions as $action) {
			$action['action']->run($event);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->persistence);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->persistence[$offset];
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		$this->persistence[$offset] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		unset($this->persistence[$offset]);
	}
}