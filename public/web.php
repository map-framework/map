<?php

use store\Logger;
use handler\mode\AbstractModeHandler;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;

final class Web {

	const AUTOLOAD       = 'src/misc/autoload.php';
	const CONFIG_PUBLIC  = 'public/web.ini';
	const CONFIG_PRIVATE = 'private/web.ini';

	/**
	 * @var Bucket
	 */
	private $config;

	/**
	 * @var MAPUrl
	 */
	private $request;

	/**
	 * initialize application and load configs
	 */
	public function __construct() {
		include_once self::AUTOLOAD;

		if (strtolower(ini_get('display_errors')) === 'off') {
			define('ENVIRONMENT', 'LIVE');
		}
		else {
			define('ENVIRONMENT', 'DEV');
		}

		if (!session_start()) {
			Logger::error('failed to start session');
			exit();
		}

		# load public & private config
		$this->config = (new Bucket())
				->applyIni(new File(self::CONFIG_PUBLIC))
				->applyIni(new File(self::CONFIG_PRIVATE));

		# load MAPUrl
		if (stripos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== false) {
			$scheme = 'https';
		}
		else {
			$scheme = 'http';
		}
		$this->request = new MAPUrl($scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $this->config);

		# load area & page config
		$configPathList = array(
				'private/src/area/'.$this->request->getArea().'/config/area.ini',
				'private/src/area/'.$this->request->getArea().'/config/page/'.$this->request->getPage().'.ini'
		);
		foreach ($configPathList as $configPath) {
			$configFile = new File($configPath);
			if (!$configFile->isFile()) {
				if (constant('ENVIRONMENT') === 'DEV') {
					Logger::info('config-file `'.$configFile.'` not found');
				}
				continue;
			}
			$this->config->applyIni($configFile);
		}

		# load session config
		if (!isset($_SESSION['config'])) {
			$_SESSION['config'] = array();
		}
		$this->config->applyArray($_SESSION['config']);
	}

	/**
	 * call mode handler
	 *
	 * @throws Exception
	 * @return void
	 */
	public function main() {
		$modeSettings = $this->config->get('mode', $this->request->getMode());

		if ($modeSettings === null) {
			throw new RuntimeException('mode `'.$this->request->getMode().'` not applied');
		}

		if (!class_exists($modeSettings['handler'])) {
			throw new RuntimeException('mode `'.$this->request->getMode().'` is invalid');
		}

		$handler = new $modeSettings['handler']($this->config, $this->request, $modeSettings);
		if (!($handler instanceof AbstractModeHandler)) {
			throw new RuntimeException(
					'mode `'
					.$this->request->getMode()
					.'` handler `'
					.$modeSettings['handler']
					.'` is not instance of `handler\mode\AbstractModeHandler`'
			);
		}

		$handler->handle();
	}

}

(new Web())->main();
