<?php

namespace phloat\common;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
abstract class Event
{
	const ANY = '*';

	protected $calls = 0;

	public function increaseCalls()
	{
		++$this->calls;
	}
}