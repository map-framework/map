<?php

use store\data\File;

final class FileTest extends PHPUnit_Framework_TestCase {

	const FILE    = 'public/web.php';
	const DIR     = 'public/';
	const NOTHING = 'notExists.file';

	public function testExists_file_true() {
		$this->assertTrue((new File(self::FILE))->exists());
	}

	public function testIsFile_file_true() {
		$this->assertTrue((new File(self::FILE))->isFile());
	}

	public function testIsDir_dir_true() {
		$this->assertTrue((new File(self::DIR))->isDir());
	}

	public function testIsLink_file_false() {
		$this->assertFalse((new File(self::FILE))->isLink());
	}

	public function testIsReadable_file_true() {
		$this->assertTrue((new File(self::FILE))->isReadable());
	}

	public function testIsWritable_file_true() {
		$this->assertTrue((new File(self::FILE))->isWritable());
	}

	public function testIsExecutable_file_true() {
		$this->assertTrue((new File(self::FILE))->isExecutable());
	}

	public function testAttach_withoutGlue() {
		$this->assertEquals('dir/file', (new File('dir'))->attach('file')->get());
	}

	public function testAttach_dirGlue() {
		$this->assertEquals('dir/file', (new File('dir/'))->attach('file')->get());
	}

	public function testAttach_fileGlue() {
		$this->assertEquals('dir/file', (new File('dir'))->attach('/file')->get());
	}

	public function testAttach_bothGlue() {
		$this->assertEquals('dir/file', (new File('dir/'))->attach('/file')->get());
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionCode 1
	 */
	public function testChangeMode_wrongUser_failed() {
		(new File(self::FILE))->changeMode(8, 5, 5);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionCode 2
	 */
	public function testChangeMode_wrongGroup_failed() {
		(new File(self::FILE))->changeMode(5, 17, 5);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionCode 3
	 */
	public function testChangeMode_wrongOther_failed() {
		(new File(self::FILE))->changeMode(5, 5, -1);
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 1
	 */
	public function testChangeMode_nothing_failed() {
		(new File(self::NOTHING))->changeMode(5, 5, 0);
	}

}