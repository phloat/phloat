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
	protected $running = false;

	protected $executedActions = 0;
	protected $dispatchedEvents = 0;

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

	/**
	 * Analyzes the Action object and stores it
	 *
	 * @param string $name
	 * @param Action $action
	 *
	 * @throws FlowException
	 */
	protected function analyzeAction($name, Action $action)
	{
		$callable = $action->getRunClosure();

		if(is_object($callable) === true && $callable instanceof \Closure) {
			$refFunc = new \ReflectionFunction($callable);
		} elseif(is_array($callable) === true && is_callable($callable) === true) {
			$refFunc = new \ReflectionMethod($callable[0], $callable[1]);
		} else {
			throw new FlowException('Action ' . $name . ': No valid callback');
		}

		if(($paramCount = count(($params = $refFunc->getParameters()))) !== 1) {
			throw new FlowException('Action ' . $name . ': The closure has to consume exactly 1 parameter (' . $paramCount . ' given)');
		}

		$eventClass = $refFunc->getParameters()[0]->getClass();

		if($eventClass->name !== Event::class && $eventClass->isSubclassOf(Event::class) === false)
			throw new FlowException('Action ' . $name . ': The closure should consume a parameter of (sub-)type ' . Event::class . ' but does of type ' . $eventClass->name);

		if(isset($this->eventTree[$eventClass->name]) === false)
			$this->eventTree[$eventClass->name] = $this->getParents($eventClass);
		
		return $eventClass->name;
	}

	/**
	 * Add an action to the end of the flow
	 * 
	 * @param string $name Name of the action
	 * @param Action $action The actual action
	 *
	 * @return Flow $this The current flow instance
	 *
	 * @throws FlowException
	 */
	public function addAction($name, Action $action)
	{
		if(isset($this->reactions[$name]) === true)
			throw new FlowException('Action with name ' . $name . ' does already exist in this flow');

		$eventClassName = $this->analyzeAction($name, $action);

		$action->setName($name);
		$action->setFlow($this);

		$this->reactions[$name] = array('event' => $eventClassName, 'action' => $action);
		
		return $this;
	}

	protected function injectArrayEntry(array &$array, $pos, $entry, $key)
	{		
		$array = array_slice($array, 0, $pos, true) +
		         array($key => $entry) +
		         array_slice($array, $pos, count($array)-$pos, true);
	}
	
	/**
	 * Inject an action before another one in the flow
	 * 
	 * @param string $name Name of the action
	 * @param Action $action The actual action
	 * @param string $beforeActionName
	 *
	 * @return $this
	 */
	public function injectActionBefore($name, Action $action, $beforeActionName)
	{
		$pos = array_search($beforeActionName, array_keys($this->reactions));
				
		$eventClassName = $this->analyzeAction($name, $action);

		$this->injectArrayEntry($this->reactions, $pos, array('event' => $eventClassName, 'action' => $action), $name);
		
		return $this;
	}

	/**
	 * Inject an action after another one in the flow
	 * 
	 * @param string $name Name of the action
	 * @param Action $action The actual action
	 * @param string $afterActionName
	 *
	 * @return $this
	 */
	public function injectActionAfter($name, Action $action, $afterActionName)
	{
		$pos = array_search($afterActionName, array_keys($this->reactions)) + 1;

		$eventClassName = $this->analyzeAction($name, $action);

		$this->injectArrayEntry($this->reactions, $pos, array('event' => $eventClassName, 'action' => $action), $name);
		
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

		$this->analyzeAction($name, $action, $this->reactions[$name]['weight']);

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
	 * Returns an actions weight
	 * 
	 * @param string $name
	 *
	 * @return int
	 * @throws FlowException
	 */
	public function getActionsWeight($name)
	{
		if(isset($this->reactions[$name]) === false)
			throw new FlowException('Action with name ' . $name . ' does not exist in this flow');

		return $this->reactions[$name]['weight'];
	}

	/**
	 * Starts the flow
	 */
	public function start()
	{
		if($this->running === true)
			return;

		$this->running = true;

		if($this->handlePHPErrors === true)
			set_error_handler($this->errorHandler);

		if($this->handleExceptions === true)
			set_exception_handler(function(\Exception $e) {
				if($e instanceof FlowException)
					throw $e;
				
				$eh = $this->exceptionHandler;
				$eh($e);
			});
		
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

		if($this->running === false)
			return;
		
		$eventClass = get_class($event);
		$eventClasses = isset($this->eventTree[$eventClass]) ? $this->eventTree[$eventClass] : array();

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

		$this->running = false;
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