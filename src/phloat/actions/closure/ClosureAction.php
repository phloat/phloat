<?php

namespace phloat\actions\closure;

use phloat\common\Action;
use phloat\common\ConditionalAction;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class ClosureAction extends Action
{
	protected $runClosure;

	public function __construct(callable $runClosure)
	{
		$this->runClosure = $runClosure;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRunClosure()
	{
		return $this->runClosure;
	}
}