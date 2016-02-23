<?php
namespace peer\mysql\statement;

use peer\mysql\Query;

abstract class AbstractStatement {

	const OPERATOR_EQUAL         = '=';
	const OPERATOR_NOT_EQUAL     = '!=';
	const OPERATOR_LESS          = '<';
	const OPERATOR_LESS_EQUAL    = '<=';
	const OPERATOR_GREATER       = '>';
	const OPERATOR_GREATER_EQUAL = '>=';

	/**
	 * @var string
	 */
	private $tableName = null;

	/**
	 * @return Query
	 */
	abstract public function assemble();

	/**
	 * @param string $tableName
	 */
	public function __construct($tableName) {
		$this->setTableName($tableName);
	}

	/**
	 * @param  string $tableName
	 * @return AbstractStatement this
	 */
	final public function setTableName($tableName) {
		$this->tableName = $tableName;
		return $this;
	}

	/**
	 * @return string
	 */
	final public function getTableName() {
		return $this->tableName;
	}

}
