<?php
namespace peer\mysql;

use DateTime;
use Exception;
use peer\mysql\statement\Insert;
use peer\mysql\statement\Select;
use peer\mysql\statement\Update;
use ReflectionClass;
use RuntimeException;
use store\Bucket;

abstract class AbstractTable {

	/**
	 * @var Bucket
	 */
	protected $config = null;

	/**
	 * custom table name
	 *
	 * @var string
	 */
	private $tableName = null;

	/**
	 * array[columnName:string]['type']    = type:string
	 * array[columnName:string]['null']    = null:bool
	 * array[columnName:string]['default'] = default:bool
	 *
	 * @see Query (TYPE_* constants)
	 * @var array (see above)
	 */
	private $columnList = array();

	/**
	 * @var string
	 */
	private $primaryKey = null;

	/**
	 * @var Bucket
	 */
	private $content = null;

	/**
	 * @var int
	 */
	private $pointer = 0;

	/**
	 * @var bool
	 */
	private $isReadOnly = false;

	/**
	 * 1. call AbstractTable::setTableName, if: Class-Name <> Table-Name
	 * 2. call AbstractTable::addColumn, for each column (expect id column)
	 * 3. call AbstractTable::setPrimaryKey
	 *
	 * @return void
	 */
	abstract protected function init();

	/**
	 * @throws RuntimeException
	 * @param  Bucket $config
	 */
	final public function __construct(Bucket $config) {
		$this->config  = $config;
		$this->content = new Bucket();

		$this->init();

		if (count($this->columnList) === 0) {
			throw new RuntimeException('required to call: AbstractTable::addColumn');
		}
		if ($this->primaryKey === null) {
			throw new RuntimeException('required one primary key');
		}
		if (!isset($this->columnList[$this->primaryKey])) {
			throw new RuntimeException('primary key column `'.$this->primaryKey.'` not exists');
		}
	}

	/**
	 * set custom table name
	 * type:     table set-up
	 * required: false
	 *
	 * @param  string $tableName
	 * @return AbstractTable this
	 */
	final protected function setTableName($tableName) {
		$this->tableName = $tableName;
		return $this;
	}

	/**
	 * add column
	 * type:     table set-up
	 * required: true
	 *
	 * @param  string $name
	 * @param  string $type
	 * @param  bool   $null
	 * @param  bool   $default
	 * @throws RuntimeException
	 * @return AbstractTable this
	 */
	final protected function addColumn($name, $type, $null = true, $default = false) {
		if (isset($this->columnList[$name])) {
			throw new RuntimeException('column `'.$name.'` already configured');
		}

		$this->columnList[$name] = array(
				'type'    => (string) $type,
				'null'    => (bool) $null,
				'default' => (bool) $default
		);
		return $this;
	}

	/**
	 * set primary key
	 * type:     table set-up
	 * required: true
	 *
	 * @param  string $columnName
	 * @return AbstractTable this
	 */
	final protected function setPrimaryKey($columnName) {
		$this->primaryKey = $columnName;
		return $this;
	}

	/**
	 * @return string
	 */
	final public function getTableName() {
		if ($this->tableName !== null) {
			return $this->tableName;
		}
		return lcfirst((new ReflectionClass($this))->getShortName());
	}

	/**
	 * length of rows
	 *
	 * @return int
	 */
	final public function getLength() {
		return $this->content->getGroupCount();
	}

	/**
	 * set pointer to row
	 *
	 * @param  int $pointer
	 * @return AbstractTable this
	 */
	final public function setPointer($pointer) {
		$this->pointer = $pointer;
		return $this;
	}

	/**
	 * @return int
	 */
	final public function getPointer() {
		return $this->pointer;
	}

	/**
	 * do {
	 *   echo $person->name;
	 * }
	 * while ($person->next());
	 *
	 * @example (see above)
	 * @return  bool
	 */
	final public function next() {
		$newPointer = $this->getPointer() + 1;
		if ($this->content->getGroupCount() <= $newPointer) {
			return false;
		}
		$this->setPointer($newPointer);
		return true;
	}

	/**
	 * @return bool
	 */
	final public function isFilled() {
		return !$this->content->isNull(0, $this->primaryKey);
	}

	/**
	 * @see    AbstractTable::$isReadOnly
	 * @return bool
	 */
	final public function isReadOnly() {
		return $this->isReadOnly;
	}

	/**
	 * @return AbstractTable this
	 */
	final public function toReadOnly() {
		$this->isReadOnly = true;
		return $this;
	}

	/**
	 * @see    AbstractTable::getPointer
	 * @param  string   $columnName
	 * @param  mixed    $value
	 * @param  int|null $pointer (default getPointer)
	 * @throws RuntimeException
	 * @return AbstractTable this
	 */
	final public function set($columnName, $value, $pointer = null) {
		if ($this->isReadOnly()) {
			throw new RuntimeException('this object is read only');
		}

		if (!isset($this->columnList[$columnName])) {
			throw new RuntimeException('column `'.$columnName.'` not exists');
		}
		if ($pointer === null) {
			$pointer = $this->getPointer();
		}
		$this->content->set($pointer, $columnName, $value);
		return $this;
	}

	/**
	 * @see    AbstractTable::getPointer
	 * @param  string   $columnName
	 * @param  int|null $pointer (default getPointer)
	 * @return mixed
	 */
	final public function get($columnName, $pointer = null) {
		if ($pointer === null) {
			$pointer = $this->pointer;
		}
		return $this->content->get($pointer, $columnName);
	}

	/**
	 * make MySQL SELECT
	 *
	 * @see    Select
	 * @param  string   $columnName
	 * @param  mixed    $value
	 * @param  int|null $limit
	 * @throws RuntimeException
	 * @throws Exception
	 * @return AbstractTable this
	 */
	final public function fillBy($columnName, $value, $limit = null) {
		if ($this->getLength() !== 0 || $this->isReadOnly()) {
			throw new RuntimeException('this object is already filled or readonly');
		}
		if (!isset($this->columnList[$columnName])) {
			throw new RuntimeException('column `'.$columnName.'` not exists');
		}

		$select = (new Select($this->getTableName()))
				->addCondition($columnName, $this->columnList[$columnName]['type'], $value);

		foreach ($this->columnList as $column => $settings) {
			$select->addExpression($column);
		}

		if ($limit !== null) {
			$select->setLimit($limit);
		}

		$request = new Request($this->config);
		$result  = $request->query($select->assemble());
		if ($result === false) {
			throw new Exception('MySQL request failed: `'.$request->getLastQuery().'`');
		}

		# convert Date columns into `\DateTime`
		for ($i = 0; $i < $result->getGroupCount(); $i++) {
			foreach ($this->columnList as $column => $settings) {
				if (in_array($settings['type'], Query::LIST_DATE) && !$result->isNull($i, $column)) {
					$result->set($i, $column, new DateTime($result->get($i, $column)));
				}
			}
		}

		$this->content = $result;
		return $this;
	}

	/**
	 * make MySQL UPDATE or INSERT
	 *
	 * @see    AbstractTable::update
	 * @see    AbstractTable::insert
	 * @throws RuntimeException
	 * @return bool
	 */
	final public function save() {
		if ($this->isReadOnly()) {
			throw new RuntimeException('can\'t save read only object');
		}

		if ($this->isFilled()) {
			return $this->update();
		}
		else {
			return $this->insert();
		}
	}

	/**
	 * make MySQL UPDATE
	 *
	 * @see    Update
	 * @throws RuntimeException
	 * @return bool
	 */
	final private function update() {
		$request = new Request($this->config);

		$queryList = array();
		for ($i = 0; $i < $this->getLength(); $i++) {

			$update = (new Update($this->getTableName()))
					->addCondition(
							$this->primaryKey,
							$this->columnList[$this->primaryKey]['type'],
							$this->content->get($i, $this->primaryKey)
					)
					->setLimit(1);

			foreach ($this->columnList as $column => $settings) {
				if ($this->content->isNull($i, $column) && $settings['null'] === false) {
					throw new RuntimeException('column `'.$column.'` is NOT NULL');
				}
				$update->addAssignment($column, $settings['type'], $this->content->get($i, $column));
			}
			$queryList[] = $update->assemble();
		}
		try {
			$request->queryList($queryList);
		}
		catch (Exception $e) {
			$this->toReadOnly();
			return false;
		}
		return true;
	}

	/**
	 * make MySQL INSERT
	 *
	 * @see    Insert
	 * @throws RuntimeException
	 * @return bool
	 */
	final private function insert() {
		$this->toReadOnly();
		$request = new Request($this->config);

		$queryList = array();
		for ($i = 0; $i < $this->getLength(); $i++) {
			$insert = new Insert($this->getTableName());

			foreach ($this->columnList as $column => $settings) {
				if ($this->content->isNull($i, $column)) {
					if ($settings['null'] === false && $settings['default'] === false) {
						throw new RuntimeException('column `'.$column.'` is null (not null & no default)');
					}
				}
				else {
					$insert->addValue($column, $settings['type'], $this->content->get($i, $column));
				}
			}
			$queryList[] = $insert->assemble();
		}

		try {
			$request->queryList($queryList);
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * make MySQL DELETE
	 *
	 * @return bool
	 */
	final public function delete() {
		# @TODO make MySQL DELETE
		# @TODO make this object read only
	}

	/**
	 * @see   AbstractTable::set
	 * @param string $name
	 * @param mixed  $value
	 */
	final public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * @see     AbstractTable::get
	 * @param  string $name
	 * @return mixed
	 */
	final public function __get($name) {
		return $this->get($name);
	}

}
