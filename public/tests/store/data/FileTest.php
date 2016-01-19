<?php

use store\data\File;

final class FileTest extends PHPUnit_Framework_TestCase {
	
	const FILE 			= 'file.test';
	const DIRECTORY 	= 'dir.test';
	const NOTHING 		= 'nothing.test';

	/**
	 * @group ignore
	 */
	public function __construct() {
		if (!is_file(self::FILE) && !is_dir(self::DIRECTORY) && !file_exists(self::NOTHING)) {
			touch(self::FILE);
			mkdir(self::DIRECTORY);
		}
	}

	/**
	 * @group ignore
	 */
	public function __destruct() {
		if (is_file(self::FILE) && is_dir(self::DIRECTORY)) {
			unlink(self::FILE);
			rmdir(self::DIRECTORY);
		}
	}
	
	public function testExists_true() {
		$this->assertTrue((new File(self::FILE))->exists());
	}
	
	public function testExists_false() {
		$this->assertFalse((new File(self::NOTHING))->exists());
	}
	
	public function testEsFile_true() {
		$this->assertTrue((new File(self::FILE))->isFile());
	}
	
	public function testIsFile_false() {
		$this->assertFalse((new File(self::DIRECTORY))->isFile());
		$this->assertFalse((new File(self::NOTHING))->isFile());
	}
	
}