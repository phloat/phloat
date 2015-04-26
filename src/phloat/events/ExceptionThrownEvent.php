<?php

namespace phloat\events;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class ExceptionThrownEvent extends FlowEvent
{
	protected $exception;

	public function __construct(\Exception $e)
	{
		$this->exception = $e;
	}

	public function getException()
	{
		return $this->exception;
	}
}