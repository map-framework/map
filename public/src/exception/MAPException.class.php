<?php
namespace exception;

use Exception;
use Throwable;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class MAPException extends Exception implements Throwable {

	/**
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct(string $message, int $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @param  mixed $data
	 * @return string
	 */
	final protected function export($data):string {
		if (is_null($data)) {
			return 'NULL';
		}
		if (is_bool($data)) {
			return $data === true ? 'TRUE' : 'FALSE';
		}
		if (is_integer($data) || is_float($data)) {
			return $data;
		}
		if (is_string($data)) {
			return '"'.$data.'"';
		}
		if (is_array($data)) {
			return 'ARRAY';
		}
		if (is_resource($data)) {
			return 'RESOURCE';
		}
		if (is_object($data)) {
			return get_class($data).(method_exists($data, '__toString') ? '("'.$data.'")' : '');
		}
		return 'UNKNOWN';
	}

}
