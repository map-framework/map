<?php
namespace peer\mysql;

use Exception;
use mysqli;
use RuntimeException;
use util\Bucket;
use util\Logger;

class Request {

	const DEFAULT_ACCESS_POINT = 'local';

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
	 * @param  string $accessPoint
	 * @throws RuntimeException
	 * @throws Exception
	 */
	public function __construct(Bucket $config, $accessPoint = self::DEFAULT_ACCESS_POINT) {
		if (!$config->isArray('mysql', $accessPoint)) {
			throw new RuntimeException('unknown access point `'.$accessPoint.'`');
		}

		$accessData = $config->get('mysql', $accessPoint);

		$this->link = new MySQLi(
				isset($accessData['hostname']) ? $accessData['hostname'] : null,
				isset($accessData['username']) ? $accessData['username'] : null,
				isset($accessData['password']) ? $accessData['password'] : null,
				isset($accessData['database']) ? $accessData['database'] : null,
				isset($accessData['port']) ? $accessData['port'] : null
		);
		if ($this->link->connect_errno) {
			throw new Exception('MySQL Connection-Error: '.$this->link->connect_error);
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
