<?php
namespace util;

use Exception;
use data\file\File;
use DateTime;
use xml\Tree;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Logger {

	const LOG_DIR = 'log';

	const TYPE_ERROR   = 'ERROR';
	const TYPE_WARNING = 'WARN ';
	const TYPE_INFO    = 'INFO ';
	const TYPE_DEBUG   = 'DEBUG';

	public static function error(string $message) {
		self::log(self::TYPE_ERROR, $message);
	}

	public static function warning(string $message) {
		self::log(self::TYPE_WARNING, $message);
	}

	public static function info(string $message) {
		self::log(self::TYPE_INFO, $message);
	}

	public static function debug(string $message) {
		if (constant('ENVIRONMENT') === 'DEV') {
			self::log(self::TYPE_DEBUG, $message);
		}
	}

	protected static function log(string $type, string $message) {
		$now = new DateTime();

		(new File(self::LOG_DIR))
				->makeDir()
				->attach($now->format('Y-M'))
				->makeDir()
				->attach($now->format('d').'.log')
				->putContents(
						sprintf(
								'[%s @ %s] %s',
								$type,
								$now->format('H:i:s'),
								$message.PHP_EOL
						),
						true
				);
	}

	public static function storeTree(Tree $tree, string $extension = ''):File {
		return self::storeText($tree->getSource(true), $extension);
	}

	public static function storeText(string $text, string $extension = ''):File {
		$now = new DateTime();
		do {
			$file = (new File(self::LOG_DIR))
					->makeDir()
					->attach($now->format('Y-M'))
					->makeDir()
					->attach($now->format('d'))
					->makeDir()
					->attach(bin2hex(random_bytes(4)).$extension);
		}
		while ($file->exists());
		return $file->putContents($text, false);
	}

}
