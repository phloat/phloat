<?php

namespace phloat\actions\log;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class LogEntry
{
	protected $logLevel;
	protected $message;
	protected $context;

	/**
	 * @param string $logLevel
	 * @param string $message
	 * @param array $context
	 */
	public function __construct($logLevel, $message, array $context = array())
	{
		$this->logLevel = $logLevel;
		$this->message = $message;
		$this->context = $context;
	}

	/**
	 * @return string
	 */
	public function getLogLevel()
	{
		return $this->logLevel;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return array
	 */
	public function getContext()
	{
		return $this->context;
	}
}