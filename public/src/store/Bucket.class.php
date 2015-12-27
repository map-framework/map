<?php
namespace store;

/**
 * @TODO write unit-tests
 */
class Bucket {
	
	const PATTERN_GROUP = '/^[A-Za-z0-9_-]{3,32}$/';
	const PATTERN_KEY = '/^[A-Za-z0-9_-]{3,32}$/';
	
	private $data = array();
	
	/**
	 * @param string $group
	 * @param string $key
	 */
	final public function exists($group, $key) {
		return isset($this->data[$group][$key]);
	}
	
	/**
	 * @param string $group
	 * @param string $key
	 * @param string $default
	 */
	final public function get($group, $key, $default = null) {
		if ($this->exists($group, $key)) {
			return $this->data[$group][$key];
		}
		return $default;
	}

	/**
	 * @param string $group
	 * @param string $key
	 * @param string $value
	 * @throws RuntimeException if group or key invalid
	 */
	final public function set($group, $key, $value) {
		if (!preg_match(PATTERN_GROUP, $group)) {
			throw new RuntimeException('Invalid group `'.$group.'`.');
		}
		if (!preg_match(PATTERN_KEY, $key)) {
			throw new RuntimeException('Invalid key `'.$key.'`.');
		}
		$this->data[$group][$key] = $value;
	}
	
}