<?php

namespace phloat\actions\closure;

use phloat\common\Action;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class ClosureAction extends Action
{
	protected $closure;

	public function __construct(callable $closure)
	{
		$this->closure = $closure;
	}


	/**
	 * @return callable
	 */
	public function getClosure()
	{
		return $this->closure;
	}
}