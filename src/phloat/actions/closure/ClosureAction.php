<?php

namespace phloat\actions\closure;

use phloat\common\Action;
use phloat\common\ConditionalAction;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class ClosureAction extends Action implements ConditionalAction
{
	protected $runClosure;
	protected $conditionClosure;

	public function __construct(callable $runClosure, callable $conditionClosure = null)
	{
		$this->runClosure = $runClosure;
		$this->conditionClosure = $conditionClosure;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getRunClosure()
	{
		return $this->runClosure;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConditionClosure()
	{
		return $this->conditionClosure;
	}
}