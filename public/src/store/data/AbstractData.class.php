<?php
namespace store\data;

/**
 * @TODO write unit-tests
 */
abstract class AbstractData {
	
	protected $data = '';
	
	/**
	 * @param mixed $data
	 */
	public function __construct($data) {
		$this->set($data);
	}
	
	/**
	 * @param string $data
	 * @throws RuntimeException
	 * @return AbstractData
	 */
	protected function set($data) {
		if (!is_string($data)) {
			throw new RuntimeException();
		}
		$this->data = $data;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function get() {
		return $this->data;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->get();
	}
	
	/**
	 * @param string $pattern
	 * @param string $subject
	 * @return bool
	 */
	final protected static function match($pattern, $subject) {
		if (!is_string($subject)) {
			return false;
		}
		return (bool) preg_match('/'.$pattern.'/', $subject);
	}
	
}