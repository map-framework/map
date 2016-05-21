<?php
namespace data\oop;

use data\AbstractData;
use data\InvalidDataException;
use ReflectionProperty;
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
	 * @return Annotation[]
	 */
	public function getAnnotationList():array {
		$doc = $this->getReflection()->getDocComment();
		return $doc !== false ? Annotation::instanceListByDoc($doc) : array();
	}

	/**
	 * @throws AnnotationNotFoundException
	 * @throws PropertyNotFoundException
	 */
	public function getAnnotation(string $name):Annotation {
		foreach ($this->getAnnotationList() as $annotation) {
			if ($annotation->getName() === $name) {
				return $annotation;
			}
		}
		throw new AnnotationNotFoundException(new Annotation($name));
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function setValue($object, $value):PropertyObject {
		$this->assertExists();

		$this->getReflection()->setValue($object, $value);
		return $this;
	}

	/**
	 * @throws PropertyNotFoundException
	 */
	final public function getValue($object) {
		$this->assertExists();

		return $this->getReflection()->getValue($object);
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
		catch (AnnotationNotFoundException $e) {
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
	 * @throws AnnotationNotFoundException
	 */
	final public function assertHasAnnotation(string $name) {
		$this->getAnnotation($name);
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
