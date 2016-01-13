<?php

use store\data\File;

final class FileTest extends PHPUnit_Framework_TestCase {
	
	const FILE_EXISTS			= 'web.ini';
	const FILE_NOT_EXISTS	= 'notExists.ini';
	
	public function testExists_true() {
		$this->assertTrue((new File(self::FILE_EXISTS))->exists());
	}
	
	public function testExists_false() {
		$this->assertFalse((new File(self::FILE_NOT_EXISTS))->exists());
	}
	
}