<?php
namespace data\peer\http;

use data\AbstractEnum;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
final class StatusEnum extends AbstractEnum {

	const CONTINUE                        = 'CONTINUE';
	const SWITCHING_PROTOCOLS             = 'SWITCHING_PROTOCOLS';
	const PROCESSING                      = 'PROCESSING';
	const OK                              = 'OK';
	const CREATED                         = 'CREATED';
	const ACCEPTED                        = 'ACCEPTED';
	const NON_AUTHORITATIVE_INFORMATION   = 'NON_AUTHORITATIVE_INFORMATION';
	const NO_CONTENT                      = 'NO_CONTENT';
	const RESET_CONTENT                   = 'RESET_CONTENT';
	const PARTIAL_CONTENT                 = 'PARTIAL_CONTENT';
	const MULTI_STATUS                    = 'MULTI_STATUS';
	const ALREADY_REPORTED                = 'ALREADY_REPORTED';
	const IM_USED                         = 'IM_USED';
	const MULTIPLE_CHOICES                = 'MULTIPLE_CHOICES';
	const MOVED_PERMANENTLY               = 'MOVED_PERMANENTLY';
	const FOUND                           = 'FOUND';
	const SEE_OTHER                       = 'SEE_OTHER';
	const NOT_MODIFIED                    = 'NOT_MODIFIED';
	const USE_PROXY                       = 'USE_PROXY';
	const TEMPORARY_REDIRECT              = 'TEMPORARY_REDIRECT';
	const PERMANENT_REDIRECT              = 'PERMANENT_REDIRECT';
	const BAD_REQUEST                     = 'BAD_REQUEST';
	const UNAUTHORIZED                    = 'UNAUTHORIZED';
	const FORBIDDEN                       = 'FORBIDDEN';
	const NOT_FOUND                       = 'NOT_FOUND';
	const METHOD_NOT_ALLOWED              = 'METHOD_NOT_ALLOWED';
	const NOT_ACCEPTABLE                  = 'NOT_ACCEPTABLE';
	const PROXY_AUTHENTICATION_REQUIRED   = 'PROXY_AUTHENTICATION_REQUIRED';
	const REQUEST_TIME_OUT                = 'REQUEST_TIME_OUT';
	const CONFLICT                        = 'CONFLICT';
	const GONE                            = 'GONE';
	const LENGTH_REQUIRED                 = 'LENGTH_REQUIRED';
	const PRECONDITION_FAILED             = 'PRECONDITION_FAILED';
	const REQUEST_ENTITY_TOO_LARGE        = 'REQUEST_ENTITY_TOO_LARGE';
	const REQUEST_URL_TOO_LONG            = 'REQUEST_URL_TOO_LONG';
	const UNSUPPORTED_MEDIA_TYPE          = 'UNSUPPORTED_MEDIA_TYPE';
	const REQUEST_RANGE_NOT_SATISFIABLE   = 'REQUEST_RANGE_NOT_SATISFIABLE';
	const EXPECTATION_FAILED              = 'EXPECTATION_FAILED';
	const POLICY_NOT_FULFILLED            = 'POLICY_NOT_FULFILLED';
	const MISDIRECTED_REQUEST             = 'MISDIRECTED_REQUEST';
	const UNPROCESSABLE_ENTITY            = 'UNPROCESSABLE_ENTITY';
	const LOCKED                          = 'LOCKED';
	const FAILED_DEPENDENCY               = 'FAILED_DEPENDENCY';
	const UNORDERED_COLLECTION            = 'UNORDERED_COLLECTION';
	const UPGRADE_REQUIRED                = 'UPGRADE_REQUIRED';
	const PRECONDITION_REQUIRED           = 'PRECONDITION_REQUIRED';
	const TOO_MANY_REQUESTS               = 'TOO_MANY_REQUESTS';
	const REQUEST_HEADER_FIELDS_TOO_LARGE = 'REQUEST_HEADER_FIELDS_TOO_LARGE';
	const UNAVAILABLE_FOR_LEGAL_REASONS   = 'UNAVAILABLE_FOR_LEGAL_REASONS';
	const INTERNAL_SERVER_ERROR           = 'INTERNAL_SERVER_ERROR';
	const NOT_IMPLEMENTED                 = 'NOT_IMPLEMENTED';
	const BAD_GATEWAY                     = 'BAD_GATEWAY';
	const SERVICE_UNAVAILABLE             = 'SERVICE_UNAVAILABLE';
	const GATEWAY_TIME_OUT                = 'GATEWAY_TIME_OUT';
	const HTTP_VERSION_NOT_SUPPORTED      = 'HTTP_VERSION_NOT_SUPPORTED';
	const VARIANT_ALSO_NEGOTIATES         = 'VARIANT_ALSO_NEGOTIATES';
	const INSUFFICIENT_STORAGE            = 'INSUFFICIENT_STORAGE';
	const LOOP_DETECTED                   = 'LOOP_DETECTED';
	const BANDWIDTH_LIMIT_EXCEEDED        = 'BANDWIDTH_LIMIT_EXCEEDED';
	const NOT_EXTENDED                    = 'NOT_EXTENDED';

	private static $informationalList = array(
			self::CONTINUE            => 100,
			self::SWITCHING_PROTOCOLS => 101,
			self::PROCESSING          => 102
	);

	private static $successList       = array(
			self::OK                            => 200,
			self::CREATED                       => 201,
			self::ACCEPTED                      => 202,
			self::NON_AUTHORITATIVE_INFORMATION => 203,
			self::NO_CONTENT                    => 204,
			self::RESET_CONTENT                 => 205,
			self::PARTIAL_CONTENT               => 206,
			self::MULTI_STATUS                  => 207,
			self::ALREADY_REPORTED              => 208,
			self::IM_USED                       => 226
	);

	private static $redirectionList   = array(
			self::MULTIPLE_CHOICES   => 300,
			self::MOVED_PERMANENTLY  => 301,
			self::FOUND              => 302,
			self::SEE_OTHER          => 303,
			self::NOT_MODIFIED       => 304,
			self::USE_PROXY          => 305,
			self::TEMPORARY_REDIRECT => 307,
			self::PERMANENT_REDIRECT => 308
	);

	private static $clientErrorList   = array(
			self::BAD_REQUEST                     => 400,
			self::UNAUTHORIZED                    => 401,
			self::FORBIDDEN                       => 403,
			self::NOT_FOUND                       => 404,
			self::METHOD_NOT_ALLOWED              => 405,
			self::NOT_ACCEPTABLE                  => 406,
			self::PROXY_AUTHENTICATION_REQUIRED   => 407,
			self::REQUEST_TIME_OUT                => 408,
			self::CONFLICT                        => 409,
			self::GONE                            => 410,
			self::LENGTH_REQUIRED                 => 411,
			self::PRECONDITION_FAILED             => 412,
			self::REQUEST_ENTITY_TOO_LARGE        => 413,
			self::REQUEST_URL_TOO_LONG            => 414,
			self::UNSUPPORTED_MEDIA_TYPE          => 415,
			self::REQUEST_RANGE_NOT_SATISFIABLE   => 416,
			self::EXPECTATION_FAILED              => 417,
			self::POLICY_NOT_FULFILLED            => 420,
			self::MISDIRECTED_REQUEST             => 421,
			self::UNPROCESSABLE_ENTITY            => 422,
			self::LOCKED                          => 423,
			self::FAILED_DEPENDENCY               => 424,
			self::UNORDERED_COLLECTION            => 425,
			self::UPGRADE_REQUIRED                => 426,
			self::PRECONDITION_REQUIRED           => 428,
			self::TOO_MANY_REQUESTS               => 429,
			self::REQUEST_HEADER_FIELDS_TOO_LARGE => 431,
			self::UNAVAILABLE_FOR_LEGAL_REASONS   => 451
	);

	private static $serverErrorList   = array(
			self::INTERNAL_SERVER_ERROR      => 500,
			self::NOT_IMPLEMENTED            => 501,
			self::BAD_GATEWAY                => 502,
			self::SERVICE_UNAVAILABLE        => 503,
			self::GATEWAY_TIME_OUT           => 504,
			self::HTTP_VERSION_NOT_SUPPORTED => 505,
			self::VARIANT_ALSO_NEGOTIATES    => 506,
			self::INSUFFICIENT_STORAGE       => 507,
			self::LOOP_DETECTED              => 508,
			self::BANDWIDTH_LIMIT_EXCEEDED   => 509,
			self::NOT_EXTENDED               => 510,
	);

	public function getCode():int {
		$codeList = array_merge(
				self::$informationalList,
				self::$successList,
				self::$redirectionList,
				self::$clientErrorList,
				self::$serverErrorList
		);
		return $codeList[$this->get()];
	}

	public function isInformational():bool {
		return isset(self::$informationalList[$this->get()]);
	}

	public function isSuccess():bool {
		return isset(self::$successList[$this->get()]);
	}

	public function isRedirection():bool {
		return isset(self::$redirectionList[$this->get()]);
	}

	public function isClientError():bool {
		return isset(self::$clientErrorList[$this->get()]);
	}

	public function isServerError():bool {
		return isset(self::$serverErrorList[$this->get()]);
	}

}
