<?php

use data\oop\Annotation;

class AnnotationTest extends PHPUnit_Framework_TestCase {

	public function testInstanceByDocLine_valid() {
		$annotation = Annotation::instanceByDocLine('	 * @foo bar miu');

		$this->assertEquals('foo', $annotation->get());
		$this->assertEquals('bar', $annotation->getParam(0));
		$this->assertEquals('miu', $annotation->getParam(1));
	}

	/**
	 * @expectedException util\MAPException
	 * @expectedExceptionMessage Invalid docLine
	 */
	public function testInstanceByDocLine_invalidDocLine() {
		Annotation::instanceByDocLine('This is CRAZY!');
	}

	public function testInstanceListByDoc_empty() {
		$this->assertEmpty(Annotation::instanceListByDoc(''));
	}

	public function testInstanceListByDoc_valid() {
		$doc = '	/**'.PHP_EOL
				.'	 * Hello World!'.PHP_EOL
				.'	 *'.PHP_EOL
				.'	 * @var $crazy string'.PHP_EOL
				.'	 * @foo'.PHP_EOL
				.'	 */'.PHP_EOL;
		$this->assertCount(2, Annotation::instanceListByDoc($doc));
	}

}
