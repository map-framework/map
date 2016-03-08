<?php
namespace peer\mysql\statement;

use peer\mysql\Query;
use RuntimeException;

/**
 * simple mysql update statement
 *
 * @link http://dev.mysql.com/doc/refman/5.7/en/update.html
 */
final class Update extends AbstractStatement {

	/**
	 * array[columnName:string]['type']  = type:string
	 * array[columnName:string]['value'] = value:mixed
	 *
	 * @see Query (TYPE_* constants)
	 * @var array (see above)
	 */
	private $assignmentList = array();

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
	 * @see    Query (TYPE_* constants)
	 * @param  string $columnName
	 * @param  string $type
	 * @param  mixed  $value
	 * @return Update this
	 */
	public function addAssignment($columnName, $type, $value) {
		$this->assignmentList[$columnName] = array(
				'type'  => $type,
				'value' => $value
		);
		return $this;
	}

	/**
	 * @see    AbstractStatement (OPERATOR_* constants)
	 * @see    Query (TYPE_* constants)
	 * @param  string $columnName
	 * @param  string $type
	 * @param  mixed  $value
	 * @param  string $operator
	 * @return Update this
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
	 * @return Update this
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @see    Update::$assignmentList
	 * @return array
	 */
	public function getAssignmentList() {
		return $this->assignmentList;
	}

	/**
	 * @see    Update::$conditionList
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

		$sql = 'UPDATE '.$query->ph(Query::TYPE_TABLE, $this->getTableName()).' SET';

		if (count($this->getAssignmentList()) === 0) {
			throw new RuntimeException('assignmentList is empty');
		}
		foreach ($this->getAssignmentList() as $columnName => $options) {
			if (isset($notFirst)) {
				$sql .= ',';
			}
			$notFirst = true;
			$sql .= ' '.$query->ph(Query::TYPE_COLUMN, $columnName);
			$sql .= '=';
			$sql .= $query->ph($options['type'], $options['value']);
		}

		if (count($this->getConditionList())) {
			$sql .= ' WHERE';
			foreach ($this->getConditionList() as $nr => $condition) {
				if ($nr !== 0) {
					$sql .= ' &&';
				}
				$sql .= ' '.$query->ph(Query::TYPE_COLUMN, $condition['columnName']);
				$sql .= ' '.$condition['operator'].' ';
				$sql .= $query->ph($condition['type'], $condition['value']);
			}
		}

		if ($this->getLimit() >= 1) {
			$sql .= ' LIMIT '.$query->ph(Query::TYPE_INT, $this->getLimit());
		}
		return $query->setQuery($sql);
	}

}
