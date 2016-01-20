<?php

use store\data\net\MAPUrl;

class MAPUrlTest extends PHPUnit_Framework_TestCase {

	const URL_FULL				= '/seven/eight/nine/ten/eleven';
	const URL_FULL_MODE		= 'seven';
	const URL_FULL_AREA		= 'eight';
	const URL_FULL_PAGE		= 'nine';
	const URL_FULL_INPUTS	= ['ten', 'eleven'];

	const URL_NONE				= '/';
	const URL_NONE_MODE		= null;
	const URL_NONE_AREA		= null;
	const URL_NONE_PAGE		= null;
	const URL_NONE_INPUTS	= array();

	# constant: URL_FULLL

	public function testGetMode_full() {
		$this->assertEquals(self::URL_FULL_MODE, (new MAPUrl(self::URL_FULL))->getMode());
	}

	public function testGetArea_full() {
		$this->assertEquals(self::URL_FULL_AREA, (new MAPUrl(self::URL_FULL))->getArea());
	}

	public function testGetPage_full() {
		$this->assertEquals(self::URL_FULL_PAGE, (new MAPUrl(self::URL_FULL))->getPage());
	}

	public function testGetInputList_full() {
		$this->assertEquals(self::URL_FULL_INPUTS, (new MAPUrl(self::URL_FULL))->getInputList());
	}

	public function testSetGet_full() {
		$this->assertEquals(self::URL_FULL, (new MAPUrl(self::URL_FULL))->get());
	}

	public function testSetAllAndGet_full() {
		$url = (new MAPUrl())
				->setMode(self::URL_FULL_MODE)
				->setArea(self::URL_FULL_AREA)
				->setPage(self::URL_FULL_PAGE)
				->setInputList(self::URL_FULL_INPUTS);
		$this->assertEquals(self::URL_FULL, $url->get());
	}

	# constant: URL_NONE

	public function testSetMode_none() {
		$this->assertEquals(self::URL_NONE_MODE, (new MAPUrl())->setMode(self::URL_NONE_MODE)->getMode());
	}

	public function testSetArea_none() {
		$this->assertEquals(self::URL_NONE_AREA, (new MAPUrl())->setArea(self::URL_NONE_AREA)->getArea());
	}

	public function testSetPage_none() {
		$this->assertEquals(self::URL_NONE_PAGE, (new MAPUrl())->setPage(self::URL_NONE_PAGE)->getPage());
	}

	public function testSetInputList_none() {
		$this->assertEquals(self::URL_NONE_INPUTS, (new MAPUrl())->setInputList(self::URL_NONE_INPUTS)->getInputList());
	}

	public function testGetMode_none() {
		$this->assertEquals(self::URL_NONE_MODE, (new MAPUrl(self::URL_NONE))->getMode());
	}

	public function testGetArea_none() {
		$this->assertEquals(self::URL_NONE_AREA, (new MAPUrl(self::URL_NONE))->getArea());
	}

	public function testGetPage_none() {
		$this->assertEquals(self::URL_NONE_PAGE, (new MAPUrl(self::URL_NONE))->getPage());
	}

	public function testGetInputList_none() {
		$this->assertEquals(self::URL_NONE_INPUTS, (new MAPUrl(self::URL_NONE))->getInputList());
	}

	public function testSetGet_none() {
		$this->assertEquals(self::URL_NONE, (new MAPUrl(self::URL_NONE))->get());
	}


}