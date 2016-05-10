<?php
namespace data\norm;

use data\AbstractEnum;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class DataTypeEnum extends AbstractEnum {

	const BOOLEAN  = 'BOOLEAN';
	const BOOL     = 'BOOLEAN';
	const INTEGER  = 'INTEGER';
	const INT      = 'INTEGER';
	const FLOAT    = 'FLOAT';
	const DOUBLE   = 'FLOAT';
	const STRING   = 'STRING';
	const ARRAY    = 'ARRAY';
	const OBJECT   = 'OBJECT';
	const RESOURCE = 'RESOURCE';
	const NULL     = 'NULL';

	final public static function getInstance($data):DataTypeEnum {
		switch (getType($data)) {
			case 'boolean':
				return new DataTypeEnum(self::BOOLEAN);
			case 'integer':
				return new DataTypeEnum(self::INTEGER);
			case 'double':
				return new DataTypeEnum(self::FLOAT);
			case 'string':
				return new DataTypeEnum(self::STRING);
			case 'array':
				return new DataTypeEnum(self::ARRAY);
			case 'object':
				return new DataTypeEnum(self::OBJECT);
			case 'resource':
				return new DataTypeEnum(self::RESOURCE);
			default:
				return new DataTypeEnum(self::NULL);
		}
	}

	final public function isOfType(...$data):bool {
		foreach ($data as $d) {
			if ($this != self::getInstance($d)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataTypeException
	 */
	final public function assertIsOfType(...$data) {
		foreach ($data as $d) {
			if (!$this->isOfType($d)) {
				throw new InvalidDataTypeException($this, $data);
			}
		}
	}

}
