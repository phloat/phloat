<?php

namespace phloat\events;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class PHPErrorOccurredEvent extends FlowEvent
{
	protected $no;
	protected $message;
	protected $file;
	protected $line;
	protected $context;

	public function __construct($no, $message, $file, $line, array $context)
	{
		parent::__construct();
		
		$this->no = $no;
		$this->message = $message;
		$this->file = $file;
		$this->line = $line;
		$this->context = $context;
	}

	/**
	 * @return int
	 */
	public function getNo()
	{
		return $this->no;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return int
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * @return array
	 */
	public function getContext()
	{
		return $this->context;
	}
}