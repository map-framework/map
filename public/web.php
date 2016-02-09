<?php

use core\Logger;
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
	 * initialize application and load configs
	 */
	public function __construct() {
		include_once self::AUTOLOAD;

		if (!session_start()) {
			Logger::error('failed to start session');
			exit();
		}

		$this->config = new Bucket();
		# apply public config
		$this->config->applyIni(new File(self::CONFIG_PUBLIC));
		# apply private config
		$this->config->applyIni(new File(self::CONFIG_PRIVATE));
		# apply session config
		if (!isset($_SESSION['config'])) {
			$_SESSION['config'] = array();
		}
		$this->config->applyArray($_SESSION['config']);
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
		$modeSettings = $this->config->get('mode', $request->getMode());

		if ($modeSettings === null) {
			throw new RuntimeException('mode `'.$request->getMode().'` not applied');
		}

		if (!class_exists($modeSettings['handler'])) {
			throw new RuntimeException('mode `'.$request->getMode().'` handler `'.$modeSettings['handler'].'` not applied');
		}

		$handler = new $modeSettings['handler']($this->config);
		if (!($handler instanceof AbstractModeHandler)) {
			throw new RuntimeException('mode `'.$request->getMode().'` handler `'.$modeSettings['handler'].'` is not instance of `handler\mode\AbstractModeHandler`');
		}

		$handler->handle($request, $modeSettings);
	}
	
}

(new Web())->main();