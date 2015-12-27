<?php
namespace store\data;

/**
 * simple File-Class
 * @TODO write unit-tests
 */
class File extends store\data\AbstractData {
	
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
	final public function isWriteable() {
		return is_writeable($this->get());
	}
	
	/**
	 * @return bool
	 */
	final public function isExecuteable() {
		return is_executeable($this->get());
	}

	/**
	 * @param octal $read
	 * @param octal $write
	 * @param octal $execute
	 * @return bool
	 */
	final public function changeMode($read, $write, $execute) {
		if (!is_int($read) || $read < 0 || $read > 7) {
			throw new RuntimeExcepton('Invalid Read-Mode `'.$read.'`.');
		}
		if (!is_int($write) || $write < 0 || $write > 7) {
			throw new RuntimeException('Invalid Write-Mode `'.$write.'`.');
		}
		if (!is_int($execute) || $execute < 0 || $execute > 7) {
			throw new RuntimeException('Invalid Execute-Mode `'.$execute.'`.');
		}
		return chmod($this->get(), $read.$write.$execute);
	}
	
	/**
	 * @return string
	 * @throws RuntimeException
	 */
	final public function getContents() {
		$content = file_get_contents($this->get());
		if ($content !== false) {
			return $content;
		}
		if (!$this->exists()) {
			throw new RuntimeException('File `'.$this.'` not exists.');
		}
		elseif (!$this->readable()) {
			throw new RuntimeException('File `'.$this.'` is not readable.');
		}
		else {
			throw new RuntimeException('Failed to get content of file `'.$this->get().'`.');
		}
	}
	
}