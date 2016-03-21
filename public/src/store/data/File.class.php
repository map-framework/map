<?php
namespace store\data;

use Exception;
use RuntimeException;
use store\data\math\number\OctalNumber;

class File extends AbstractData {

	const ROOT_DIR = '../';

	const TYPE_FILE = 'file';
	const TYPE_DIR  = 'dir';
	const TYPE_LINK = 'link';

	const MAKE_DIR_MODE  = 0777;
	const MAKE_FILE_MODE = 0777;
	const MAKE_LINK_MODE = 0777;

	/**
	 * set file path
	 *
	 * @param string $file
	 * @return File
	 */
	public function set($file) {
		if ($file[strlen($file) - 1] === '/') {
			$file = substr($file, 0, -1);
		}
		return parent::set($file);
	}

	/**
	 * get real file system path
	 *
	 * @return string
	 */
	public function getRealPath() {
		if ($this->isRelative()) {
			return self::ROOT_DIR.$this->get();
		}
		return $this->get();
	}

	/**
	 * is absolute file path
	 *
	 * @return bool
	 */
	final public function isAbsolute() {
		return $this->get()[0] === '/';
	}

	/**
	 * is relative file path
	 *
	 * @return bool
	 */
	final public function isRelative() {
		return $this->get()[0] !== '/';
	}

	/**
	 * @link   http://php.net/file_exists
	 * @return bool
	 */
	final public function exists() {
		return file_exists($this->getRealPath());
	}

	/**
	 * @link   http://php.net/is_file
	 * @return bool
	 */
	final public function isFile() {
		return is_file($this->getRealPath());
	}

	/**
	 * @link   http://php.net/is_dir
	 * @return bool
	 */
	final public function isDir() {
		return is_dir($this->getRealPath());
	}

	/**
	 * @link   http://php.net/is_link
	 * @return bool
	 */
	final public function isLink() {
		return is_link($this->getRealPath());
	}

	/**
	 * @link   http://php.net/is_readable
	 * @return bool
	 */
	final public function isReadable() {
		return is_readable($this->getRealPath());
	}

	/**
	 * @link   http://php.net/is_writeable
	 * @return bool
	 */
	final public function isWritable() {
		return is_writeable($this->getRealPath());
	}

	/**
	 * @link   http://php.net/is_executable
	 * @return bool
	 */
	final public function isExecutable() {
		return is_executable($this->getRealPath());
	}

	/**
	 * @link   http://php.net/filesize
	 * @return int
	 */
	final public function getSize() {
		return filesize($this->getRealPath());
	}

	/**
	 * @param  string $path
	 * @return File
	 */
	final public function attach($path) {
		if ($path[0] === '/') {
			$path = substr($path, 1);
		}
		return $this->set(parent::get().'/'.$path);
	}

	/**
	 * @param  OctalNumber $user
	 * @param  OctalNumber $group
	 * @param  OctalNumber $other
	 * @throws Exception if changing mode failed
	 * @return File
	 */
	final public function changeMode(OctalNumber $user, OctalNumber $group, OctalNumber $other) {
		if ($user === null) {
			$user = new OctalNumber(7);
		}
		if ($group === null) {
			$group = new OctalNumber(7);
		}
		if ($other === null) {
			$other = new OctalNumber(7);
		}

		if (!$this->exists()) {
			throw new RuntimeException('File `'.$this.'` not exists.');
		}

		if (!chmod($this->getRealPath(), $user.$group.$other)) {
			throw new Exception('Failed to change mode to '.$user.$group.$other.' of file `'.$this.'`.');
		}
		return $this;
	}

	/**
	 * create dir if not exists
	 *
	 * @throws Exception
	 * @return File
	 */
	final public function makeDir() {
		if (!$this->isDir()) {
			if (!mkdir($this->getRealPath(), self::MAKE_DIR_MODE, true)) {
				throw new Exception('Failed to make dir `'.$this.'`', 1);
			}
		}
		return $this;
	}

	/**
	 * @throws Exception
	 * @return File
	 */
	final public function makeFile() {
		if (file_put_contents($this->getRealPath(), '', self::MAKE_FILE_MODE) === false) {
			throw new Exception('Failed to make file `'.$this.'`', 1);
		}
		return $this;
	}

	/**
	 * @throws Exception if file not exists
	 * @throws Exception if file is not readable
	 * @throws Exception if failed to get content
	 * @return string
	 */
	final public function getContents() {
		$content = file_get_contents($this->getRealPath());
		if ($content !== false) {
			return $content;
		}
		elseif (!$this->exists()) {
			throw new Exception('File `'.$this.'` not exists.', 1);
		}
		elseif (!$this->isReadable()) {
			throw new Exception('File `'.$this.'` is not readable.', 2);
		}
		else {
			throw new Exception('Failed to get content of file `'.$this.'`.', 3);
		}
	}

	/**
	 * create file, if not exists
	 *
	 * @param  $content
	 * @param  $append = true
	 * @throws Exception if failed to put content
	 * @return File
	 */
	final public function putContents($content, $append = true) {
		if (!file_put_contents($this->getRealPath(), $content, $append ? FILE_APPEND : 0)) {
			throw new Exception('failed to put content in file `'.$this.'`');
		}
		return $this;
	}

	/**
	 * print the file
	 *
	 * @return bool
	 */
	final public function printFile() {
		if (readfile($this->getRealPath()) === false) {
			return false;
		}
		return true;
	}

	/**
	 * @param  string $type allow only one File::TYPE_*
	 * @throws Exception if dir not exists
	 * @throws Exception if is not a dir
	 * @throws Exception if failed to scan dir
	 * @throws RuntimeException if file type not exists
	 * @return File[]
	 */
	final public function scan($type = null) {
		if (!$this->exists()) {
			throw new Exception('dir `'.$this.'` not exists', 1);
		}
		elseif (!$this->isDir()) {
			throw new Exception('file `'.$this.'` is not a dir', 2);
		}

		$fileList = scandir($this->getRealPath());
		if ($fileList === false) {
			throw new Exception('failed to scan dir `'.$this.'`', 3);
		}

		# check type
		$checkMethod = 'is'.ucfirst($type);
		if ($type !== null && !method_exists($this, 'is'.ucfirst($type))) {
			throw new RuntimeException('file type `'.$type.'` not exists.', 4);
		}

		foreach ($fileList as $fileKey => $file) {
			# new file path = old + new
			$fileList[$fileKey] = (new File($this->get()))->attach($file);

			# filter by type if filter-type is not null
			if ($type !== null && !$fileList[$fileKey]->$checkMethod()) {
				unset($fileList[$fileKey]);
			}
		}
		return $fileList;
	}

}
