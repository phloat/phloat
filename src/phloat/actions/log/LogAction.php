<?php

namespace phloat\actions\log;

use phloat\common\Action;
use phloat\exceptions\FlowException;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class LogAction extends Action
{
	protected $logger;
	
	public function __construct(AbstractLogger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return callable
	 */
	public function getRunClosure()
	{
		return function(LogEvent $event)
		{
			$entry = $event->getLogEntry();
			$logLevel = $entry->getLogLevel();
			
			$callable = array($this->logger, $logLevel);
			
			if(is_callable($callable) === false)
				throw new FlowException('Illegal log level. Must be one defined in ' . LogLevel::class);
			
			$this->logger->{$logLevel}($entry->getMessage(), $entry->getContext());
		};
	}
}