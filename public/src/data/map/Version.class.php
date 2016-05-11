<?php
namespace data\map;

use data\AbstractData;
use data\InvalidDataException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Version extends AbstractData {

	const PATTERN_VERSION = '^[0-9]{1,5}\.[0-9]{1,5}\.[0-9]{1,5}$';

	/**
	 * @var int
	 */
	private $major;

	/**
	 * @var int
	 */
	private $minor;

	/**
	 * @var int
	 */
	private $micro;

	/**
	 * @throws InvalidDataException
	 */
	public function set(string $version) {
		self::assertIsVersion($version);

		$explodedVersion = explode('.', $version);
		$this->major     = (int) $explodedVersion[0];
		$this->minor     = (int) $explodedVersion[1];
		$this->micro     = (int) $explodedVersion[2];
	}

	public function get():string {
		return $this->getMajor().'.'.$this->getMinor().'.'.$this->getMicro();
	}

	final public function getMajor():int {
		return $this->major;
	}

	final public function getMinor():int {
		return $this->minor;
	}

	final public function getMicro():int {
		return $this->micro;
	}

	final public function isEqual(Version $version = null):bool {
		if ($version === null) {
			return true;
		}
		return version_compare($this, $version, '==');
	}

	final public function isGreater(Version $version = null):bool {
		if ($version === null) {
			return true;
		}
		return version_compare($this, $version, '>');
	}

	final public function isGreaterOrEqual(Version $version = null):bool {
		if ($version === null) {
			return true;
		}
		return version_compare($this, $version, '>=');
	}

	final public function isLess(Version $version = null):bool {
		if ($version === null) {
			return true;
		}
		return version_compare($this, $version, '<');
	}

	final public function isLessOrEqual(Version $version = null):bool {
		if ($version === null) {
			return true;
		}
		return version_compare($this, $version, '<=');
	}

	final public function isBetweenOrEqual(Version $min = null, Version $max = null):bool {
		return $this->isGreaterOrEqual($min) && $this->isLessOrEqual($max);
	}

	final public static function isVersion(string $version):bool {
		return self::isMatching(self::PATTERN_VERSION, $version);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsVersion(string $version) {
		self::assertIsMatching(self::PATTERN_VERSION, $version);
	}

}
