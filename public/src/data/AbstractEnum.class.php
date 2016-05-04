<?php
namespace data;

use exception\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
abstract class AbstractEnum {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @throws MAPException
	 */
	final public function __construct(string $key) {
		$this->assertContains($key);
		$this->key = $key;
	}

	final public function get():string {
		return $this->key;
	}

	final public function contains(string $key):bool {
		return defined(get_class($this).'::'.$key);
	}

	final public function __toString():string {
		return $this->get();
	}

	/**
	 * @throws MAPException
	 */
	final public function assertContains(string $key) {
		if (!$this->contains($key)) {
			throw (new MAPException('This key does not exists.'))
					->setData('key', $key);
		}
	}

}
