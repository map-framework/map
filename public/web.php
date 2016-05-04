<?php

use util\Logger;
use handler\mode\AbstractModeHandler;
use util\Bucket;
use data\file\File;
use util\data\net\MAPUrl;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
final class Web {

	const AUTOLOAD       = 'src/misc/autoload.php';
	const CONFIG_PUBLIC  = 'public/web.ini';
	const CONFIG_PRIVATE = 'private/web.ini';

	const ENVIRONMENT_DEV  = 'DEV';
	const ENVIRONMENT_PROD = 'PROD';

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

		if (strtolower(ini_get('display_errors')) !== 'off') {
			define('ENVIRONMENT', self::ENVIRONMENT_DEV);
		}
		else {
			define('ENVIRONMENT', self::ENVIRONMENT_PROD);
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
		Logger::debug(
				'REQUEST ('.
				'mode: `'.$this->request->getMode().'` '.
				'area: `'.$this->request->getArea().'` '.
				'page: `'.$this->request->getPage().'`)'
		);

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

	public function callModeHandler() {
		$modeSettings = $this->config->get('mode', $this->request->getMode());

		if ($modeSettings === null) {
			throw new RuntimeException('mode `'.$this->request->getMode().'` not applied');
		}

		if (!isset($modeSettings['handler']) || !class_exists($modeSettings['handler'])) {
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

		try {
			$handler->handle();
		}
		catch (Exception $exception) {
			$this->callExceptionHandlers($exception);
		}
	}

	public function callExceptionHandlers(Exception $exception) {
		throw $exception;
		# TODO implement method
	}

	public static function isDev():bool {
		return constant('ENVIRONMENT') === self::ENVIRONMENT_DEV;
	}

	public static function isProd():bool {
		return constant('ENVIRONMENT') === self::ENVIRONMENT_PROD;
	}

}

(new Web())->callModeHandler();
