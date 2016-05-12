<?php
namespace data;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
abstract class AbstractData {

	abstract public function set(string $data);

	abstract public function get():string;

	public function __construct(string $data) {
		$this->set($data);
	}

	public function __toString():string {
		return $this->get();
	}

	final public static function isMatching(string $pattern, string ...$data):bool {
		foreach ($data as $d) {
			if (!preg_match('/'.$pattern.'/', $d)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsMatching(string $pattern, string ...$data) {
		foreach ($data as $d) {
			if (!self::isMatching($pattern, $d)) {
				throw new InvalidDataException($pattern, $d);
			}
		}
	}

}
