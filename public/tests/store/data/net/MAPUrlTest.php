<?php

use store\data\net\MAPUrl;
use store\Bucket;
use store\data\File;

class MAPUrlTest extends PHPUnit_Framework_TestCase {

	const URL_VALID        = '/seven/eight/nine/ten/eleven';
	const URL_VALID_MODE   = 'seven';
	const URL_VALID_AREA   = 'eight';
	const URL_VALID_PAGE   = 'nine';
	const URL_VALID_INPUTS = ['ten', 'eleven'];

	const URL_INVALID        = '/4848ß/`hello/world´/föö/bär';
	const URL_INVALID_MODE   = '4848ß';
	const URL_INVALID_AREA   = '`hello´';
	const URL_INVALID_PAGE   = 'world´';
	const URL_INVALID_INPUTS = array('more€s', 'invalid$');

	const URL_DEFAULT_MODE   = 'site';
	const URL_DEFAULT_AREA   = 'base';
	const URL_DEFAULT_PAGE   = 'home';
	const URL_DEFAULT_INPUTS = [];

	public function testSetAndGetPath_valid() {
		$this->assertEquals(self::URL_VALID, (string) new MAPUrl(self::URL_VALID));
	}

	public function testSetAndGetPath_real() {
		$this->assertEquals(self::URL_VALID, (string) new MAPUrl(self::URL_VALID, $this->getConfig()));
	}

	public function testGetMode_valid() {
		$this->assertEquals(self::URL_VALID_MODE, (new MAPUrl(self::URL_VALID))->getMode());
	}

	public function testGetMode_default() {
		$this->assertEquals(self::URL_DEFAULT_MODE, (new MAPUrl(null, $this->getConfig()))->getMode());
	}

	public function testGetArea_valid() {
		$this->assertEquals(self::URL_VALID_AREA, (new MAPUrl(self::URL_VALID))->getArea());
	}

	public function testGetArea_default() {
		$this->assertEquals(self::URL_DEFAULT_AREA, (new MAPUrl(null, $this->getConfig()))->getArea());
	}

	public function testGetPage_valid() {
		$this->assertEquals(self::URL_VALID_PAGE, (new MAPUrl(self::URL_VALID))->getPage());
	}

	public function testGetPage_default() {
		$this->assertEquals(self::URL_DEFAULT_PAGE, (new MAPUrl(null, $this->getConfig()))->getPage());
	}

	public function testGetInputList_valid() {
		$this->assertEquals(self::URL_VALID_INPUTS, (new MAPUrl(self::URL_VALID))->getInputList());
	}

	public function testSetInputList_valid() {
		$this->assertTrue((new MAPUrl())->setInputList(self::URL_INVALID_INPUTS));
	}

	public function testSetMode_invalid() {
		$this->assertFalse((new MAPUrl())->setMode(self::URL_INVALID_MODE));
	}

	public function testSetArea_invalid() {
		$this->assertFalse((new MAPUrl())->setMode(self::URL_INVALID_AREA));
	}

	public function testSetPage_invalid() {
		$this->assertFalse((new MAPUrl())->setMode(self::URL_INVALID_PAGE));
	}

	public function testSetInputList_invalid() {
		$this->assertFalse((new MAPUrl())->setMode(self::URL_INVALID_INPUTS));
	}

	/**
	 * @return Bucket
	 */
	private function getConfig() {
		return (new Bucket())->applyIni(new File('public/web.ini'));
	}

}