<?php
namespace store\data;

use Exception;
use RuntimeException;
use store\data\AbstractData;

/**
 * simple File-Class
 * @TODO write unit-tests
 */
class File extends AbstractData {
	
	const TYPE_FILE				= 'file';
	const TYPE_DIR				= 'dir';
	const TYPE_LINK				= 'link';

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
	 * @param  string $path
	 * @return File
	 */
	final public function attach($path) {
		$glue = '';
		if (substr($this->get(), -1) !== '/' && substr($path, 0, 1) !== '/') {
			$glue = '/';
		}
		return $this->set($this->get().$glue.$path);
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
		elseif (!is_int($write) || $write < 0 || $write > 7) {
			throw new Exception('Invalid Write-Mode `'.$write.'`.', 2);
		}
		elseif (!is_int($execute) || $execute < 0 || $execute > 7) {
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
		elseif (!$this->exists()) {
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
	 * @param  string $type allow only one File::TYPE_*
	 * @throws Exception if directory not exists
	 * @throws Exception if is not a directory
	 * @throws Exception if failed to scan directory 
	 * @throws RuntimeException if file type not exists
	 * @return File[]
	 */
	final public function scan($type = null) {
		if (!$this->exists($this->get())) {
			throw new Exception('Directory `'.$this->get().'` not exists.', 1);
		}
		elseif (!$this->isDir($this->get())) {
			throw new Exception('Directory `'.$this->get().'` is not a directory.', 2);
		}

		$fileList = scandir($this->get());
		if ($fileList === false) {
			throw new Exception('Failed to scan directory `'.$this->get().'`.', 3);
		}

		# check type
		$checkMethod = 'is'.ucfirst($type);
		if ($type !== null && !method_exists($this, 'is'.ucfirst($type))) {
			throw new RuntimeException('File type `'.$type.'` not exists.', 4);
		}
		
		foreach ($fileList as $fileKey => $file) {
			# new file path = old + new
			$fileList[$fileKey] = (new File($this->get()))
				->attach($file);

			# filter by type if filter-type is not null
			if ($type !== null && !$fileList[$fileKey]->$checkMethod()) {
				unset($fileList[$fileKey]);
			}
		}
		return $fileList;
	}
	
}