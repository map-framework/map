<?php
namespace data\map;

use data\AbstractData;
use data\InvalidDataException;
use store\data\File;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class AddOn extends AbstractData {

	const PATTERN_NAME = '[A-Za-z\-_]+';
	const DIR_ADDONS   = 'public/addons';
	const CONFIG_FILE  = 'addon.ini';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var Version
	 */
	private $minVersion;

	/**
	 * @var Version
	 */
	private $maxVersion;

	public function __construct($name, Version $min = null, Version $max = null) {
		parent::__construct($name);
	}

	public function set(string $name) {
		self::assertIsName($name);

		$this->name = $name;
	}

	public function get():string {
		return $this->getName();
	}

	final public function getName():string {
		return $this->name;
	}

	final public function setMinVersion(Version $min):AddOn {
		$this->minVersion = $min;
		return $this;
	}

	final public function getMinVersion() {
		return is_object($this->minVersion) ? clone $this->minVersion : null;
	}

	final public function setMaxVersion(Version $max):AddOn {
		$this->maxVersion = $max;
		return $this;
	}

	final public function getMaxVersion() {
		return is_object($this->maxVersion) ? clone $this->maxVersion : null;
	}

	final public function getDir():File {
		return (new File(self::DIR_ADDONS))
				->attach($this->get());
	}

	final public function getConfigFile():File {
		return $this->getDir()
				->attach(self::CONFIG_FILE);
	}

	final public function isInstalled():bool {
		return $this->getDir()->isDir() && $this->getConfigFile()->isFile();
	}

	final public function assertIsInstalled() {
		if (!$this->isInstalled()) {
			throw new DependencyException($this);
		}
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
