<?php
namespace data\norm;

use data\AbstractData;
use data\InvalidDataException;
use ReflectionMethod;
use TypeError;
use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class MethodObject extends AbstractData {

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
	public function setClassObject(ClassObject $classObject):MethodObject {
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
	public function setName(string $name):MethodObject {
		self::assertIsName($name);

		$this->name = $name;
		return $this;
	}

	public function getName():string {
		return $this->name;
	}

	/**
	 * @throws MethodNotFoundException
	 */
	public function getReflection():ReflectionMethod {
		self::assertExists();

		return new ReflectionMethod($this->getClassObject()->get(), $this->getName());
	}

	/**
	 * @throws MethodNotFoundException
	 */
	public function getAnnotationList():array {
		$doc = $this->getReflection()->getDocComment();
		return $doc !== false ? Annotation::instanceListByDoc($doc) : array();
	}

	/**
	 * @throws TypeError
	 * @throws MethodNotFoundException
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
		return method_exists($this->getClassObject()->get(), $this->getName());
	}

	/**
	 * @throws MethodNotFoundException
	 */
	final public function isPublic():bool {
		$this->assertExists();

		return $this->getReflection()->isPublic();
	}

	/**
	 * @throws MethodNotFoundException
	 */
	final public function isProtected():bool {
		$this->assertExists();

		return $this->getReflection()->isProtected();
	}

	/**
	 * @throws MethodNotFoundException
	 */
	final public function isPrivate():bool {
		$this->assertExists();

		return $this->getReflection()->isPrivate();
	}

	/**
	 * @throws MethodNotFoundException
	 */
	final public function isStatic():bool {
		$this->assertExists();

		return $this->getReflection()->isStatic();
	}

	/**
	 * @throws MethodNotFoundException
	 */
	final public function isFinal():bool {
		$this->assertExists();

		return $this->getReflection()->isFinal();
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function isAbstract():bool {
		$this->assertExists();
		$this->classObject->assertIsAbstract();

		return $this->getReflection()->isAbstract();
	}

	/**
	 * @throws MethodNotFoundException
	 */
	final public function isConstructor():bool {
		$this->assertExists();

		return $this->getReflection()->isConstructor();
	}

	/**
	 * @throws MethodNotFoundException
	 */
	final public function isDestructor():bool {
		$this->assertExists();

		return $this->getReflection()->isDestructor();
	}

	/**
	 * @throws MethodNotFoundException
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
	 * @throws MethodNotFoundException
	 */
	final public function assertExists() {
		if (!$this->exists()) {
			throw new MethodNotFoundException($this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsPublic() {
		if (!$this->isPublic()) {
			throw (new MAPException('Expected public method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotPublic() {
		if ($this->isPublic()) {
			throw (new MAPException('Expected non-public method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsProtected() {
		if (!$this->isProtected()) {
			throw (new MAPException('Expected protected method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotProtected() {
		if ($this->isProtected()) {
			throw (new MAPException('Expected non-protected method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsPrivate() {
		if (!$this->isPrivate()) {
			throw (new MAPException('Expected private method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotPrivate() {
		if ($this->isPrivate()) {
			throw (new MAPException('Expected non-private method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsStatic() {
		if (!$this->isStatic()) {
			throw (new MAPException('Expected static method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotStatic() {
		if ($this->isStatic()) {
			throw (new MAPException('Expected non-static method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsFinal() {
		if (!$this->isFinal()) {
			throw (new MAPException('Expected final method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotFinal() {
		if ($this->isFinal()) {
			throw (new MAPException('Expected non-final method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsAbstract() {
		if (!$this->isAbstract()) {
			throw (new MAPException('Expected abstract method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotAbstract() {
		if ($this->isAbstract()) {
			throw (new MAPException('Expected non-abstract method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsConstructor() {
		if (!$this->isConstructor()) {
			throw (new MAPException('Expected construct method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotConstructor() {
		if ($this->isConstructor()) {
			throw (new MAPException('Expected non-construct method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsDestructor() {
		if (!$this->isDestructor()) {
			throw (new MAPException('Expected destruct method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertIsNotDestructor() {
		if ($this->isDestructor()) {
			throw (new MAPException('Expected non-destruct method'))
					->setData('methodObject', $this);
		}
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws MAPException
	 */
	final public function assertHasAnnotation(string $name) {
		if (!$this->hasAnnotation($name)) {
			throw (new MAPException('Expected annotated method'))
					->setData('methodObject', $this)
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
			self::assertIsName($n);
		}
	}

}
