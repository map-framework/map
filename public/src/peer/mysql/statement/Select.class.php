<?php
namespace peer\mysql\statement;

use peer\mysql\Query;

/**
 * simple mysql select statement
 *
 * @link http://dev.mysql.com/doc/refman/5.7/en/select.html
 */
final class Select extends AbstractStatement {

	/**
	 * @var bool
	 */
	private $distinct = false;

	/**
	 * @var array { int => string }
	 */
	private $expressionList = array();

	/**
	 * array[int]['columnName'] = columnName:string
	 * array[int]['type']       = type:string
	 * array[int]['value']      = value:mixed
	 * array[int]['operator']   = operator:string
	 *
	 * @see AbstractStatement (OPERATOR_* constants)
	 * @see Query (TYPE_* constants)
	 * @var array (see above)
	 */
	private $conditionList = array();

	/**
	 * @var array { int => array { 'columnName' => string, 'desc' => bool } }
	 */
	private $orderByList = array();

	/**
	 * @var int
	 */
	private $limit = 0;

	/**
	 * required Select::$limit > 0
	 *
	 * @var int
	 */
	private $offset = 0;

	/**
	 * @param  bool $distinct
	 * @return Select this
	 */
	public function setDistinct($distinct = true) {
		$this->distinct = (bool) $distinct;
		return $this;
	}

	/**
	 * @param  string $columnName
	 * @return Select this
	 */
	public function addExpression($columnName) {
		$this->expressionList[] = $columnName;
		return $this;
	}

	/**
	 * @see    AbstractStatement (OPERATOR_* constants)
	 * @see    Query (TYPE_* constants)
	 * @param  string $columnName
	 * @param  string $type
	 * @param  mixed  $value
	 * @param  string $operator
	 * @return Select this
	 */
	public function addCondition($columnName, $type, $value, $operator = self::OPERATOR_EQUAL) {
		$this->conditionList[] = array(
				'columnName' => $columnName,
				'type'       => $type,
				'value'      => $value,
				'operator'   => $operator
		);
		return $this;
	}

	/**
	 * @param  string $columnName
	 * @param  bool   $desc
	 * @return Select this
	 */
	public function addOrderBy($columnName, $desc = false) {
		$this->orderByList[] = array(
				'columnName' => $columnName,
				'desc'       => (bool) $desc
		);
		return $this;
	}

	/**
	 * @param  int $limit
	 * @return Select this
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @param  int $offset
	 * @return Select this
	 */
	public function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDistinctEnabled() {
		return $this->distinct;
	}

	/**
	 * @see    Select::$expressionList
	 * @return array
	 */
	public function getExpressionList() {
		return $this->expressionList;
	}

	/**
	 * @see    Select::$conditionList
	 * @return array
	 */
	public function getConditionList() {
		return $this->conditionList;
	}

	/**
	 * @see    Select::$orderByList
	 * @return array
	 */
	public function getOrderByList() {
		return $this->orderByList;
	}

	/**
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * @return Query
	 */
	public function assemble() {
		$query = new Query();
		$sql   = 'SELECT';

		if ($this->isDistinctEnabled()) {
			$sql .= ' DISTINCT';
		}

		if (count($this->getExpressionList())) {
			foreach ($this->getExpressionList() as $nr => $expression) {
				if ($nr !== 0) {
					$sql .= ',';
				}
				$sql .= $query->ph(Query::TYPE_COLUMN, $expression);
			}
		}
		else {
			$sql .= ' *';
		}

		$sql .= ' FROM '.$query->ph(Query::TYPE_TABLE, $this->getTableName());

		if (count($this->getConditionList())) {
			$sql .= ' WHERE';
			foreach ($this->getConditionList() as $nr => $condition) {
				if ($nr !== 0) {
					$sql .= ' &&';
				}

				$sql .= ' '.$query->ph(Query::TYPE_COLUMN, $condition['columnName']);
				$sql .= ' '.$condition['operator'];
				$sql .= ' '.$query->ph($condition['type'], $condition['value']);
			}
		}

		if (count($this->getOrderByList())) {
			$sql .= ' ORDER BY';
			foreach ($this->getOrderByList() as $nr => $orderBy) {
				if ($nr !== 0) {
					$sql .= ',';
				}

				$sql .= ' '.$query->ph(Query::TYPE_COLUMN, $orderBy['columnName']);
				if ($orderBy['desc'] === true) {
					$sql .= ' DESC';
				}
			}
		}

		if ($this->getLimit() >= 1) {
			$sql .= ' LIMIT '.$query->ph(Query::TYPE_INT, $this->getLimit());

			if ($this->getOffset() >= 1) {
				$sql .= ' OFFSET '.$query->ph(Query::TYPE_INT, $this->getOffset());
			}
		}
		return $query->setQuery($sql);
	}

}

