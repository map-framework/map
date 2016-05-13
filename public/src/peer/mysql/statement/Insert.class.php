<?php
namespace peer\mysql\statement;

use peer\mysql\Query;

/**
 * simple MySQL insert statement
 *
 * @link http://dev.mysql.com/doc/refman/5.7/en/insert.html
 * @TODO migrate to PHP 7 (#32)
 * @TODO outsource into Add-On (#33)
 */
final class Insert extends AbstractStatement {

	/**
	 * array[columnName:string]['type']  = type:string
	 * array[columnName:string]['value'] = value:string
	 *
	 * @var array (see above)
	 */
	private $valueList = array();

	/**
	 * @param  string $columnName
	 * @param  string $type
	 * @param  mixed  $value
	 * @return Insert this
	 */
	public function addValue($columnName, $type, $value) {
		$this->valueList[$columnName] = array(
				'type'  => $type,
				'value' => $value
		);
		return $this;
	}

	/**
	 * @see    Insert::$valueList
	 * @return array
	 */
	public function getValueList() {
		return $this->valueList;
	}

	/**
	 * @return Query
	 */
	public function assemble() {
		$query = new Query();
		$sql   = 'INSERT INTO '.$query->ph(Query::TYPE_TABLE, $this->getTableName());

		$columnPart = '';
		$valuePart  = '';
		foreach ($this->valueList as $columnName => $options) {
			if ($columnPart !== '' && $valuePart !== '') {
				$columnPart .= ', ';
				$valuePart .= ', ';
			}

			$columnPart .= $query->ph(Query::TYPE_COLUMN, $columnName);
			$valuePart .= $query->ph($options['type'], $options['value']);
		}
		$sql .= ' ('.$columnPart.') VALUES ('.$valuePart.')';
		return $query->setQuery($sql);
	}

}
