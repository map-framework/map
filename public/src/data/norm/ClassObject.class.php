<?php
namespace data\norm;

use data\AbstractData;
use data\InvalidDataException;
use exception\MAPException;
use ReflectionClass;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class ClassObject extends AbstractData {

	const PATTERN_NAME_SPACE = '^([a-zA-Z0-9_]+\\\)*[a-zA-Z0-9_]+';

	/**
	 * @var string
	 */
	private $nameSpace;

	public function set(string $nameSpace) {
		self::assertIsNameSpace($nameSpace);

		$this->nameSpace = $nameSpace;
	}

	public function get():string {
		return $this->nameSpace;
	}

	final public function exists():bool {
		return class_exists($this->nameSpace);
	}

	/**
	 * @throws ClassNotFoundException
	 */
	final protected function getReflection():ReflectionClass {
		$this->assertExists();

		return new ReflectionClass($this->get());
	}

	/**
	 * @throws ClassNotFoundException
	 */
	final public function isInterface():bool {
		return $this->getReflection()->isInterface();
	}

	/**
	 * @throws ClassNotFoundException
	 */
	final public function isAbstract():bool {
		return $this->getReflection()->isAbstract();
	}

	/**
	 * @throws ClassNotFoundException
	 */
	final public function isFinal():bool {
		return $this->getReflection()->isFinal();
	}

	/**
	 * @throws ClassNotFoundException
	 */
	final public function isChildOf(ClassObject $classObject):bool {
		$classObject->assertExists();

		$reflection = $this->getReflection();

		do {
			if ($reflection->getName() === $classObject->get()) {
				return true;
			}

			$reflection = $reflection->getParentClass();
		}
		while ($reflection !== false);
		return false;
	}

	/**
	 * @throws ClassNotFoundException
	 */
	final public function assertExists() {
		if (!$this->exists()) {
			throw new ClassNotFoundException($this);
		}
	}

	/**
	 * @throws MAPException
	 */
	final public function assertIsInterface() {
		if (!$this->isInterface()) {
			throw (new MAPException('Expected an Interface.'))
					->setData('class', $this);
		}
	}

	/**
	 * @throws MAPException
	 */
	final public function assertIsAbstract() {
		if (!$this->isAbstract()) {
			throw (new MAPException('Expected an Abstract-Class.'))
					->setData('class', $this);
		}
	}

	/**
	 * @throws MAPException
	 */
	final public function assertIsFinal() {
		if (!$this->isFinal()) {
			throw (new MAPException('Expected a Final-Class.'))
					->setData('class', $this);
		}
	}

	final public static function isNameSpace(string $nameSpace) {
		return self::isMatching(self::PATTERN_NAME_SPACE, $nameSpace);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsNameSpace(string $nameSpace) {
		self::assertIsMatching(self::PATTERN_NAME_SPACE, $nameSpace);
	}

}
