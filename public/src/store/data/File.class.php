<?php
namespace store\data;

use Exception;
use RuntimeException;

class File extends AbstractData {

	const TYPE_FILE       = 'file';
	const TYPE_DIR        = 'dir';
	const TYPE_LINK       = 'link';

	const MAKE_DIR_MODE		= 0777;
	const MAKE_FILE_MODE	= 0777;
	const MAKE_LINK_MODE	= 0777;

	/**
	 * @param string $file
	 * @return File
	 */
	public function set($file) {
		if (substr($file, -1) === '/') {
			$file = substr($file, 0, -1);
		}
		return parent::set(constant('ROOT_DIR').$file);
	}

	/**
	 * @return bool
	 */
	final public function exists() {
		return file_exists($this->get());
	}

	/**
	 * @return bool
	 */
	final public function isFile() {
		return is_file($this->get());
	}

	/**
	 * @return bool
	 */
	final public function isDir() {
		return is_dir($this->get());
	}

	/**
	 * @return bool
	 */
	final public function isLink() {
		return is_link($this->get());
	}

	/**
	 * @return bool
	 */
	final public function isReadable() {
		return is_readable($this->get());
	}

	/**
	 * @return bool
	 */
	final public function isWritable() {
		return is_writeable($this->get());
	}
	
	/**
	 * @return bool
	 */
	final public function isExecutable() {
		return is_executable($this->get());
	}

	/**
	 * @param  string $path
	 * @return File
	 */
	final public function attach($path) {
		if (substr($path, 0, 1) === '/') {
			$path = substr($path, 1);
		}
		return $this->set($this->get().'/'.$path);
	}

	/**
	 * @param  int $user
	 * @param  int $group
	 * @param  int $other
	 * @throws RuntimeException if invalid user mode
	 * @throws RuntimeException if invalid group mode
	 * @throws RuntimeException if invalid other mode
	 * @throws Exception if changing mode failed
	 * @return File
	 */
	final public function changeMode($user, $group, $other) {
		if (!is_int($user) || $user < 0 || $user > 7) {
			throw new RuntimeException('Invalid User-Mode `'.$user.'`.', 1);
		}
		elseif (!is_int($group) || $group < 0 || $group > 7) {
			throw new RuntimeException('Invalid Group-Mode `'.$group.'`.', 2);
		}
		elseif (!is_int($other) || $other < 0 || $other > 7) {
			throw new RuntimeException('Invalid Other-Mode `'.$other.'`.', 3);
		}
		elseif (!$this->exists()) {
			throw new Exception('File or Dir `'.$this.'` not exists.', 1);
		}
		elseif (chmod($this->get(), $user.$group.$other) === false) {
			throw new Exception('Failed to change mode to `'.$user.$group.$other.'` in file `'.$this.'`.', 2);
		}
		else {
			return $this;
		}
	}

	/**
	 * @throws Exception
	 * @return File
	 */
	final public function makeDir() {
		if (!mkdir($this->get(), self::MAKE_DIR_MODE, true)) {
			throw new Exception('Failed to make dir `'.$this.'`', 1);
		}
		return $this;
	}

	/**
	 * @throws Exception
	 * @return File
	 */
	final public function makeFile() {
		if (file_put_contents($this->get(), '', self::MAKE_FILE_MODE) === false) {
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
		$content = file_get_contents($this->get());
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
	 * @param  $content
	 * @param  $append = true
	 * @throws Exception if failed to put content
	 * @return File
	 */ 
	final public function putContents($content, $append = true) {
		if (!file_put_contents($this->get(), $content, $append ? FILE_APPEND : 0)) {
			throw new Exception('failed to put content in file `'.$this.'`');
		}
		return $this;
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

		$fileList = scandir($this->get());
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