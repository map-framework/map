<?php

use data\InvalidDataException;
use data\map\AddOn;
use data\norm\ClassNotFoundException;
use data\norm\ClassObject;
use data\norm\InstanceException;
use handler\exception\ExceptionHandlerInterface;
use util\Logger;
use util\Bucket;
use data\file\File;
use data\net\MAPUrl;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
final class Web {

	const PATH_AUTOLOAD       = 'src/misc/autoload.php';
	const PATH_CONFIG_PUBLIC  = 'public/web.ini';
	const PATH_CONFIG_PRIVATE = 'private/web.ini';

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
		/** @noinspection PhpIncludeInspection */
		include_once self::PATH_AUTOLOAD;

		define('ENVIRONMENT', $this->getEnvironment());

		if (!session_start()) {
			Logger::error('Failed to start session (Early-Exit).');
			exit();
		}

		try {
			# Config: Public
			$this->config = (new Bucket())
					->applyIni(new File(self::PATH_CONFIG_PUBLIC));

			# Config: Add-Ons
			foreach (AddOn::getList() as $addOn) {
				if ($addOn instanceof AddOn) {
					$addOn->assertIsInstalled();

					$this->config->applyIni($addOn->getConfigFile());
				}
			}

			# Config: Private
			$this->config->applyIni(new File(self::PATH_CONFIG_PRIVATE));

			# Request: analyze
			$scheme        = stripos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') ? 'https' : 'http';
			$this->request = new MAPUrl($scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $this->config);
			Logger::debug(
					'REQUEST',
					array(
							'mode'      => $this->request->getMode(),
							'area'      => $this->request->getArea(),
							'page'      => $this->request->getPage(),
							'inputList' => $this->request->getInputList()
					)
			);

			# Config: Area
			$areaConfigFile = $this->request->getArea()->getConfigFile();
			if ($areaConfigFile->isFile()) {
				$this->config->applyIni($areaConfigFile);
			}
			else {
				Logger::debug('Config-File not found', ['file' => $areaConfigFile]);
			}

			# Config: Page
			$pageConfigFile = $this->request->getArea()->getDir()
					->attach('config')
					->attach('page')
					->attach($this->request->getPage().'.ini');
			if ($pageConfigFile->isFile()) {
				$this->config->applyIni($pageConfigFile);
			}
			else {
				Logger::debug('Config-File not found', ['file' => $pageConfigFile]);
			}

			# Config: Session
			$this->config->applyArray($_SESSION['config'] ?? array());
		}
		catch (Throwable $e) {
			$this->callExceptionHandlers($e);
			exit();
		}
	}

	public function callModeHandler() {
		try {
			$mode = $this->request->getMode();
			$mode->assertExists($this->config);

			$handlerNameSpace = $mode->getHandler($this->config)->get();
			/** @noinspection PhpUndefinedMethodInspection */
			(new $handlerNameSpace($this->config, $this->request))->handle();
		}
		catch (Throwable $exception) {
			$this->callExceptionHandlers($exception);
		}
	}

	/**
	 * @throws InvalidDataException
	 * @throws ClassNotFoundException
	 * @throws InstanceException
	 * @throws Throwable
	 */
	public function callExceptionHandlers(Throwable $exception) {
		$handlerListKey = $this->isDev() ? 'devHandler' : 'prodHandler';

		if (!$this->config->isNull('exception', $handlerListKey)) {
			$this->config->assertIsArray('exception', $handlerListKey);

			$handlerNameSpaceList = array_reverse($this->config->get('exception', $handlerListKey));
			foreach ($handlerNameSpaceList as $handlerNameSpace) {
				$handlerClass = new ClassObject($handlerNameSpace);
				$handlerClass->assertExists();
				$handlerClass->assertImplementsInterface(new ClassObject(ExceptionHandlerInterface::class));

				/** @noinspection PhpUndefinedMethodInspection */
				if ($handlerNameSpace::handle($exception)) {
					return;
				}

				Logger::info(
						'Exception-Handler does not handling Exception.',
						array(
								'Exception-Handler Class' => $handlerClass,
								'Exception'               => $exception
						)
				);
			}
		}
		Logger::warning(
				'No Exception-Handler does handling Exception.',
				array(
						'Handler NameSpace List' => $handlerNameSpaceList ?? array(),
						'Exception'              => $exception
				)
		);
		throw $exception;
	}

	public static function isDev():bool {
		return constant('ENVIRONMENT') === self::ENVIRONMENT_DEV;
	}

	public static function isProd():bool {
		return constant('ENVIRONMENT') === self::ENVIRONMENT_PROD;
	}

	private function getEnvironment():string {
		return strtolower(ini_get('display_errors')) !== 'off' ? self::ENVIRONMENT_DEV : self::ENVIRONMENT_PROD;
	}

}

(new Web())->callModeHandler();
