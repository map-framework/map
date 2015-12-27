<?php
namespace store\data;

/**
 * @TODO write unit-tests
 */
abstract class AbstractData {
	
	private $data = '';
	
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
	final protected function set($data) {
		if (!is_string($data)) {
			throw new RuntimeException();
		}
		$this->data = $data;
		return $this;
	}
	
	/**
	 * @return string
	 */
	final protected function get() {
		return $this->data;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->get();
	}
	
}