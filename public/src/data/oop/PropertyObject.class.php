<?php
namespace data\oop;

use data\AbstractData;
use data\InvalidDataException;
use ReflectionProperty;
use TypeError;
use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class PropertyObject extends AbstractData {

	const PATTERN_NAME = '^[A-Za-z_][A-Za-z_0-9]*$';

	/**
	 * @var ClassObject
	 */
	private $classObject;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @return InvalidDataException
	 * @throws ClassNotFoundException
	 */
	public function __construct(ClassObject $classObject, string $name) {
		parent::__construct($name);

		$this->setClassObject($classObject);
	}

	/**
	 * @return InvalidDataException
	 */
	public function set(string $name) {
		$this->setName($name);
	}

	public function get():string {
		return $this->getClassObject()->get().'::'.$this->getName();
	}

	/**
	 * @throws ClassNotFoundException
	 */
	public function setClassObject(ClassObject $classObject):PropertyObject {
		$classObject->assertExists();

		$this->classObject = $classObject;
		return $this;
	}

	public function getClassObject():ClassObject {
		return $this->classObject;
	}

	/**
	 * @return InvalidDataException
	 */
	public function setName(string $name):PropertyObject {
		self::assertIsName($name);

		$this->name = $name;
		return $this;
	}

	public function getName():string {
		return $this->name;
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	public function getReflection():ReflectionProperty {
		$this->assertExists();

		return new ReflectionProperty($this->getClassObject()->get(), $this->getName());
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	public function getAnnotationList():array {
		$doc = $this->getReflection()->getDocComment();
		return $doc !== false ? Annotation::instanceListByDoc($doc) : array();
	}

	/**
	 * @throws TypeError
	 * @throws PropertyNotFoundException
	 */
	public function getAnnotation(string $name):Annotation {
		foreach ($this->getAnnotationList() as $annotation) {
			/** @noinspection PhpUndefinedMethodInspection */
			if ($annotation->getName() === $name) {
				return $annotation;
			}
		}
	}

	final public function exists():bool {
		return property_exists($this->getClassObject()->get(), $this->getName());
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function isPublic():bool {
		$this->assertExists();

		return $this->getReflection()->isPublic();
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function isProtected():bool {
		$this->assertExists();

		return $this->getReflection()->isProtected();
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function isPrivate():bool {
		$this->assertExists();

		return $this->getReflection()->isPrivate();
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function isStatic():bool {
		$this->assertExists();

		return $this->getReflection()->isStatic();
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function hasAnnotation(string $name):bool {
		try {
			$this->getAnnotation($name);
		}
		catch (TypeError $e) {
			return false;
		}
		return true;
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function assertExists() {
		if (!$this->exists()) {
			throw new PropertyNotFoundException($this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsPublic() {
		if (!$this->isPublic()) {
			throw (new MAPException('Expected public property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotPublic() {
		if ($this->isPublic()) {
			throw (new MAPException('Expected non-public property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsProtected() {
		if (!$this->isProtected()) {
			throw (new MAPException('Expected protected property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotProtected() {
		if ($this->isProtected()) {
			throw (new MAPException('Expected non-protected property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsPrivate() {
		if (!$this->isPrivate()) {
			throw (new MAPException('Expected private property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotPrivate() {
		if ($this->isPrivate()) {
			throw (new MAPException('Expected non-private property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsStatic() {
		if (!$this->isStatic()) {
			throw (new MAPException('Expected static property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotStatic() {
		if ($this->isStatic()) {
			throw (new MAPException('Expected non-static property'))
					->setData('propertyObject', $this);
		}
	}

	/**
	 * @throws PropertyNotFoundException
	 * @throws MAPException
	 */
	final public function assertHasAnnotation(string $name) {
		if (!$this->hasAnnotation($name)) {
			throw (new MAPException('Expected annotated property'))
					->setData('propertyObject', $this)
					->setData('annotationName', $name);
		}
	}

	final public static function isName(string ...$name):bool {
		foreach ($name as $n) {
			if (!self::isMatching(self::PATTERN_NAME, $n)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsName(string ...$name) {
		foreach ($name as $n) {
			self::assertIsMatching(self::PATTERN_NAME, $n);
		}
	}

}
