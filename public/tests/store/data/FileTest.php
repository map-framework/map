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