<?php
namespace data\map;

use data\AbstractData;
use data\file\TypeEnum;
use data\InvalidDataException;
use MAPAutoloader;
use util\Bucket;
use data\file\File;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class AddOn extends AbstractData {

	const PATTERN_NAME = '[A-Za-z\-_]{1,32}';

	const PATH_CONFIG = 'addon.ini';

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

	/**
	 * @throws InvalidDataException
	 */
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

	final public function getMinVersion():Version {
		return is_object($this->minVersion) ? clone $this->minVersion : null;
	}

	final public function setMaxVersion(Version $max):AddOn {
		$this->maxVersion = $max;
		return $this;
	}

	final public function getMaxVersion():Version {
		return is_object($this->maxVersion) ? clone $this->maxVersion : null;
	}

	final public function getDir():File {
		return (new File(MAPAutoloader::PATH_ADDONS))
				->attach($this->get());
	}

	final public function getConfigFile():File {
		return $this->getDir()
				->attach(self::PATH_CONFIG);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function getList():array {
		$addOnRootDir = new File(MAPAutoloader::PATH_ADDONS);
		if ($addOnRootDir->isDir()) {
			foreach ($addOnRootDir->scanDir(new TypeEnum(TypeEnum::DIR)) as $addOnDir) {
				if ($addOnDir instanceof File) {
					$addonList[] = new AddOn($addOnDir->getShortName());
				}
			}
		}
		return $addonList ?? array();
	}

	final public function hasMinVersion():bool {
		return $this->minVersion !== null;
	}

	final public function hasMaxVersion():bool {
		return $this->maxVersion !== null;
	}

	/**
	 * @throws InvalidDataException
	 * @throws DependencyException
	 */
	final public function isInstalled():bool {
		if (!$this->getDir()->isDir() || !$this->getConfigFile()->isFile()) {
			return false;
		}
		$group = 'addon-'.$this->getName();

		$config = new Bucket($this->getConfigFile());
		$config->assertIsString($group, 'version');

		$version = new Version($config->get($group, 'version'));
		if ($this->hasMinVersion() && $this->getMinVersion()->isGreater($version)) {
			return false;
		}
		if ($this->hasMaxVersion() && $this->getMaxVersion()->isLess($version)) {
			return false;
		}

		# Dependencies
		foreach ($config->getKeyList($group) as $key) {
			if (AbstractData::isMatching('dep\-'.self::PATTERN_NAME, $key)) {
				$config->assertIsArray($group, $key);
				$min        = $config->get($group, $key)['min'] ?? null;
				$max        = $config->get($group, $key)['max'] ?? null;
				$dependency = new AddOn(
						substr($key, 4),
						$min !== null ? new Version($min) : null,
						$max !== null ? new Version($max) : null
				);

				$dependency->assertIsInstalled();
			}
		}
		return true;
	}

	/**
	 * @throws DependencyException
	 */
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
