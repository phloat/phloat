<?php

namespace phloat\actions\http_request;

use phloat\common\Event;
use Psr\Http\Message\RequestInterface;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class RequestCreationSucceededEvent extends Event
{
	protected $request;
	
	public function __construct(RequestInterface $request)
	{
		$this->request = $request;
	}

	/**
	 * @return RequestInterface
	 */
	public function getRequest()
	{
		return $this->request;
	}
}