<?php
namespace util;

use data\AbstractData;
use data\file\File;
use data\file\NotFoundException;
use data\file\UnexpectedTypeException;
use data\InvalidDataException;
use data\norm\DataTypeEnum;
use data\norm\InvalidDataTypeException;
use xml\Node;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Bucket {

	const PATTERN_GROUP = '^[A-Za-z0-9_\-.]{1,32}$';
	const PATTERN_KEY   = '^[A-Za-z0-9_\-.]{1,32}$';

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @see Bucket::applyBucket
	 * @see Bucket::applyArray
	 * @see Bucket::applyIni
	 */
	public function __construct($applyData = null) {
		if ($applyData instanceof Bucket) {
			$this->applyBucket($applyData);
		}
		elseif (is_array($applyData)) {
			$this->applyArray($applyData);
		}
		elseif ($applyData instanceof File) {
			$this->applyIni($applyData);
		}
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function get(string $group, string $key, $default = null) {
		self::assertIsGroupName($group);
		self::assertIsKeyName($key);

		return $this->data[$group][$key] ?? $default;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function set(string $group, string $key, $value, bool $mergeArrays = false):Bucket {
		self::assertIsGroupName($group);
		self::assertIsKeyName($key);

		if ($mergeArrays && is_array($value) && $this->isArray($group, $key)) {
			$value = array_merge($this->get($group, $key), $value);
		}

		$this->data[$group][$key] = $value;
		return $this;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function groupExists(string ...$group):bool {
		foreach ($group as $g) {
			self::assertIsGroupName($g);

			if (!isset($this->data[$g])) {
				return false;
			}
		}
		return true;
	}

	final public function getGroupCount():int {
		return count($this->data);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function getKeyCount(string ...$group):int {
		$count = 0;
		foreach ($group as $g) {
			if ($this->groupExists($g)) {
				$count += count($this->data[$g]);
			}
		}
		return $count;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function getDataType(string $group, string $key):DataTypeEnum {
		return DataTypeEnum::getInstance($this->get($group, $key));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function remove(string $group, string ...$key):Bucket {
		self::assertIsGroupName($group);

		foreach ($key as $k) {
			self::assertIsKeyName($k);

			unset($this->data[$group][$k]);
		}

		if ($this->getKeyCount($group) === 0) {
			$this->removeGroup($group);
		}
		return $this;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function removeGroup(string ...$group):Bucket {
		foreach ($group as $g) {
			self::assertIsGroupName($g);

			unset($this->data[$g]);
		}
		return $this;
	}

	/**
	 * ignores keys without group
	 *
	 * @throws InvalidDataException
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
	 * @throws NotFoundException
	 * @throws UnexpectedTypeException
	 * @throws InvalidDataException
	 */
	final public function applyIni(File $iniFile):Bucket {
		$iniFile->assertExists();
		$iniFile->assertIsFile();

		return $this->applyArray(parse_ini_file($iniFile->getRealPath(), true, INI_SCANNER_TYPED));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function applyBucket(Bucket $bucket):Bucket {
		return $this->applyArray($bucket->toArray());
	}

	final public function toArray():array {
		return $this->data;
	}

	/**
	 * @throws InvalidDataException
	 */
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
			throw (new MAPException('Failed to encode JSON.'))
					->setData('value', $this->toArray());
		}
		return $json;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isOfDataType(string $group, string $key, DataTypeEnum $dataType):bool {
		return $this->getDataType($group, $key) == $dataType;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isBoolean(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::BOOLEAN))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isBool(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isBoolean($group, $k)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isTrue(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if ($this->get($group, $k) !== true) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isFalse(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if ($this->get($group, $k) !== false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isInteger(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::INTEGER))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isInt(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isInteger($group, $k)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isFloat(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::FLOAT))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isDouble(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isFloat($group, $k)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isString(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::STRING))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isArray(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::ARRAY))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isObject(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::OBJECT))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isResource(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::RESOURCE))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function isNull(string $group, string ...$key):bool {
		foreach ($key as $k) {
			if (!$this->isOfDataType($group, $k, new DataTypeEnum(DataTypeEnum::NULL))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsOfDataType(string $group, string $key, DataTypeEnum $dataType) {
		if (!$this->isOfDataType($group, $key, $dataType)) {
			throw new InvalidDataTypeException($dataType, $this->get($group, $key));
		}
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsBoolean(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::BOOLEAN));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsBool(string $group, string $key) {
		$this->assertIsBoolean($group, $key);
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsInteger(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::INTEGER));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsInt(string $group, string $key) {
		$this->assertIsInteger($group, $key);
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsFloat(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::FLOAT));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsDouble(string $group, string $key) {
		$this->assertIsFloat($group, $key);
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsString(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::STRING));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsArray(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::ARRAY));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsObject(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::OBJECT));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsResource(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::RESOURCE));
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsNull(string $group, string $key) {
		$this->assertIsOfDataType($group, $key, new DataTypeEnum(DataTypeEnum::NULL));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function isGroupName(string $groupName):bool {
		return AbstractData::isMatching($groupName);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function isKeyName(string $keyName):bool {
		return AbstractData::isMatching($keyName);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsGroupName(string $groupName) {
		AbstractData::assertIsMatching(self::PATTERN_GROUP, $groupName);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsKeyName(string $keyName) {
		AbstractData::assertIsMatching(self::PATTERN_KEY, $keyName);
	}

}
