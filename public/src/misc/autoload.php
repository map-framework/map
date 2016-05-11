<?php

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
final class MAPAutoloader {

	const PATH_ROOT   = '../';
	const PATH_SOURCE = 'src/';

	const PATH_PUBLIC  = 'public/';
	const PATH_ADDONS  = 'public/addon/';
	const PATH_PRIVATE = 'private/';

	const FILE_EXTENSION = '.class.php';

	/**
	 * @var array
	 */
	private static $pathList = array();

	public static function addPath(string $path):bool {
		if (is_dir(self::PATH_ROOT.$path)) {
			self::$pathList[] = self::PATH_ROOT.$path;
			return true;
		}
		return false;
	}

	public static function clearPathList() {
		self::$pathList = array();
	}

	public static function refreshPathList() {
		self::clearPathList();

		# Public
		self::addPath(self::PATH_PUBLIC.self::PATH_SOURCE);

		# Add-Ons
		if (is_dir(self::PATH_ROOT.self::PATH_ADDONS)) {
			$addOnNameList = scandir(self::PATH_ROOT.self::PATH_ADDONS);
			foreach ($addOnNameList as $addOnName) {
				if ($addOnName === '.' || $addOnName === '..') {
					continue;
				}
				if (!is_dir(self::PATH_ROOT.self::PATH_ADDONS.$addOnName)) {
					continue;
				}
				self::addPath(self::PATH_ADDONS.$addOnName.'/'.self::PATH_SOURCE);
			}
		}

		# Private
		self::addPath(self::PATH_PRIVATE.self::PATH_SOURCE);
	}

	public static function load(string $nameSpace):bool {
		$filePath = str_replace('\\', '/', $nameSpace).self::FILE_EXTENSION;

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
