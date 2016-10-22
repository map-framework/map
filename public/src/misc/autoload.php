<?php

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
final class MAPAutoloader {

	const PATH_ROOT           = '../';
	const PATH_SOURCE         = 'src/';
	const PATH_PUBLIC         = 'public/';
	const PATH_PUBLIC_SOURCE  = self::PATH_PUBLIC.self::PATH_SOURCE;
	const PATH_PRIVATE        = 'private/';
	const PATH_PRIVATE_SOURCE = self::PATH_PRIVATE.self::PATH_SOURCE;
	const PATH_ADD_ONS        = self::PATH_PUBLIC.'addon/';

	const FILE_PHP_EXTENSION = '.class.php';

	/**
	 * @var string[]
	 */
	private static $pathList = array();

	public static function addPath(string $path):bool {
		if (is_dir(self::PATH_ROOT.$path)) {
			self::$pathList[] = self::PATH_ROOT.(substr($path, -1) !== '/' ? $path.'/' : $path);
			return true;
		}
		return false;
	}

	public static function refreshPathList() {
		self::$pathList = array();

		# Public
		self::addPath(self::PATH_PUBLIC_SOURCE);

		# Add-Ons
		if (is_dir(self::PATH_ROOT.self::PATH_ADD_ONS)) {
			foreach (scandir(self::PATH_ROOT.self::PATH_ADD_ONS) as $addOnName) {
				if ($addOnName[0] !== '.') {
					self::addPath(self::PATH_ADD_ONS.$addOnName.'/'.self::PATH_SOURCE);
				}
			}
		}

		# Private
		self::addPath(self::PATH_PRIVATE_SOURCE);
	}

	public static function load(string $nameSpace):bool {
		$filePath = str_replace('\\', '/', $nameSpace).self::FILE_PHP_EXTENSION;

		foreach (array_reverse(self::$pathList) as $path) {
			if (is_file($path.$filePath)) {
				/** @noinspection PhpIncludeInspection */
				include_once $path.$filePath;
				return true;
			}
		}
		return false;
	}

}

MAPAutoloader::refreshPathList();
spl_autoload_register('MAPAutoloader::load', true);

