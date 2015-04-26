<?php

namespace phloat\common;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
abstract class Action
{
	protected $name;

	/** @var Flow2 */
	protected $flow;

	/**
	 * @return callable
	 */
	abstract public function getClosure();

	public function setFlow(Flow2 $flow)
	{
		$this->flow = $flow;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
}