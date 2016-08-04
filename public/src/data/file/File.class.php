<?php
namespace data\file;

use data\AbstractData;
use data\map\AddOn;
use util\MAPException;
use RuntimeException;
use util\data\math\number\OctalNumber;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class File extends AbstractData {

	const ROOT_DIR = '../';

	private $filePath;

	public function set(string $file) {
		if (strlen($file) && $file[strlen($file) - 1] === '/') {
			$file = substr($file, 0, -1);
		}
		$this->filePath = $file;
	}

	public function get():string {
		return $this->filePath;
	}

	/**
	 * get real file system path
	 */
	public function getRealPath():string {
		if ($this->isRelative()) {
			return self::ROOT_DIR.$this->get();
		}
		return $this->get();
	}

	public function getType():TypeEnum {
		$this->assertExists();

		if ($this->isFile()) {
			return new TypeEnum(TypeEnum::FILE);
		}
		elseif ($this->isDir()) {
			return new TypeEnum(TypeEnum::DIR);
		}
		elseif ($this->isLink()) {
			return new TypeEnum(TypeEnum::LINK);
		}
		else {
			throw new RuntimeException('Unknown Type.');
		}
	}

	/**
	 * @throws NotFoundException
	 */
	final public function getSize():int {
		$this->assertExists();
		$this->assertIsFile();

		return filesize($this->getRealPath());
	}

	final public function getShortName():string {
		$pathItemList = explode('/', $this->get());
		return end($pathItemList) ?: '';
	}

	final public function attach(string $path):File {
		if ($path[0] === '/') {
			$path = substr($path, 1);
		}
		$this->set((strlen($this->get()) ? $this->get().'/' : '').$path);
		return $this;
	}

	/**
	 * @throws NotFoundException
	 * @throws MAPException
	 */
	final public function changeMode(OctalNumber $user, OctalNumber $group, OctalNumber $other):File {
		$this->assertExists();
		(new AddOn('math'))->assertIsInstalled();

		if (!chmod($this->getRealPath(), $user.$group.$other)) {
			throw (new MAPException('Failed to change mode.'))
					->setData('file', $this)
					->setData('user', $user)
					->setData('group', $group)
					->setData('other', $other);
		}
		return $this;
	}

	/**
	 * create dir if not exists
	 *
	 * @throws UnexpectedTypeException
	 * @throws MAPException
	 */
	final public function makeDir():File {
		if ($this->exists()) {
			$this->assertIsDir();
		}
		elseif (!mkdir($this->getRealPath(), 0777, true)) {
			throw (new MAPException('Failed to make dir.'))
					->setData('file', $this);
		}
		return $this;
	}

	/**
	 * create file if not exists
	 *
	 * @throws MAPException
	 */
	final public function putContents(string $content, bool $append):File {
		if ($this->exists()) {
			$this->assertIsFile();
			$this->assertIsReadable();
		}

		if (file_put_contents($this->getRealPath(), $content, $append ? FILE_APPEND : 0) === false) {
			throw (new MAPException('Failed to put content into file.'))
					->setData('content', $content)
					->setData('append', $append);
		}
		return $this;
	}

	/**
	 * create empty file
	 *
	 * @throws ExistsException
	 * @throws MAPException
	 */
	final public function makeFile():File {
		$this->assertNotExists();

		if (file_put_contents($this->getRealPath(), '') === false) {
			throw (new MAPException('Failed to make file.'))
					->setData('file', $this);
		}
		return $this;
	}

	/**
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 * @throws MAPException
	 */
	final public function getContents():string {
		$this->assertExists();
		$this->assertIsReadable();

		$content = file_get_contents($this->getRealPath());
		if ($content === false) {
			throw (new MAPException('Failed to get content if file.'))
					->setData('file', $this);
		}
		return $content;
	}

	/**
	 * @throws NotFoundException
	 * @throws UnexpectedTypeException
	 * @throws MAPException
	 * @return File[]
	 */
	final public function scanDir(TypeEnum $filter = null):array {
		$this->assertExists();
		$this->assertIsDir();

		$fileNameList = scandir($this->getRealPath());
		if ($fileNameList === false) {
			throw (new MAPException('Failed to scan dir.'))
					->setData('file', $this);
		}

		$fileList = array();
		foreach ($fileNameList as $key => $fileName) {
			if ($fileName === '.' || $fileName === '..') {
				unset($fileNameList[$key]);
				continue;
			}

			$file = (new File($this->get()))
					->attach($fileName);
			if ($filter === null || $filter == $file->getType()) {
				$fileList[] = $file;
			}
		}
		return $fileList;
	}

	/**
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 * @throws MAPException
	 */
	final public function output():File {
		$this->assertExists();
		$this->assertIsReadable();

		if (readfile($this->getRealPath()) === false) {
			throw (new MAPException('Failed to output file.'))
					->setData('file', $this);
		}
		return $this;
	}

	final public function isAbsolute():bool {
		return strlen($this->get()) && $this->get()[0] === '/';
	}

	final public function isRelative():bool {
		return !$this->isAbsolute();
	}

	final public function exists():bool {
		return file_exists($this->getRealPath());
	}

	final public function isFile():bool {
		return is_file($this->getRealPath());
	}

	final public function isDir():bool {
		return is_dir($this->getRealPath());
	}

	final public function isLink():bool {
		return is_link($this->getRealPath());
	}

	final public function isReadable():bool {
		return is_readable($this->getRealPath());
	}

	final public function isWritable():bool {
		return is_writeable($this->getRealPath());
	}

	final public function isExecutable():bool {
		return is_executable($this->getRealPath());
	}

	/**
	 * @throws NotFoundException
	 */
	final public function assertExists() {
		if (!$this->exists()) {
			throw new NotFoundException($this);
		}
	}

	/**
	 * @throws ExistsException
	 */
	final public function assertNotExists() {
		if ($this->exists()) {
			throw new ExistsException($this);
		}
	}

	/**
	 * @throws ForbiddenException
	 */
	final public function assertIsReadable() {
		if (!$this->isReadable()) {
			throw new ForbiddenException($this, true, false, false);
		}
	}

	/**
	 * @throws ForbiddenException
	 */
	final public function assertIsWritable() {
		if (!$this->isWritable()) {
			throw new ForbiddenException($this, false, true, false);
		}
	}

	/**
	 * @throws ForbiddenException
	 */
	final public function assertIsExecutable() {
		if (!$this->isExecutable()) {
			throw new ForbiddenException($this, false, false, true);
		}
	}

	/**
	 * @throws UnexpectedTypeException
	 */
	final public function assertIsFile() {
		if (!$this->isFile()) {
			throw new UnexpectedTypeException($this, new TypeEnum(TypeEnum::FILE));
		}
	}

	/**
	 * @throws UnexpectedTypeException
	 */
	final public function assertIsDir() {
		if (!$this->isDir()) {
			throw new UnexpectedTypeException($this, new TypeEnum(TypeEnum::DIR));
		}
	}

	/**
	 * @throws UnexpectedTypeException
	 */
	final public function assertIsLink() {
		if (!$this->isLink()) {
			throw new UnexpectedTypeException($this, new TypeEnum(TypeEnum::LINK));
		}
	}

}
