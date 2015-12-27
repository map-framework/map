<?php

final class Web {
	
	use store\Bucket;
	
	public static $config;
	
	public function __construct() {
		$this->config = new Bucket();	
	}
	
	public function main() {
		
	}
	
}

/**
 * simple autoloader
 * @param string $namespace
 * @throws RuntimeException if class not found
 */
function __autoload($namespace) {
	$directoryList = array('src/',	'../private');
	$items = explode('\\', $namespace);
	foreach ($directoryList as $directory) {
		$path = $directory.implode('/', $items).'.class.php';
		if (file_exists($path)) {
			return include_once $path;
		}
	}
	throw new RuntimeException('Class `'.$namespace.'` not found.');
}

(new Web())->main();