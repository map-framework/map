<?php
namespace peer\mysql;

/**
 * @TODO migrate to PHP 7 (#32)
 * @TODO outsource into Add-On (#33)
 */
use DateTime;
use Exception;
use peer\mysql\statement\Delete;
use peer\mysql\statement\Insert;
use peer\mysql\statement\Select;
use peer\mysql\statement\Update;
use ReflectionClass;
use RuntimeException;
use util\Bucket;
use util\Logger;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
abstract class AbstractTable {

	/**
	 * @var Bucket
	 */
	protected $config;

	/**
	 * @var string
	 */
	private $accessPoint = Request::DEFAULT_ACCESS_POINT;

	/**
	 * custom table name
	 *
	 * @var string
	 */
	private $tableName;

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
	private $primaryKey;

	/**
	 * @var Bucket
	 */
	private $content;

	/**
	 * @var int
	 */
	private $pointer = 0;

	/**
	 * @var bool
	 */
	private $isReadOnly = false;

	/**
	 * 1. call AbstractTable::setAccessPoint, if not default (Request::$DEFAULT_ACCESS_POINT)
	 * 2. call AbstractTable::setTableName, if: Class-Name <> Table-Name
	 * 3. call AbstractTable::addColumn, for each column
	 * 4. call AbstractTable::setPrimaryKey
	 */
	abstract protected function init();

	final public function __construct(Bucket $config) {
		$this->config  = $config;
		$this->content = new Bucket();

		$this->init();

		if (!count($this->columnList)) {
			throw new RuntimeException('required to call: AbstractTable::addColumn');
		}
		if ($this->primaryKey === null) {
			throw new RuntimeException('required to call: AbstractTable::setPrimaryKey');
		}
		if (!isset($this->columnList[$this->primaryKey])) {
			throw new RuntimeException('column `'.$this->primaryKey.'` not exists');
		}
	}

	final protected function setAccessPoint(string $accessPoint):AbstractTable {
		$this->accessPoint = $accessPoint;
		return $this;
	}

	final protected function setTableName(string $tableName):AbstractTable {
		$this->tableName = $tableName;
		return $this;
	}

	final protected function addColumn(
			string $name,
			string $type,
			bool $null = true,
			bool $default = false
	):AbstractTable {
		$this->columnList[$name] = array(
				'type'    => $type,
				'null'    => $null,
				'default' => $default
		);
		return $this;
	}

	final protected function setPrimaryKey(string $columnName):AbstractTable {
		$this->primaryKey = $columnName;
		return $this;
	}

	final public function getTableName():string {
		if ($this->tableName !== null) {
			return $this->tableName;
		}
		return lcfirst((new ReflectionClass($this))->getShortName());
	}

	/**
	 * get number of rows
	 */
	final public function getLength():int {
		return $this->content->getGroupCount();
	}

	/**
	 * set row-pointer
	 */
	final public function setPointer(int $pointer):AbstractTable {
		$this->pointer = $pointer;
		return $this;
	}

	/**
	 * get row-pointer
	 */
	final public function getPointer():int {
		return $this->pointer;
	}

	final public function next():bool {
		$newPointer = $this->getPointer() + 1;
		if ($this->getLength() <= $newPointer) {
			return false;
		}
		$this->setPointer($newPointer);
		return true;
	}

	final public function isReadOnly():bool {
		return $this->isReadOnly;
	}

	final public function toReadOnly():AbstractTable {
		$this->isReadOnly = true;
		return $this;
	}

	final public function isContentFromDB():bool {
		return !$this->content->isNull(0, $this->primaryKey);
	}

	final public function toBucket():Bucket {
		return clone $this->content;
	}

	final public function set(string $columnName, $value, int $pointer = -1):AbstractTable {
		$this->assertNotReadonly();
		$this->assertColumnExists($columnName);
		$this->assertColumnIsNotPrimaryKey($columnName);

		if ($pointer < 0) {
			$pointer = $this->getPointer();
		}
		$this->content->set($pointer, $columnName, $value);
		return $this;
	}

	final public function __set(string $name, $value) {
		$this->set($name, $value);
	}

	final public function get(string $columnName, int $pointer = -1) {
		if ($pointer < 0) {
			$pointer = $this->pointer;
		}
		return $this->content->get($pointer, $columnName);
	}

	final public function __get(string $name) {
		return $this->get($name);
	}

	/**
	 * @see Select
	 */
	final public function fillBy(string $columnName, $value, int $limit = 0):AbstractTable {
		$this->assertNotReadonly();
		$this->assertContentIsEmpty();
		$this->assertColumnExists($columnName);

		$select = (new Select($this->getTableName()))
				->addCondition($columnName, $this->columnList[$columnName]['type'], $value);

		foreach ($this->columnList as $column => $settings) {
			$select->addExpression($column);
		}

		if ($limit > 0) {
			$select->setLimit($limit);
		}

		$request = new Request($this->config, $this->accessPoint);
		$result  = $request->query($select->assemble());

		if ($result === false) {
			throw new RuntimeException('MySQL-Request failed: `'.$request->getLastQuery().'`');
		}
		if ($result->getGroupCount() === 0) {
			Logger::debug('MySQL-Result is empty (Query: `'.$request->getLastQuery().'`)');
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
	 * @see AbstractTable::update
	 * @see AbstractTable::insert
	 */
	final public function save():bool {
		$this->assertNotReadonly();
		$this->assertContentIsNotEmpty();

		if ($this->isContentFromDB()) {
			return $this->update();
		}
		else {
			return $this->insert();
		}
	}

	/**
	 * @see Update
	 */
	final private function update():bool {
		$request = new Request($this->config, $this->accessPoint);

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
	 * @see Insert
	 */
	final private function insert():bool {
		$this->toReadOnly();

		$request   = new Request($this->config, $this->accessPoint);
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
	 * @see Delete
	 */
	final public function delete() {
		$this->assertNotReadonly();
		$this->assertContentIsNotEmpty();
		$this->assertContentIsFromDB();

		$this->toReadOnly();

		$request = new Request($this->config, $this->accessPoint);

		$queryList = array();
		for ($i = 0; $i < $this->getLength(); $i++) {
			$queryList[] = (new Delete($this->getTableName()))
					->addCondition(
							$this->primaryKey,
							$this->columnList[$this->primaryKey]['type'],
							$this->content->get($i, $this->primaryKey)
					)
					->setLimit(1)
					->assemble();
		}

		try {
			$request->queryList($queryList);
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}

	final private function assertColumnExists(string $columnName) {
		if (!isset($this->columnList[$columnName])) {
			throw new RuntimeException('Can\'t do that! Column `'.$columnName.'` not exists.');
		}
	}

	final private function assertColumnIsNotPrimaryKey(string $columnName) {
		if ($columnName === $this->primaryKey) {
			throw new RuntimeException('Can\'t do that! Column `'.$columnName.'` is primary key.');
		}
	}

	final private function assertNotReadonly() {
		if ($this->isReadOnly()) {
			throw new RuntimeException('Can\'t do that! Object is read only.');
		}
	}

	final private function assertContentIsFromDB() {
		if (!$this->isContentFromDB()) {
			throw new RuntimeException('Can\'t do that! Content isn\'t from DB.');
		}
	}

	final private function assertContentIsEmpty() {
		if ($this->getLength() !== 0) {
			throw new RuntimeException('Can\'t do that! Content isn\'t empty.');
		}
	}

	final private function assertContentIsNotEmpty() {
		if ($this->getLength() === 0) {
			throw new RuntimeException('Can\'t do that! Content is empty.');
		}
	}

}
