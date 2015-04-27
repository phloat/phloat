<?php

namespace phloat\common;

use phloat\events\PHPErrorOccurredEvent;
use phloat\events\StartUpEvent;
use phloat\events\ShutdownEvent;
use phloat\events\ExceptionThrownEvent;
use phloat\exceptions\FlowException;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class Flow
{
	protected $reactions = array();
	protected $eventTree = array();
	protected $started = false;
	protected $stopped = false;

	protected $executedActions = 0;
	protected $dispatchedEvents = 0;

	protected $highestWeight = 0;
	protected $weightIncrement = 10;

	protected $errorHandler;
	protected $exceptionHandler;
	protected $handlePHPErrors = true;
	protected $handleExceptions = true;

	public function __construct()
	{
		$this->errorHandler = function($no, $message, $file, $line, array $context) {
			// Respect suppressed errors
			if(0 === error_reporting())
				return;

			$this->dispatch(new PHPErrorOccurredEvent($no, $message, $file, $line, $context));
		};
		
		$this->exceptionHandler = function(\Exception $e) {
			$this->dispatch(new ExceptionThrownEvent($e));	
		};
	}

	protected function checkAndStoreAction($name, Action $action, $weight)
	{
		$callable = $action->getRunClosure();

		if(is_object($callable) === true && $callable instanceof \Closure) {
			$refFunc = new \ReflectionFunction($callable);
		} elseif(is_array($callable) === true && is_callable($callable) === true) {
			$refFunc = new \ReflectionMethod($callable[0], $callable[1]);
		} else {
			throw new FlowException('Action ' . $name . ': No valid callback');
		}

		if(count(($params = $refFunc->getParameters())) !== 1) {
			throw new FlowException('Action ' . $name . ': The closure has to consume exactly one parameter');
		}

		$eventClass = $refFunc->getParameters()[0]->getClass();

		if($eventClass->name !== Event::class && $eventClass->isSubclassOf(Event::class) === false)
			throw new FlowException('Action ' . $name . ': The closure should consume a parameter of (sub-)type ' . Event::class . ' but does of type ' . $eventClass->name);

		$action->setName($name);
		$action->setFlow($this);

		$this->reactions[$name] = array('event' => $eventClass->name, 'action' => $action, 'weight' => $weight);

		if(isset($this->eventTree[$eventClass->name]) === false)
			$this->eventTree[$eventClass->name] = $this->getParents($eventClass);
	}

	/**
	 * @param string $name Name of the action
	 * @param Action $action The actual action
	 * @param int $weight
	 *
	 * @return Flow $this The current flow instance
	 *
	 * @throws FlowException
	 */
	public function addAction($name, Action $action, $weight = 0)
	{
		if(isset($this->reactions[$name]) === true)
			throw new FlowException('Action with name ' . $name . ' does already exist in this flow');

		if($weight === 0) {
			$weight = $this->highestWeight = $this->highestWeight + $this->weightIncrement;
		}

		$this->checkAndStoreAction($name, $action, $weight);

		return $this;
	}

	/**
	 * Replaces and existing action with a new one
	 *
	 * @param string $name Name of the action to replace
	 * @param Action $action The new action
	 *
	 * @return $this The current flow instance
	 *
	 * @throws FlowException
	 */
	public function replaceAction($name, Action $action)
	{
		if(isset($this->reactions[$name]) === false)
			throw new FlowException('Action with name ' . $name . ' does not exist in this flow');

		$this->checkAndStoreAction($name, $action, $this->reactions[$name]['weight']);

		return $this;
	}

	/**
	 * Removes an action from the flow
	 *
	 * @param string $name
	 * @return $this The current flow instance
	 * @throws FlowException
	 */
	public function removeAction($name)
	{
		if(isset($this->reactions[$name]) === false)
			throw new FlowException('Action with name ' . $name . ' does not exist in this flow');

		unset($this->reactions[$name]);

		return $this;
	}

	/**
	 * Re-weight an existing action
	 *
	 * @param string $name
	 * @param int $weight
	 * @return $this The current flow instance
	 * @throws FlowException
	 */
	public function reWeightAction($name, $weight)
	{
		if(isset($this->reactions[$name]) === false)
			throw new FlowException('Action with name ' . $name . ' does not exist in this flow');

		$this->reactions[$name]['weight'] = $weight;

		return $this;
	}

	/**
	 * Starts the flow
	 */
	public function start()
	{
		if($this->started === true)
			return;

		$this->started = true;

		uasort($this->reactions, function($a, $b) {
			if($a['weight'] === $b['weight'])
				return 0;

			return ($a['weight'] < $b['weight']) ? -1 : 1;
		});

		if($this->handlePHPErrors === true)
			set_error_handler($this->errorHandler);

		if($this->handleExceptions === true)
			set_exception_handler($this->exceptionHandler);
		
		$this->dispatch(new StartUpEvent());
		$this->stop();

		if($this->handleExceptions === true)
			restore_exception_handler();
		
		if($this->handlePHPErrors === true)
			restore_error_handler();
	}

	/**
	 * Dispatches a new event
	 *
	 * @param Event $event
	 */
	public function dispatch(Event $event)
	{
		++$this->dispatchedEvents;

		if($this->stopped === true)
			return;

		$eventClasses = $this->eventTree[get_class($event)];

		foreach($this->reactions as $reaction) {
			if(in_array($reaction['event'], $eventClasses) === false)
				continue;

			++$this->executedActions;
			/** @var Action $action */
			$action = $reaction['action'];
			
			call_user_func($action->getRunClosure(), $event);
		}
	}

	protected function getParents(\ReflectionClass $class)
	{
		$parents = array($class->name);

		if(($parentClass = $class->getParentClass()) === false)
			return $parents;

		$parents = array_merge($this->getParents($parentClass), $parents);

		return $parents;
	}

	/**
	 * Terminates the flow
	 */
	public function stop()
	{
		$this->dispatch(new ShutdownEvent());

		$this->stopped = true;
	}

	/**
	 * @return int
	 */
	public function getExecutedActions()
	{
		return $this->executedActions;
	}

	/**
	 * @return int
	 */
	public function getDispatchedEvents()
	{
		return $this->dispatchedEvents;
	}

	/**
	 * Defines if triggered PHP errors during the flow should be handled as {@see PHPErrorOccurredEvent}.
	 * (Default is true)
	 * 
	 * @param boolean $handlePHPErrors
	 */
	public function handlePHPErrors($handlePHPErrors)
	{
		$this->handlePHPErrors = $handlePHPErrors;
	}

	/**
	 * Defines if thrown exceptions during the flow should be handled as {@see ExceptionThrownEvent}.
	 * (Default is true)
	 * 
	 * @param boolean $handleExceptions
	 */
	public function handleExceptions($handleExceptions)
	{
		$this->handleExceptions = $handleExceptions;
	}
}