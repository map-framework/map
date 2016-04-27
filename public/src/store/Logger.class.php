<?php
namespace store;

use Exception;
use store\data\File;
use DateTime;
use RuntimeException;
use xml\Tree;

/**
 * use this to write logs
 */
class Logger {

	const LOG_DIR      = 'log';
	const TYPE_ERROR   = 'ERROR';
	const TYPE_WARNING = 'WARN ';
	const TYPE_INFO    = 'INFO ';
	const TYPE_DEBUG   = 'DEBUG';

	/**
	 * write error logs
	 *
	 * @param  string $message
	 */
	public static function error($message) {
		self::log(self::TYPE_ERROR, $message);
	}

	/**
	 * write warning logs
	 *
	 * @param  string $message
	 */
	public static function warning($message) {
		self::log(self::TYPE_WARNING, $message);
	}

	/**
	 * write info logs
	 *
	 * @param  string $message
	 */
	public static function info($message) {
		self::log(self::TYPE_INFO, $message);
	}

	/**
	 * write debug logs (only DEV)
	 *
	 * @param  string $message
	 */
	public static function debug($message) {
		if (constant('ENVIRONMENT') === 'DEV') {
			self::log(self::TYPE_DEBUG, $message);
		}
	}

	/**
	 * write logs
	 *
	 * @param  string $type
	 * @param  string $message
	 * @throws RuntimeException if failed to create log-dir
	 * @throws RuntimeException if log-file exists & is dir or link
	 */
	protected static function log($type, $message) {
		$now = new DateTime();

		# create dir
		$logFile = (new File(self::LOG_DIR))->attach($now->format('Y-M'));
		if (!$logFile->isDir()) {
			$logFile->makeDir();
		}

		# check file
		$logFile->attach($now->format('d').'.log');
		if (!$logFile->isFile() && $logFile->exists()) {
			throw new RuntimeException('log-file is not a file `'.$logFile.'`');
		}

		# write log
		$time = $now->format('H:i:s');
		$logFile->putContents('['.$type.' @ '.$time.'] '.$message.PHP_EOL);
	}

	/**
	 * @throws Exception
	 */
	public static function storeTree(Tree $tree, string $extension = ''):File {
		return self::storeText($tree->getSource(true), $extension);
	}

	/**
	 * @throws Exception
	 */
	public static function storeText(string $text, string $extension = ''):File {
		$now = new DateTime();
		do {
			$file = (new File(self::LOG_DIR))
					->attach($now->format('Y-M'))
					->attach($now->format('d'))
					->makeDir()
					->attach(bin2hex(random_bytes(4)).$extension);
		}
		while ($file->exists());
		return $file->putContents($text);
	}

}
