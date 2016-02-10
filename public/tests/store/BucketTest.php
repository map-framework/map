<?php

use store\Bucket;

final class BucketTest extends PHPUnit_Framework_TestCase {

	const GROUP = 'magic';

	const KEY_NOTHING = 'nothing';
	const KEY_STRING  = 'content';
	const KEY_INT     = 'number';
	const KEY_ARRAY   = 'list';
	const KEY_TRUE    = 'yes';
	const KEY_FALSE   = 'no';

	const VALUE_NOTHING = null;
	const VALUE_STRING  = 'O.ô';
	const VALUE_INT     = 12;
	const VALUE_ARRAY   = array(3, 2, 1, 0);
	const VALUE_TRUE    = true;
	const VALUE_FALSE   = false;

	const INVALID_GROUP = 'This is invalid';
	const INVALID_KEY   = 'This is invalid';

	/**
	 * @var Bucket
	 */
	private $bucket = null;

	/**
	 * @param string $name
	 * @param array  $data
	 * @param string $dataName
	 * @group ignore
	 */
	public function __construct($name = null, $data = array(), $dataName = '') {
		$this->bucket = (new Bucket())
				->set(self::GROUP, self::KEY_NOTHING, self::VALUE_NOTHING)
				->set(self::GROUP, self::KEY_STRING, self::VALUE_STRING)
				->set(self::GROUP, self::KEY_INT, self::VALUE_INT)
				->set(self::GROUP, self::KEY_ARRAY, self::VALUE_ARRAY)
				->set(self::GROUP, self::KEY_TRUE, self::VALUE_TRUE)
				->set(self::GROUP, self::KEY_FALSE, self::VALUE_FALSE);
		parent::__construct($name, $data, $dataName);
	}

	public function testIsNull_true() {
		$this->assertTrue($this->bucket->isNull(self::GROUP, self::KEY_NOTHING));
	}

	public function testIsNull_false() {
		$this->assertFalse($this->bucket->isNull(self::GROUP, self::KEY_STRING));
	}

	public function testIsString_true() {
		$this->assertTrue($this->bucket->isString(self::GROUP, self::KEY_STRING));
	}

	public function testIsString_false() {
		$this->assertFalse($this->bucket->isString(self::GROUP, self::KEY_INT));
	}

	public function testIsArray_true() {
		$this->assertTrue($this->bucket->isArray(self::GROUP, self::KEY_ARRAY));
	}

	public function testIsArray_false() {
		$this->assertFalse($this->bucket->isArray(self::GROUP, self::KEY_INT));
	}

	public function testIsInt_true() {
		$this->assertTrue($this->bucket->isInt(self::GROUP, self::KEY_INT));
	}

	public function testIsInt_false() {
		$this->assertFalse($this->bucket->isInt(self::GROUP, self::KEY_TRUE));
	}

	public function testIsBool_true() {
		$this->assertTrue($this->bucket->isBool(self::GROUP, self::KEY_TRUE));
	}

	public function testIsBool_false() {
		$this->assertFalse($this->bucket->isBool(self::GROUP, self::KEY_INT));
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionCode 1
	 */
	public function testSet_invalidGroup() {
		$this->bucket->set(self::INVALID_GROUP, self::KEY_INT, self::VALUE_INT);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionCode 2
	 */
	public function testSet_invalidKey() {
		$this->bucket->set(self::GROUP, self::INVALID_KEY, self::VALUE_INT);
	}

	public function testToNode_success() {
		$result = '<doc>'
				.PHP_EOL
				.'<'
				.self::GROUP
				.'>'
				.PHP_EOL
				.'<'
				.self::KEY_INT
				.'>'
				.self::VALUE_INT
				.'</'
				.self::KEY_INT
				.'>'
				.PHP_EOL
				.'</'
				.self::GROUP
				.'>'
				.PHP_EOL
				.'</doc>';
		$bucket = (new Bucket())
				->set(self::GROUP, self::KEY_INT, self::VALUE_INT);
		$this->assertEquals($result, $bucket->toNode('doc')->getSource(false));
	}

}