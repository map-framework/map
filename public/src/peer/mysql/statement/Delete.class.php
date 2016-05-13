<?php
namespace peer\mysql\statement;

use peer\mysql\Query;

/**
 * simple MySQL delete statement
 *
 * @link http://dev.mysql.com/doc/refman/5.7/en/delete.html
 * @TODO migrate to PHP 7 (#32)
 * @TODO outsource into Add-On (#33)
 */
final class Delete extends AbstractStatement {

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
	 * @var int
	 */
	private $limit = 0;

	/**
	 * @see    AbstractStatement (OPERATOR_* constants)
	 * @see    Query (TYPE_* constants)
	 * @param  string $columnName
	 * @param  string $type
	 * @param  mixed  $value
	 * @param  string $operator
	 * @return Delete this
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
	 * @param  int $limit
	 * @return Delete this
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @see    Delete::$conditionList
	 * @return array
	 */
	public function getConditionList() {
		return $this->conditionList;
	}

	/**
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @return Query
	 */
	public function assemble() {
		$query = new Query();

		$sql = 'DELETE FROM '.$query->ph(Query::TYPE_TABLE, $this->getTableName());

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

		if ($this->getLimit() >= 1) {
			$sql .= ' LIMIT '.$query->ph(Query::TYPE_INT, $this->getLimit());
		}

		return $query->setQuery($sql);
	}

}