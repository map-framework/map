<?php
namespace store;

use store\data\File;
use DateTime;
use RuntimeException;

/**
 * use this to write logs
 */
class Logger {

	const TYPE_ERROR   = 'ERROR';
	const TYPE_WARNING = 'WARNING';
	const TYPE_INFO    = 'INFO';
	const LOG_DIR      = ROOT_DIR.'log/';

	/**
	 * write error logs
	 *
	 * @param  string $message
	 * @return Logger
	 */
	public static function error($message) {
		return self::log(self::TYPE_ERROR, $message);
	}

	/**
	 * write warning logs
	 *
	 * @param  string $message
	 * @return Logger
	 */
	public static function warning($message) {
		return self::log(self::TYPE_WARNING, $message);
	}

	/**
	 * write info logs
	 *
	 * @param  string $message
	 * @return Logger
	 */
	public static function info($message) {
		return self::log(self::TYPE_INFO, $message);
	}

	/**
	 * write logs
	 *
	 * @param  string $type
	 * @param  string $message
	 * @throws RuntimeException if failed to create log-dir
	 * @throws RuntimeException if log-file exists & is dir or link
	 * @return Logger
	 */
	protected static function log($type, $message) {
		$now = new DateTime();

		# create dir
		$logFile = new File(self::LOG_DIR.$now->format('Y-M'));
		if (!$logFile->isDir()) {
			$logFile->makeDir();
		}

		# check file
		$logFile->attach($now->format('d').'.log');
		if (!$logFile->isFile() && $logFile->exists()) {
			throw new RuntimeException('log-file is not a file `'.$logFile.'`');
		}

		# write log
		$logFile->putContents('['.$type.'] '.$message.PHP_EOL);
	}

}