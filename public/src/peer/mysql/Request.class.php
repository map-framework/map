<?php
namespace peer\mysql;

use Exception;
use mysqli;
use RuntimeException;
use store\Bucket;
use store\Logger;

class Request {

	/**
	 * @var MySQLi
	 */
	protected $link = null;

	/**
	 * @var string
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
	 * execute MySQL Query
	 *
	 * @see    Request::queryList
	 * @param  Query $query
	 * @throws RuntimeException
	 * @throws Exception on failure
	 * @return Bucket|bool
	 */
	public function query(Query $query) {
		return $this->queryList(array($query), true)[0];
	}

	/**
	 * execute MySQL Query's
	 *
	 * @see    Query
	 * @param  array   $queryList
	 * @param  boolean $map
	 * @throws RuntimeException
	 * @throws Exception on failure -> rollback
	 * @return array ($map === true && possible ? array with Buckets : array with raw result)
	 */
	public function queryList($queryList, $map = false) {
		$this->link->begin_transaction();

		$rawResultList = array();
		foreach ($queryList as $query) {
			if (!($query instanceof Query)) {
				throw new RuntimeException('item in queryList is not instance of `\peer\mysql\Query`');
			}

			$this->lastQuery = $query->getQuery($this->link);
			$rawResult       = $this->link->query($this->lastQuery);

			if ($rawResult === false) {
				$this->link->rollback();
				throw new Exception('MySQL query failed: `'.$this->lastQuery.'`');
			}
			$rawResultList[] = $rawResult;
		}

		$this->link->commit();
		if ($map !== true) {
			return $rawResultList;
		}

		$bucketResultList = array();
		foreach ($rawResultList as $rawResult) {
			if ($rawResult === true) {
				$bucketResultList[] = true;
			}
			else {
				$bucket = new Bucket();
				foreach ($rawResult as $rowNumber => $valueList) {
					foreach ($valueList as $column => $value) {
						$bucket->set($rowNumber, $column, $value);
					}
				}
				$bucketResultList[] = $bucket;
			}
		}
		return $bucketResultList;
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
