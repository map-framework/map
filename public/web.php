<?php

use handler\mode\AbstractModeHandler;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;

final class Web {
	
	const AUTOLOAD 			  = 'src/misc/autoload.php';
	const CONFIG_PUBLIC		= 'public/web.ini';
	const CONFIG_PRIVATE	= 'private/web.ini';
	
	private $config;
	
	/**
	 * include autoload- and config-files
	 */
	public function __construct() {
		include_once self::AUTOLOAD;
		
		$this->config = new Bucket();
		# apply public config file
		$this->config->applyIni(new File(self::CONFIG_PUBLIC));
		# apply private config file
		$this->config->applyIni(new File(self::CONFIG_PRIVATE));
	}

	/**
	 * call mode handler
	 * @throws Exception
	 * @return void
	 */
	public function main() {
		if (stripos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== false) {
			$scheme = 'https';
		}
		else {
			$scheme = 'http';
		}

		$request = new MAPUrl($scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $this->config);
		$mode = $this->config->get('mode', $request->getMode());

		if ($mode === null) {
			throw new Exception('mode `'.$request->getMode().'` not exists');
		}

		if (!class_exists($mode['handler'])) {
			throw new Exception('mode `'.$request->getMode().'` handler `'.$mode['handler'].'` not exists');
		}

		$handler = new $mode['handler']($this->config);
		if (!($handler instanceof AbstractModeHandler)) {
			throw new Exception('mode `'.$request->getMode().'` handler `'.$mode['handler'].'` is not instance of `handler\mode\AbstractModeHandler`');
		}

	}
	
}

(new Web())->main();