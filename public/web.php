<?php
	
use store\Bucket;

final class Web {
	
	const AUTOLOADER = 'src/core/misc/autoloader.php';
	
	public static $config;
	
	public function __construct() {
		include_once self::AUTOLOADER;
		$this->config = new Bucket();
	}
	
	public function main() {
		
	}
	
}

(new Web())->main();