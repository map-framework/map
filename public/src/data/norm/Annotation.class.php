<?php
namespace data\norm;

use data\AbstractData;
use data\InvalidDataException;
use data\net\ParseException;
use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 * @Aa0
 */
class Annotation extends AbstractData {

	const PATTERN_NAME           = '^[A-Za-z_][A-Za-z0-9_\-]*$';
	const PATTERN_PARAM_NAME     = '^[A-Za-z0-9]+$';
	const PATTERN_CONTENT_BOOL   = '^([Tt][Rr][Uu][Ee]|[Ff][Aa][Ll][Ss][Ee])$';
	const PATTERN_CONTENT_FLOAT  = '^-?[0-9]+\.[0-9]+$';
	const PATTERN_CONTENT_INT    = '^-?[0-9]+$';
	const PATTERN_CONTENT_NULL   = '^[Nn][Uu][Ll][Ll]$';
	const PATTERN_CONTENT_STRING = '^["\'][^"\'\s=]*["\']$';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $paramList;

	public function __construct(string $name, array $paramList = array()) {
		parent::__construct($name);

		$this->setParamList($paramList);
	}

	/**
	 * @throws InvalidDataException
	 */
	public function set(string $name) {
		$this->setName($name);
	}

	public function get():string {
		return $this->getName();
	}

	public function getName():string {
		return $this->name;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setName(string $name):Annotation {
		self::assertIsName($name);

		$this->name = $name;
		return $this;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setParamList(array $paramList):Annotation {
		foreach ($paramList as $paramName => $rawParamValue) {
			$this->setParam($paramName, $rawParamValue);
		}
		return $this;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setParam(string $paramName, string $rawParamValue):Annotation {
		self::assertIsParamName($paramName);
		self::assertIsRawParamValue($rawParamValue);

		$this->paramList[$paramName] = $rawParamValue;
		return $this;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function getParam(string $paramName, $default = null) {
		self::assertIsParamName($paramName);

		if ($this->isBool($paramName)) {
			return (bool) $this->getRawParam($paramName);
		}
		elseif ($this->isFloat($paramName)) {
			return (float) $this->getRawParam($paramName);
		}
		elseif ($this->isInt($paramName)) {
			return (int) $this->getRawParam($paramName);
		}
		elseif ($this->isNull($paramName)) {
			return null;
		}
		elseif ($this->isString($paramName)) {
			return substr($this->getRawParam($paramName), 1, -1);
		}
		return $default;
	}

	public function getRawParam(string $paramName):string {
		return $this->paramList[$paramName] ?? '';
	}

	/**
	 * @throws InvalidDataException
	 * @throws MAPException
	 */
	public static function instanceByDocLine(string $originalDocLine):Annotation {
		$docLine = trim($originalDocLine, "* \t\n\r\0\x0B");
		if (strlen($docLine) < 3 || $docLine[0] !== '@') {
			throw (new MAPException('The docLine is not valid.'))
					->setData('docLine', $originalDocLine);
		}

		$name      = null;
		$paramList = array();
		foreach (explode(' ', substr($docLine, 1)) as $item) {
			if ($item === '') {
				continue;
			}

			if (!isset($name)) {
				$name = $item;
				continue;
			}

			$paramItemList = explode('=', $item);
			if (count($paramItemList) !== 2) {
				throw (new MAPException('The docLine is not valid (param invalid).'))
						->setData('docLine', $originalDocLine)
						->setData('paramItemList', $paramItemList);
			}
			$paramList[$paramItemList[0]] = $paramItemList[1];
		}
		return new Annotation($name, $paramList);
	}

	final public function hasParam(string $paramName):bool {
		return isset($this->paramList[$paramName]);
	}

	final public function isBool(string $paramName):bool {
		return $this->hasParam($paramName) && self::isMatching(self::PATTERN_CONTENT_BOOL, $this->getRawParam($paramName));
	}

	final public function isFloat(string $paramName):bool {
		return $this->hasParam($paramName) && self::isMatching(self::PATTERN_CONTENT_FLOAT, $this->getRawParam($paramName));
	}

	final public function isInt(string $paramName):bool {
		return $this->hasParam($paramName) && self::isMatching(self::PATTERN_CONTENT_INT, $this->getRawParam($paramName));
	}

	final public function isNull(string $paramName):bool {
		return $this->hasParam($paramName) && self::isMatching(self::PATTERN_CONTENT_NULL, $this->getRawParam($paramName));
	}

	final public function isString(string $paramName):bool {
		return $this->hasParam($paramName)
		&& self::isMatching(
				self::PATTERN_CONTENT_STRING,
				$this->getRawParam($paramName)
		);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function assertIsBool(string $paramName) {
		self::assertIsMatching(self::PATTERN_CONTENT_BOOL, $this->getRawParam($paramName));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function assertIsFloat(string $paramName) {
		self::assertIsMatching(self::PATTERN_CONTENT_FLOAT, $this->getRawParam($paramName));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function assertIsInt(string $paramName) {
		self::assertIsMatching(self::PATTERN_CONTENT_INT, $this->getRawParam($paramName));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function assertIsNull(string $paramName) {
		self::assertIsMatching(self::PATTERN_CONTENT_NULL, $this->getRawParam($paramName));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function assertIsString(string $paramName) {
		self::assertIsMatching(self::PATTERN_CONTENT_STRING, $this->getRawParam($paramName));
	}

	final public static function isName(string $name):bool {
		return self::isMatching(self::PATTERN_NAME, $name);
	}

	final public static function isParamName(string $paramName):bool {
		return self::isMatching(self::PATTERN_PARAM_NAME, $paramName);
	}

	final public static function isRawParamValue(string $rawParamValue):bool {
		return self::isMatching(self::PATTERN_CONTENT_BOOL, $rawParamValue)
		|| self::isMatching(self::PATTERN_CONTENT_FLOAT, $rawParamValue)
		|| self::isMatching(self::PATTERN_CONTENT_INT, $rawParamValue)
		|| self::isMatching(self::PATTERN_CONTENT_NULL, $rawParamValue)
		|| self::isMatching(self::PATTERN_CONTENT_STRING, $rawParamValue);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsName(string $name) {
		self::assertIsMatching(self::PATTERN_NAME, $name);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsParamName(string $paramName) {
		self::assertIsMatching(self::PATTERN_PARAM_NAME, $paramName);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsRawParamValue(string $rawParamValue) {
		if (!self::isRawParamValue($rawParamValue)) {
			throw (new InvalidDataException(self::PATTERN_CONTENT_BOOL, $rawParamValue))
					->addPattern(self::PATTERN_CONTENT_FLOAT)
					->addPattern(self::PATTERN_CONTENT_INT)
					->addPattern(self::PATTERN_CONTENT_NULL)
					->addPattern(self::PATTERN_CONTENT_STRING);
		}
	}

}
