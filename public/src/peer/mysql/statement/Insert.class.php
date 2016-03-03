<?php
namespace peer\mysql\statement;

use peer\mysql\Query;

/**
 * simple MySQL insert statement
 *
 * @link http://dev.mysql.com/doc/refman/5.7/en/insert.html
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
	 * @return Query
	 */
	public function assemble() {
		$query = new Query();

		$sql = 'INSERT INTO %(0)';
		$query->addPH(Query::TYPE_TABLE, $this->getTableName());

		$phNumber   = 1;
		$columnPart = '';
		$valuePart  = '';
		foreach ($this->valueList as $columnName => $options) {
			if ($phNumber !== 1) {
				$columnPart .= ', ';
				$valuePart .= ', ';
			}
			$columnPart .= '%('.$phNumber.')';
			$phNumber++;
			$query->addPH(Query::TYPE_COLUMN, $columnName);

			$valuePart .= '%('.$phNumber.')';
			$phNumber++;
			$query->addPH($options['type'], $options['value']);
		}
		$sql .= ' ('.$columnPart.') VALUES ('.$valuePart.')';
		return $query->setQuery($sql);
	}

}
