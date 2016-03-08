<?php
namespace peer\mysql;

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
	 * array[columnName:string]['type']        = type:string
	 * array[columnName:string]['null']        = null:bool
	 * array[columnName:string]['default']     = default:bool
	 *
	 * @see Query (TYPE_* constants)
	 * @var array (see above)
	 */
	private $columnList = array();

	/**
	 * @var null|string
	 */
	private $idColumn = null;

	/**
	 * @var Bucket
	 */
	private $content = null;

	/**
	 * @var int
	 */
	private $pointer = 0;

	/**
	 * 1. call AbstractTable::setTableName, if: Class-Name <> Table-Name
	 * 2. call AbstractTable::setIdColumn
	 * 3. call AbstractTable::addColumn, for each column (expect id column)
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
		if ($this->idColumn === null) {
			throw new RuntimeException('required to call: AbstractTable::setIdColumn');
		}
		if (!isset($this->columnList[$this->idColumn])) {
			throw new RuntimeException('ID column `'.$this->idColumn.'` not exists');
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
	 * set AUTO_INCREMENT column (id)
	 * type:     table set-up
	 * required: true
	 *
	 * @param  string $name
	 * @return AbstractTable this
	 */
	final protected function setIdColumn($name) {
		$this->idColumn = $name;
		return $this->addColumn($name, Query::TYPE_INT, false, true);
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
	 * @TODO revise
	 * @param  string   $columnName
	 * @param  mixed    $value
	 * @param  int|null $pointer default AbstractTable::getPointer()
	 * @throws RuntimeException
	 * @return AbstractTable this
	 */
	final public function set($columnName, $value, $pointer = null) {
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
	 * @TODO revise
	 * @param  string   $columnName
	 * @param  int|null $pointer default AbstractTable::getPointer()
	 * @return mixed
	 */
	final public function get($columnName, $pointer = null) {
		if ($pointer === null) {
			$pointer = $this->pointer;
		}
		return $this->content->get($pointer, $columnName);
	}

	/**
	 * @TODO revise
	 * make MySQL SELECT
	 *
	 * @param  string   $columnName
	 * @param  mixed    $value
	 * @param  int|null $limit
	 * @throws RuntimeException
	 * @throws Exception
	 * @return AbstractTable this
	 */
	final public function fillBy($columnName, $value, $limit = null) {
		$this->clear();

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
			throw new Exception('request failed');
		}

		$this->content  = $result;
		$this->isFilled = true;
		return $this;
	}

	/**
	 * @TODO revise
	 * make MySQL UPDATE or INSERT
	 *
	 * @return bool
	 */
	final public function save() {
		if ($this->isFilled) {
			return $this->update();
		}
		else {
			return $this->insert();
		}
	}

	/**
	 * @TODO revise
	 * make MySQL INSERT
	 *
	 * @throws RuntimeException
	 * @return bool
	 */
	final private function insert() {
		$request = new Request($this->config);
		$success = true;

		for ($i = 0; $i < $this->content->getGroupCount(); $i++) {
			$insert = new Insert($this->getTableName());

			foreach ($this->columnList as $columnName => $options) {
				if ($this->content->isNull($i, $columnName)) {
					if ($options['nullAllowed'] === false) {
						throw new RuntimeException('column `'.$columnName.'` is null (pointer: `'.$i.'`)');
					}
				}
				else {
					$insert->addValue($columnName, $options['type'], $this->content->get($i, $columnName));
				}
			}

			if ($request->query($insert->assemble()) === false) {
				$success = false;
			}
		}
		return $success;
	}

	/**
	 * @TODO revise
	 * make MySQL UPDATE
	 *
	 * @return bool
	 */
	final private function update() {
		$request = new Request($this->config);
		$success = true;

		for ($i = 0; $i < $this->content->getGroupCount(); $i++) {
			$update = new Update($this->getTableName());

			foreach ($this->columnList as $columnName => $options) {
				if ($this->content->isNull($i, $columnName) && $options['nullAllowed'] === false) {
					throw new RuntimeException('column `'.$columnName.'` is null (pointer: `'.$i.'`)');
				}
				$update->addAssignment($columnName, $options['type'], $this->content->get($i, $columnName));
			}
			$update->setLimit(1);
		}
	}

	/**
	 * @TODO revise
	 * make MySQL DELETE
	 *
	 * @return bool
	 */
	final public function delete() {
		# @TODO make MySQL DELETE
	}

	/**
	 * @TODO revise
	 * @param string $name
	 * @param mixed  $value
	 */
	final public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * @TODO revise
	 * @param  string $name
	 * @return mixed
	 */
	final public function __get($name) {
		return $this->get($name);
	}

}
