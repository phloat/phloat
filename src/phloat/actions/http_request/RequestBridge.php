<?php

namespace phloat\actions\http_request;

use Psr\Http\Message\RequestInterface;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
interface RequestBridge
{
	/**
	 * @return RequestInterface
	 */
	public static function createFromGlobals();
}

/* EOF */