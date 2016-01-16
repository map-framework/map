<?php
namespace src\store\data;

/**
 * simple File-Class
 * @TODO write unit-tests
 */
class File extends \store\data\AbstractData {
	
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
	 * @param  octal $read
	 * @param  octal $write
	 * @param  octal $execute
	 * @throws Exception if invalid read mode
	 * @throws Exception if invalid write mode
	 * @throws Exception if invalid execute mode
	 * @return bool
	 */
	final public function changeMode($read, $write, $execute) {
		if (!is_int($read) || $read < 0 || $read > 7) {
			throw new Exception('Invalid Read-Mode `'.$read.'`.', 1);
		}
		if (!is_int($write) || $write < 0 || $write > 7) {
			throw new Exception('Invalid Write-Mode `'.$write.'`.', 2);
		}
		if (!is_int($execute) || $execute < 0 || $execute > 7) {
			throw new Exception('Invalid Execute-Mode `'.$execute.'`.', 3);
		}
		return chmod($this->get(), $read.$write.$execute);
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
		if (!$this->exists()) {
			throw new Exception('File `'.$this.'` not exists.', 1);
		}
		elseif (!$this->readable()) {
			throw new Exception('File `'.$this.'` is not readable.', 2);
		}
		else {
			throw new Exception('Failed to get content of file `'.$this->get().'`.', 3);
		}
	}

	/**
	 * scan directory
	 * @return array
	 */
	final public function scan() {

	}
	
}