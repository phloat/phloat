<?php

namespace phloat\common;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015 by TiMESPLiNTER Webdevelopment
 */
class Phloat implements \ArrayAccess
{
	protected $persistence = array();

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->persistence);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->persistence[$offset];
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		$this->persistence[$offset] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		unset($this->persistence[$offset]);
	}
}