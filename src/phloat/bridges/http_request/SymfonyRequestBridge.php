<?php

namespace phloat\bridges\http_request;

use phloat\actions\http_request\RequestBridge;
use Psr\Http\Message\RequestInterface;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class SymfonyRequestBridge implements RequestBridge
{
	/**
	 * @return RequestInterface
	 */
	public static function createFromGlobals()
	{
		return PsrRequest::createFromGlobals();
	}
}

/* EOF */