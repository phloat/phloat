<?php

namespace phloat\actions\log;

use phloat\common\Event;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class LogEvent extends Event
{
	protected $logEntry;
	
	public function __construct(LogEntry $logEntry)
	{
		$this->logEntry = $logEntry;
	}

	/**
	 * @return LogEntry
	 */
	public function getLogEntry()
	{
		return $this->logEntry;
	}
}

/* EOF */