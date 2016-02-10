<?php
namespace store\data\net;

use store\data\AbstractData;
use Exception;

/**
 * @link http://www.ietf.org/rfc/rfc2396.txt
 * @todo #3 correct patterns
 */
class Url extends AbstractData {

	const PATTERN_SCHEME   = '^[0-9a-zA-Z+-.]+$';
	const PATTERN_USER     = '^[0-9a-zA-Z-_.~%]+$';
	const PATTERN_PASS     = '^[0-9a-zA-Z-_.~%]+$';
	const PATTERN_HOST     = '^[0-9a-zA-Z-.]+$';
	const PATTERN_PATH     = '^[0-9a-zA-Z-_.!~*():@&=+$,%\/;]*$';
	const PATTERN_QUERY    = '^[0-9a-zA-Z-_.!~*();\/:@&=+$,]+$';
	const PATTERN_FRAGMENT = '^[0-9a-zA-Z-_.~%]+$';

	/**
	 * @var string|null
	 */
	private $scheme = null;

	/**
	 * @var string|null
	 */
	private $user = null;

	/**
	 * @var string|null
	 */
	private $pass = null;

	/**
	 * @var string|null
	 */
	private $host = null;

	/**
	 * @var string|null
	 */
	private $port = null;

	/**
	 * @var string|null
	 */
	private $path = null;

	/**
	 * @var string|null
	 */
	private $query = null;

	/**
	 * @var string|null
	 */
	private $fragment = null;

	/**
	 * @param $url string
	 */
	public function __construct($url = '') {
		parent::__construct($url);
	}

	/**
	 * @see    AbstractData::set
	 * @param  string $url
	 * @throws Exception if url is malformed
	 * @return Url
	 */
	final public function set($url = '') {
		$parsedUrl = parse_url($url);
		if ($parsedUrl === false) {
			throw new Exception('URL `'.$url.'` is malformed');
		}

		foreach ($parsedUrl as $component => $componentValue) {
			$setMethod = 'set'.ucfirst($component);
			if (method_exists($this, $setMethod)) {
				$this->$setMethod($componentValue);
			}
		}
		return $this;
	}

	/**
	 * @return string
	 */
	final public function get() {
		# scheme
		if ($this->getScheme() !== null) {
			$url = $this->getScheme().'://';
		}
		elseif ($this->getHost() !== null) {
			$url = '//';
		}
		else {
			$url = '';
		}

		# user & pass & host & port
		if ($this->getHost() !== null) {
			if ($this->getUser() !== null) {
				$url .= $this->getUser();
				if ($this->getPass() !== null) {
					$url .= ':'.$this->getPass();
				}
				$url .= '@';
			}
			$url .= $this->getHost();
			if ($this->getPort() !== null) {
				$url .= ':'.$this->getPort();
			}
		}

		# path, query & fragment
		if ($this->getPath() !== null) {
			$url .= $this->getPath();
		}

		# query
		if ($this->getQuery() !== null) {
			$url .= '?'.$this->getQuery();
		}

		# fragment
		if ($this->getFragment() !== null) {
			$url .= '#'.$this->getFragment();
		}
		return $url;
	}

	/**
	 * @param  string $scheme
	 * @throws Exception if scheme invalid
	 * @return Url
	 */
	public function setScheme($scheme) {
		if ($scheme !== null && !self::match(self::PATTERN_SCHEME, $scheme)) {
			throw new Exception('scheme `'.$scheme.'` is invalid');
		}
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * @param  string $user
	 * @throws Exception if user invalid
	 * @return Url
	 */
	public function setUser($user) {
		if ($user !== null && !self::match(self::PATTERN_USER, $user)) {
			throw new Exception('user `'.$user.'` is invalid');
		}
		$this->user = $user;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @param  string $pass
	 * @throws Exception if pass invalid
	 * @return Url
	 */
	public function setPass($pass) {
		if ($pass !== null && !self::match(self::PATTERN_PASS, $pass)) {
			throw new Exception('pass `'.$pass.'` is invalid');
		}
		$this->pass = $pass;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPass() {
		return $this->pass;
	}

	/**
	 * @param  string $host
	 * @throws Exception if host invalid
	 * @return Url
	 */
	public function setHost($host) {
		if ($host !== null && !self::match(self::PATTERN_HOST, $host)) {
			throw new Exception('host `'.$host.'` is invalid');
		}
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param  int $port
	 * @throws Exception if port invalid
	 * @return Url
	 */
	public function setPort($port) {
		if ($port !== null && !is_int($port) || $port < 0 || $port > 65535) {
			throw new Exception('port `'.$port.'` is invalid');
		}
		$this->port = $port;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param  string $path
	 * @throws Exception if path invalid
	 * @return Url
	 */
	public function setPath($path) {
		if ($path !== null && !self::match(self::PATTERN_PATH, $path)) {
			throw new Exception('path `'.$path.'` is invalid');
		}
		$this->path = $path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param  string $query
	 * @throws Exception if query invalid
	 * @return Url
	 */
	public function setQuery($query) {
		if ($query !== null && !self::match(self::PATTERN_QUERY, $query)) {
			throw new Exception('query `'.$query.'` is invalid');
		}
		$this->query = $query;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @param  string $fragment
	 * @throws Exception if fragment invalid
	 * @return Url
	 */
	public function setFragment($fragment) {
		if ($fragment !== null && !self::match(self::PATTERN_FRAGMENT, $fragment)) {
			throw new Exception('fragment `'.$fragment.'` is invalid');
		}
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

}