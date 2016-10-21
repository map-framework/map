<?php
namespace data;

use util\MAPException;

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

	/**
	 * @throws MAPException
	 */
	final public static function isMatching(string $pattern, string ...$data):bool {
		foreach ($data as $d) {
			$isMatching = preg_match('/'.$pattern.'/', $d);

			if ($isMatching === false) {
				throw (new MAPException('invalid pattern'))
						->setData('pattern', $pattern);
			}
			if (!$isMatching) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws MAPException
	 * @throws InvalidDataException
	 */
	final public static function assertIsMatching(string $pattern, string ...$data) {
		if (!self::isMatching($pattern, ...$data)) {
			throw new InvalidDataException($pattern, ...$data);
		}
	}

}
