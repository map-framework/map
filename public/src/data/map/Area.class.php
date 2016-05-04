<?php
namespace data\map;

use data\AbstractData;
use data\file\File;
use data\InvalidDataException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Area extends AbstractData {

	const PATTERN_NAME = '^[0-9A-Za-z_\-+]{1,32}$';

	const PATH_DIR    = 'private/src/area/';
	const PATH_CONFIG = 'config/area.ini';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @throws InvalidDataException
	 */
	public function set(string $name) {
		self::assertIsName($name);

		$this->name = $name;
	}

	public function get():string {
		return $this->name;
	}

	final public function getDir():File {
		return (new File(self::PATH_DIR))
				->attach($this->get());
	}

	final public function getConfigFile():File {
		return $this->getDir()
				->attach(self::PATH_CONFIG);
	}

	final public function exists():bool {
		return $this->getDir()->isDir();
	}

	final public static function isName(string $name):bool {
		return self::isMatching(self::PATTERN_NAME, $name);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsName(string $name) {
		self::assertIsMatching(self::PATTERN_NAME, $name);
	}

}
