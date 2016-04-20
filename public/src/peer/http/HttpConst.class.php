<?php
namespace peer\http;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
final class HttpConst {

	# Status: Informational
	const STATUS_100 = 'Continue';
	const STATUS_101 = 'Switching Protocols';
	const STATUS_102 = 'Processing';

	# Status: Success
	const STATUS_200 = 'OK';
	const STATUS_201 = 'Created';
	const STATUS_202 = 'Accepted';
	const STATUS_203 = 'Non-Authoritative Information';
	const STATUS_204 = 'No Content';
	const STATUS_205 = 'Reset Content';
	const STATUS_206 = 'Partial Content';
	const STATUS_207 = 'Multi-Status';
	const STATUS_208 = 'Already Reported';
	const STATUS_226 = 'IM Used';

	# Status: Redirection
	const STATUS_300 = 'Multiple Choices';
	const STATUS_301 = 'Moved Permanently';
	const STATUS_302 = 'Found';
	const STATUS_303 = 'See Other';
	const STATUS_304 = 'Not Modified';
	const STATUS_305 = 'Use Proxy';
	const STATUS_307 = 'Temporary Redirect';
	const STATUS_308 = 'Permanent Redirect';

	# Status: Client Error
	const STATUS_400 = 'Bad Request';
	const STATUS_401 = 'Unauthorized';
	const STATUS_403 = 'Forbidden';
	const STATUS_404 = 'Not Found';
	const STATUS_405 = 'Method Not Allowed';
	const STATUS_406 = 'Not Acceptable';
	const STATUS_407 = 'Proxy Authentication Required';
	const STATUS_408 = 'Request Time-out';
	const STATUS_409 = 'Conflict';
	const STATUS_410 = 'Gone';
	const STATUS_411 = 'Length Required';
	const STATUS_412 = 'Precondition Failed';
	const STATUS_413 = 'Request Entity Too Large';
	const STATUS_414 = 'Request-URL Too Long';
	const STATUS_415 = 'Unsupported Media Type';
	const STATUS_416 = 'Requested range not satisfiable';
	const STATUS_417 = 'Expectation Failed';
	const STATUS_420 = 'Policy Not Fulfilled';
	const STATUS_421 = 'Misdirected Request';
	const STATUS_422 = 'Unprocessable Entity';
	const STATUS_423 = 'Locked';
	const STATUS_424 = 'Failed Dependency';
	const STATUS_425 = 'Unordered Collection';
	const STATUS_426 = 'Upgrade Required';
	const STATUS_428 = 'Precondition Required';
	const STATUS_429 = 'Too Many Requests';
	const STATUS_431 = 'Request Header Fields Too Large';
	const STATUS_451 = 'Unavailable For Legal Reasons';

	# Status: Server Error
	const STATUS_500 = 'Internal Server Error';
	const STATUS_501 = 'Not Implemented';
	const STATUS_502 = 'Bad Gateway';
	const STATUS_503 = 'Service Unavailable';
	const STATUS_504 = 'Gateway Time-out';
	const STATUS_505 = 'HTTP Version not supported';
	const STATUS_506 = 'Variant Also Negotiates';
	const STATUS_507 = 'Insufficient Storage';
	const STATUS_508 = 'Loop Detected';
	const STATUS_509 = 'Bandwidth Limit Exceeded';
	const STATUS_510 = 'Not Extended';

	# Methods
	const METHOD_GET     = 'GET';
	const METHOD_POST    = 'POST';
	const METHOD_HEAD    = 'HEAD';
	const METHOD_PUT     = 'PUT';
	const METHOD_DELETE  = 'DELETE';
	const METHOD_TRACE   = 'TRACE';
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_CONNECT = 'CONNECT';

	public static function isMethod(string $method):bool {
		return defined('self::METHOD_'.$method);
	}

	public static function isStatus(int $status):bool {
		return defined('self::STATUS_'.$status);
	}

}
