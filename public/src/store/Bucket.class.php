<?php
namespace store;

use exception\file\FileNotFoundException;
use exception\InvalidValueException;
use exception\MAPException;
use store\data\File;
use xml\Node;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Bucket {

	const PATTERN_GROUP = '/^[A-Za-z0-9_\-.]{1,32}$/';
	const PATTERN_KEY   = '/^[A-Za-z0-9_\-.]{1,32}$/';

	/**
	 * array[group:string][key:string] => value:mixed
	 *
	 * @var array (see above)
	 */
	private $data = array();

	/**
	 * @see Bucket::applyIni
	 * @see Bucket::applyArray
	 */
	public function __construct($applyData = null) {
		if ($applyData instanceof Bucket) {
			$applyData = $applyData->toArray();
		}

		if (is_array($applyData)) {
			$this->applyArray($applyData);
		}
		elseif ($applyData instanceof File) {
			$this->applyIni($applyData);
		}
	}

	final public function get(string $group, string $key, $default = null) {
		return $this->data[$group][$key] ?? $default;
	}

	/**
	 * @throws InvalidValueException
	 */
	final public function set(string $group, string $key, $value, bool $mergeArrays = false):Bucket {
		if (!preg_match(self::PATTERN_GROUP, $group)) {
			throw new InvalidValueException(self::PATTERN_GROUP, $group);
		}
		if (!preg_match(self::PATTERN_KEY, $key)) {
			throw new InvalidValueException(self::PATTERN_KEY, $key);
		}

		if ($mergeArrays && is_array($value) && $this->isArray($group, $key)) {
			$value = array_merge($this->get($group, $key), $value);
		}

		$this->data[$group][$key] = $value;
		return $this;
	}

	final public function isGroup(string ...$group):bool {
		foreach ($group as $g) {
			if (!isset($this->data[$g])) {
				return false;
			}
		}
		return true;
	}

	final public function isNull(string $group, string $key):bool {
		return is_null($this->get($group, $key));
	}

	final public function isArray(string $group, string $key):bool {
		return is_array($this->get($group, $key));
	}

	final public function isString(string $group, string $key):bool {
		return is_string($this->get($group, $key));
	}

	final public function isInt(string $group, string $key):bool {
		return is_int($this->get($group, $key));
	}

	final public function isBool(string $group, string $key):bool {
		return is_bool($this->get($group, $key));
	}

	final public function isTrue(string $group, string $key):bool {
		return $this->get($group, $key) === true;
	}

	final public function isFalse(string $group, string $key):bool {
		return $this->get($group, $key) === false;
	}

	final public function getGroupCount():int {
		return count($this->data);
	}

	final public function getKeyCount(string ...$group):int {
		$count = 0;
		foreach ($group as $g) {
			if ($this->isGroup($g)) {
				$count += count($this->data[$g]);
			}
		}
		return $count;
	}

	final public function remove(string $group, string ...$key):Bucket {
		foreach ($key as $k) {
			unset($this->data[$group][$k]);
		}
		
		if ($this->getKeyCount($group) === 0) {
			$this->removeGroup($group);
		}
		return $this;
	}

	final public function removeGroup(string ...$group):Bucket {
		foreach ($group as $g) {
			unset($this->data[$g]);
		}
		return $this;
	}

	/**
	 * ignores keys without group
	 *
	 * @see    Bucket::$data
	 * @throws InvalidValueException
	 */
	final public function applyArray(array $data):Bucket {
		foreach ($data as $group => $keyList) {
			if (is_array($keyList)) {
				foreach ($keyList as $key => $value) {
					$this->set($group, $key, $value, true);
				}
			}
		}
		return $this;
	}

	/**
	 * @see    Bucket::applyArray
	 * @throws FileNotFoundException
	 * @throws InvalidValueException
	 */
	final public function applyIni(File $iniFile):Bucket {
		if ($iniFile === null || !$iniFile->isFile()) {
			throw new FileNotFoundException($iniFile);
		}
		return $this->applyArray(parse_ini_file($iniFile->getRealPath(), true, INI_SCANNER_TYPED));
	}

	/**
	 * @see Bucket::$data
	 */
	final public function toArray():array {
		return $this->data;
	}

	final public function toNode(string $nodeName):Node {
		$node = new Node($nodeName);
		foreach ($this->toArray() as $group => $keyList) {
			$groupNode = $node->addChild(new Node($group));
			foreach ($keyList as $key => $value) {
				$groupNode
						->addChild(new Node($key))
						->setContent($value);
			}
		}
		return $node;
	}

	/**
	 * @throws MAPException
	 */
	final public function toJson():string {
		$json = json_encode($this->toArray());
		if ($json === false) {
			throw new MAPException('failed to encode to json');
		}
		return $json;
	}

}
