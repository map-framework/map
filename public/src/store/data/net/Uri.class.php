<?php
namespace src\store\data\net;

/**
 * @TODO write unit-tests
 */
class Uri extends store\data\AbstractData {
	
	const PATTERN = '[A-Za-z0-9-._~:\/?#\[\]@!$&\'()*+,;=]+';
	
	/**
	 * @param string $uri
	 * @return Uri
	 */
	protected function set($uri) {
		if (!is_string($uri) || !self::match(self::PATTERN, $uri)) {
			throw new RuntimeException('URI `'.$uri.'` is not valid');
		}
		$this->data = $uri;
		return $this;
	}
	
}