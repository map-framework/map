<?php
namespace data\net;

use data\AbstractData;
use data\InvalidDataException;
use Exception;
use exception\MAPException;
use util\Logger;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 *
 * @link      http://www.ietf.org/rfc/rfc2396.txt
 */
class Url extends AbstractData {

	const PATTERN_SCHEME   = '^[A-Za-z0-9\-+.]*$';
	const PATTERN_USER     = '^[A-Za-z0-9\-_.!~*\'();&=+$,%]*$';
	const PATTERN_PASS     = '^[A-Za-z0-9\-_.!~*\'();&=+$,%]*$';
	const PATTERN_HOST     = '^[A-Za-z0-9\-.]*$';
	const PATTERN_PATH     = '^[A-Za-z0-9\-_.!~*\'();\/%]*$';
	const PATTERN_QUERY    = '^[A-Za-z0-9\-_.!~*\'();\/:@&=+$,%]*$';
	const PATTERN_FRAGMENT = '^[A-Za-z0-9\-_.!~*\'();\/:@&=+$,%]*$';

	/**
	 * @var string
	 */
	private $scheme = '';

	/**
	 * @var string
	 */
	private $user = '';

	/**
	 * @var string
	 */
	private $pass = '';

	/**
	 * @var string
	 */
	private $host = '';

	/**
	 * @var int
	 */
	private $port = -1;

	/**
	 * @var string
	 */
	private $path = '';

	/**
	 * @var string
	 */
	private $query = '';

	/**
	 * @var string
	 */
	private $fragment = '';

	/**
	 * @throws ParseException
	 */
	final public function set(string $url) {
		$componentList = parse_url($url);
		if ($componentList === false) {
			throw new ParseException($url);
		}

		foreach ($componentList as $name => $value) {
			$method = 'set'.ucfirst($name);
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
			else {
				Logger::error(
						'Unknown URL-Component ('
						.'Component-Name: '.MAPException::export($name).'; '
						.'Component-Value: '.MAPException::export($value).'; '
						.'URL: '.MAPException::export($url).')'
				);
			}
		}
	}

	final public function get():string {
		# scheme
		if ($this->getScheme() !== '') {
			$url = $this->getScheme().'://';
		}
		else {
			if ($this->getHost() !== '') {
				$url = '//';
			}
			else {
				$url = '';
			}
		}

		# user, pass, host & port
		if ($this->getHost() !== '') {
			if ($this->getUser() !== '') {
				$url .= $this->getUser();
				if ($this->getPass() !== '') {
					$url .= ':'.$this->getPass();
				}
				$url .= '@';
			}
			$url .= $this->getHost();
			if ($this->getPort() !== -1) {
				$url .= ':'.$this->getPort();
			}
		}

		# path
		if ($this->getPath() !== '') {
			$url .= $this->getPath();
		}

		# query
		if ($this->getQuery() !== '') {
			$url .= '?'.$this->getQuery();
		}

		# fragment
		if ($this->getFragment() !== '') {
			$url .= '#'.$this->getFragment();
		}
		return $url;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setScheme(string $scheme):Url {
		self::assertIsScheme($scheme);
		$this->scheme = $scheme;
		return $this;
	}

	public function getScheme():string {
		return $this->scheme;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setUser(string $user):Url {
		self::assertIsUser($user);
		$this->user = $user;
		return $this;
	}

	public function getUser():string {
		return $this->user;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setPass(string $pass):Url {
		self::assertIsPass($pass);
		$this->pass = $pass;
		return $this;
	}

	public function getPass():string {
		return $this->pass;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setHost(string $host):Url {
		self::assertIsHost($host);
		$this->host = $host;
		return $this;
	}

	public function getHost():string {
		return $this->host;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setPort(int $port):Url {
		self::assertIsPort($port);
		$this->port = $port;
		return $this;
	}

	public function getPort():int {
		return $this->port;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setPath(string $path):Url {
		self::assertIsPath($path);
		$this->path = $path;
		return $this;
	}

	public function getPath():string {
		return $this->path;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setQuery(string $query):Url {
		self::assertIsQuery($query);
		$this->query = $query;
		return $this;
	}

	public function getQuery():string {
		return $this->query;
	}

	/**
	 * @throws InvalidDataException
	 */
	public function setFragment(string $fragment):Url {
		self::assertIsFragment($fragment);
		$this->fragment = $fragment;
		return $this;
	}

	public function getFragment():string {
		return $this->fragment;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsScheme(string $scheme) {
		self::assertIsMatching(self::PATTERN_SCHEME, $scheme);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsUser(string $user) {
		self::assertIsMatching(self::PATTERN_USER, $user);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsPass(string $pass) {
		self::assertIsMatching(self::PATTERN_PASS, $pass);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsHost(string $host) {
		self::assertIsMatching(self::PATTERN_HOST, $host);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsPort(int $port) {
		if ($port < -1 || $port > 65535) {
			throw new InvalidDataException('0 - 65535 || -1', $port);
		}
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsPath(string $path) {
		self::assertIsMatching(self::PATTERN_PATH, $path);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsQuery(string $query) {
		self::assertIsMatching(self::PATTERN_QUERY, $query);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsFragment(string $fragment) {
		self::assertIsMatching(self::PATTERN_FRAGMENT, $fragment);
	}

}
