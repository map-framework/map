<?php
namespace data\oop;

use data\AbstractData;
use data\InvalidDataException;
use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Annotation extends AbstractData {

	const PATTERN_NAME = '^[A-Za-z_][A-Za-z0-9_\-]*$';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var mixed[]
	 */
	private $paramList = array();

	/**
	 * @throws InvalidDataException
	 */
	public function __construct(string $name, string ...$param) {
		parent::__construct($name);

		$this->addParam(...$param);
	}

	/**
	 * @throws InvalidDataException
	 */
	public function set(string $name) {
		self::assertIsValidName($name);

		$this->name = $name;
	}

	public function get():string {
		return $this->name;
	}

	public function addParamList(array $paramList):Annotation {
		foreach ($paramList as $param) {
			$this->addParam($param);
		}
		return $this;
	}

	public function addParam(string ...$param):Annotation {
		foreach ($param as $p) {
			$p = trim($p);
			if (strlen($p)) {
				$this->paramList[] = $p;
			}
		}
		return $this;
	}

	public function getParam(int $index, string $default = null) {
		return $this->paramList[$index] ?? $default;
	}

	public function paramCount():int {
		return count($this->paramList);
	}

	/**
	 * @return Annotation[]
	 */
	public static function instanceListByDoc(string $doc):array {
		foreach (explode(PHP_EOL, $doc) as $docLine) {
			try {
				$annotationList[] = Annotation::instanceByDocLine($docLine);
			}
			catch (MAPException $e) {
			}
		}
		return $annotationList ?? array();
	}

	/**
	 * @throws InvalidDataException
	 * @throws MAPException
	 */
	public static function instanceByDocLine(string $docLine):Annotation {
		$docLineBody = trim($docLine, "* \t\n\r\0\x0B");

		if (strlen($docLineBody) < 3 || $docLineBody[0] !== '@') {
			throw (new MAPException('Invalid docLine'))
					->setData('docLine', $docLine);
		}

		$itemList   = explode(' ', substr($docLineBody, 1));
		$annotation = new Annotation($itemList[0]);
		unset($itemList[0]);

		return $annotation->addParamList($itemList);
	}

	final public static function isValidName(string ...$name):bool {
		return self::isMatching(self::PATTERN_NAME, ...$name);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsValidName(string ...$name) {
		self::assertIsMatching(self::PATTERN_NAME, ...$name);
	}

}
