<?php

namespace phloat\common;

use phloat\binds\Bind;
use phloat\binds\BindAny;
use phloat\events\PHPErrorOccurredEvent;
use phloat\events\StartUpEvent;
use phloat\events\ShutdownEvent;
use phloat\events\ExceptionThrownEvent;
use phloat\exceptions\FlowConstructionException;
use phloat\exceptions\FlowException;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class Flow
{
	/** @var array[] */
	protected $reactions = array();
	/** @var string[] */
	protected $eventTree = array();
	protected $running = false;

	protected $executedActions = 0;
	protected $dispatchedEvents = 0;

	protected $errorHandler;
	protected $exceptionHandler;
	protected $handlePHPErrors = true;
	protected $handleExceptions = true;

	protected $defaultBind;

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

		$this->defaultBind = new BindAny();
	}

	/**
	 * Analyzes the Action object and stores it
	 *
	 * @param string $name
	 * @param Action $action
	 *
	 * @param Bind $bind
	 * @return string
	 * @throws FlowConstructionException
	 */
	protected function analyzeAndGetBinds($name, Action $action, Bind $bind)
	{
		$callable = $action->getRunClosure();

		if(is_object($callable) === true && $callable instanceof \Closure) {
			$refFunc = new \ReflectionFunction($callable);
		} elseif(is_array($callable) === true && is_callable($callable) === true) {
			$refFunc = new \ReflectionMethod($callable[0], $callable[1]);
		} else {
			throw new FlowConstructionException('Action ' . $name . ': No valid callback');
		}

		if(($paramCount = count(($params = $refFunc->getParameters()))) !== 1) {
			throw new FlowConstructionException('Action ' . $name . ': The closure has to consume exactly 1 parameter (' . $paramCount . ' given)');
		}

		$eventClass = $refFunc->getParameters()[0]->getClass();

		if($eventClass->name !== Event::class && $eventClass->isSubclassOf(Event::class) === false)
			throw new FlowConstructionException('Action ' . $name . ': The closure should consume a parameter of (sub-)type ' . Event::class . ' but does of type ' . $eventClass->name);

		$possibleEvents = isset($this->eventTree[$eventClass->name]) ? $this->eventTree[$eventClass->name] : ($this->eventTree[$eventClass->name] = $this->getParents($eventClass));

		$bindEvents = array();

		foreach($possibleEvents as $event) {
			if($bind->bindsTo($event) === false)
				continue;

			$bindEvents[] = $event;
		}

		return $eventClass->name;
	}

	/**
	 * Add an action to the end of the flow
	 *
	 * @param string $name Name of the action
	 * @param Action $action The actual action
	 * @param Bind $bind
	 *
	 * @return Flow $this The current flow instance
	 *
	 * @throws FlowConstructionException
	 */
	public function addAction($name, Action $action, Bind $bind = null)
	{
		if(isset($this->reactions[$name]) === true)
			throw new FlowConstructionException('Action with name ' . $name . ' does already exist in this flow');

		$action->setName($name);
		$action->setFlow($this);

		$eventBind = $this->analyzeAndGetBinds($name, $action, ($bind === null) ? $this->defaultBind : $bind);

		$this->reactions[$eventBind][$name] = $action;

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
	 * @param Bind $bind
	 *
	 * @return $this
	 * @throws FlowConstructionException
	 */
	public function injectActionBefore($name, Action $action, $beforeActionName, Bind $bind = null)
	{
		$pos = array_search($beforeActionName, array_keys($this->reactions));

		$eventBind = $this->analyzeAndGetBinds($name, $action, ($bind === null) ? $this->defaultBind : $bind);

		if(isset($this->reactions[$eventBind]) === false)
			$this->reactions[$eventBind][$name] = $action;
		else
			$this->injectArrayEntry($this->reactions[$eventBind], $pos, $action, $name);

		return $this;
	}

	/**
	 * Inject an action after another one in the flow
	 *
	 * @param string $name Name of the action
	 * @param Action $action The actual action
	 * @param string $afterActionName
	 * @param Bind $bind
	 *
	 * @return $this
	 * @throws FlowConstructionException
	 */
	public function injectActionAfter($name, Action $action, $afterActionName, Bind $bind = null)
	{
		$pos = array_search($afterActionName, array_keys($this->reactions)) + 1;

		$eventBind = $this->analyzeAndGetBinds($name, $action, ($bind === null) ? $this->defaultBind : $bind);

		if(isset($this->reactions[$eventBind]) === false)
			$this->reactions[$eventBind][$name] = $action;
		else
			$this->injectArrayEntry($this->reactions[$eventBind], $pos, $action, $name);

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
			throw new FlowConstructionException('Action with name ' . $name . ' does not exist in this flow');

		$this->analyzeAndGetBinds($name, $action, $this->reactions[$name]['weight']);

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
			throw new FlowConstructionException('Action with name ' . $name . ' does not exist in this flow');

		unset($this->reactions[$name]);

		return $this;
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

		foreach($eventClasses as $eventClass) {
			if(isset($this->reactions[$eventClass]) === false) {
				continue;
			}

			foreach($this->reactions[$eventClass] as $name => $action) {
				++$this->executedActions;

				/** @var Action $action */
				call_user_func($action->getRunClosure(), $event);
			}
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