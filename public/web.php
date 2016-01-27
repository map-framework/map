<?php
	
use store\Bucket;
use store\data\File;

final class Web {
	
	const AUTOLOAD 			  = 'src/misc/autoload.php';
	const CONFIG_PUBLIC		= 'public/web.ini';
	const CONFIG_PRIVATE	= 'private/web.ini';
	
	public $config;
	
	/**
	 * include autoload- and config-files
	 */
	public function __construct() {
		include_once self::AUTOLOAD;
		
		$this->config = new Bucket();
		# apply public config file
		$this->config->apply(new File(self::CONFIG_PUBLIC));
		# apply private config file
		$this->config->apply(new File(self::CONFIG_PRIVATE));
	}
	
	/**
	 * load config file
	 * @return void
	 */
	public function main() {
		
	}
	
}

(new Web())->main();