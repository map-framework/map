<?php

use store\data\File;

final class FileTest extends PHPUnit_Framework_TestCase {
	
	const FILE			= 'web.ini';
	const DIRECTORY	= 'src';
	const NOTHING		= 'notExists.ini';
	
	public function testExists_exists() {
		$this->assertTrue((new File(self::FILE))->exists());
	}
	
	public function testExists_notExists() {
		$this->assertFalse((new File(self::NOTHING))->exists());
	}
	
	public function testIsFile_file() {
		$this->assertTrue((new File(self::FILE))->isFile());
	}
	
	public function testIsFile_directory() {
		$this->assertFalse((new File(self::DIRECTORY))->isFile());
	}
	
	public function testIsFile_nothing() {
		$this->assertFalse((new File(self::NOTHING))->isFile());
	}
	
}