<?php
namespace store\data\net;

use store\data\AbstractData;
use Exception;

/**
 * @link http://www.ietf.org/rfc/rfc2396.txt
 * @todo #3 correct patterns
 */
class Url extends AbstractData {

	const PATTERN_SCHEME		= '^[0-9a-zA-Z+-.]+$';
	const PATTERN_USER			= '^[0-9a-zA-Z-_.~%]+$';
	const PATTERN_PASS			= '^[0-9a-zA-Z-_.~%]+$';
	const PATTERN_HOST			= '^[0-9a-zA-Z-.]+$';
	const PATTERN_PATH			= '^[0-9a-zA-Z-_.!~*():@&=+$,%\/;]*$';
	const PATTERN_QUERY			= '^[0-9a-zA-Z-_.!~*();\/:@&=+$,]+$';
	const PATTERN_FRAGMENT	= '^[0-9a-zA-Z-_.~%]+$';

	private $scheme 				= null;
	private $user						= null;
	private $pass						= null;
	private $host						= null;
	private $port 					= null;
	private $path						= null;
	private $query					= null;
	private $fragment				= null;

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
		$this->data = $url;
		return $this;
	}

	/**
	 * set scheme
	 * @param  string scheme
	 * @throws Exception if scheme not valid
	 * @return Url
	 */
	public function setScheme($scheme) {
		if (!self::match(self::PATTERN_SCHEME, $scheme)) {
			throw new Exception('scheme `'.$scheme.'` is not valid');
		}
		$this->scheme = $scheme;
		return $this->updateData();
	}

	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * set user
	 * @param  string user
	 * @throws Exception if user not valid
	 * @return Url
	 */
	public function setUser($user) {
		if (!self::match(self::PATTERN_USER, $user)) {
			throw new Exception('user `'.$user.'` is not valid');
		}
		$this->user = $user;
		return $this->updateData();
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * set pass
	 * @param  string pass
	 * @throws Exception if pass not valid
	 * @return Url
	 */
	public function setPass($pass) {
		if (!self::match(self::PATTERN_PASS, $pass)) {
			throw new Exception('pass `'.$pass.'` is not valid');
		}
		$this->pass = $pass;
		return $this->updateData();
	}

	/**
	 * @return string
	 */
	public function getPass() {
		return $this->pass;
	}

	/**
	 * set host
	 * @param  string host
	 * @throws Exception if host not valid
	 * @return Url
	 */
	public function setHost($host) {
		if (!self::match(self::PATTERN_HOST, $host)) {
			throw new Exception('host `'.$host.'` is not valid');
		}
		$this->host = $host;
		return $this->updateData();
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * set port
	 * @param  int port
	 * @throws Exception if port not valid
	 * @return Url
	 */
	public function setPort($port) {
		if (!is_int($port) || $port < 0 || $port > 65535) {
			throw new Exception('port `'.$port.'` is not valid');
		}
		$this->port = $port;
		return $this->updateData();
	}

	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * set path
	 * @param  string path
	 * @throws Exception if path not valid
	 * @return Url
	 */
	public function setPath($path) {
		if (!self::match(self::PATTERN_PATH, $path)) {
			throw new Exception('path `'.$path.'` is not valid');
		}
		$this->path = $path;
		return $this->updateData();
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * set query
	 * @param  string query
	 * @throws Exception if query not valid
	 * @return Url
	 */
	public function setQuery($query) {
		if (!self::match(self::PATTERN_QUERY, $query)) {
			throw new Exception('query `'.$query.'` is not valid');
		}
		$this->query = $query;
		return $this->updateData();
	}

	/**
	 * @return string
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * set fragment
	 * @param  string fragment
	 * @throws Exception if fragment not valid
	 * @return Url
	 */
	public function setFragment($fragment) {
		if (!self::match(self::PATTERN_FRAGMENT, $fragment)) {
			throw new Exception('fragment `'.$fragment.'` is not valid');
		}
		$this->fragment = $fragment;
		return $this->updateData();
	}

	/**
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * @return Url
	 */
	final private function updateData() {
		# scheme
		if ($this->getScheme() !== null) {
			$url = $this->getScheme().'://';
		}
		else {
			$url = '';
		}

		# user & pass
		if ($this->getUser() !== null) {
			$url .= $this->getUser();
			if ($this->getPass() !== null) {
				$url .= ':'.$this->getPass();
			}
			$url .= '@';
		}

		# host & port
		if ($this->getHost() !== null) {
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
		return parent::set($url);
	}

}