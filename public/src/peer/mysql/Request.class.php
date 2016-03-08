<?php
namespace peer\mysql;

use Exception;
use mysqli;
use store\Bucket;
use store\Logger;

class Request {

	/**
	 * @var MySQLi
	 */
	protected $link = null;

	/**
	 * @var null|string
	 */
	protected $lastQuery = null;

	/**
	 * @param  Bucket $config
	 * @throws Exception
	 */
	public function __construct(Bucket $config) {
		$this->link = new MySQLi(
				$config->get('mysql', 'hostname'),
				$config->get('mysql', 'username'),
				$config->get('mysql', 'password'),
				$config->get('mysql', 'database'),
				$config->get('mysql', 'port')
		);
		if ($this->link->connect_errno) {
			throw new Exception('connection error: '.$this->link->connect_error);
		}
	}

	/**
	 * execute MySQL query
	 *
	 * @param  Query $query
	 * @return Bucket|bool
	 */
	public function query(Query $query) {
		$this->lastQuery = $query->getQuery($this->link);
		$result = $this->link->query($this->lastQuery);
		if (is_bool($result)) {
			return $result;
		}

		$bucket = new Bucket();
		for ($i = 0; $resultLine = $result->fetch_array(); $i++) {
			foreach ($resultLine as $column => $value) {
				$bucket->set($i, $column, $value);
			}
		}
		return $bucket;
	}

	/**
	 * @see    Request::query
	 * @return null|string
	 */
	public function getLastQuery() {
		return $this->lastQuery;
	}

	public function __destruct() {
		if (!$this->link->close()) {
			Logger::warning('failed to close mysql');
		}
	}

}
