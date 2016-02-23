<?php
namespace peer\mysql;

use Exception;
use peer\mysql\statement\Insert;
use peer\mysql\statement\Select;
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
	 * array[columnName:string]['type']       = type:string
	 * array[columnName:string]['hasDefault'] = hasDefault:bool
	 *
	 * @see Query (TYPE_* constants)
	 * @var array (see above)
	 */
	private $columnList = array();

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
	private $isFilled = false;

	/**
	 * init table: call self::addColumn()
	 */
	abstract protected function init();

	/**
	 * @param Bucket $config
	 */
	final public function __construct(Bucket $config) {
		$this->config = $config;
		$this->clear();
		$this->init();
	}

	/**
	 * @param  string $tableName
	 * @return AbstractTable this
	 */
	final public function setTableName($tableName) {
		$this->tableName = $tableName;
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
	 * @param  string $name
	 * @param  string $type
	 * @param  bool   $hasDefault
	 * @return AbstractTable this
	 */
	final protected function addColumn($name, $type, $hasDefault = false) {
		$this->columnList[$name] = array(
				'type'       => $type,
				'hasDefault' => (bool) $hasDefault
		);
		return $this;
	}

	/**
	 * @return int
	 */
	final public function getLength() {
		return $this->content->getGroupCount();
	}

	/**
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
	 * @return bool
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
	 * @return AbstractTable this
	 */
	final public function clear() {
		$this->content  = new Bucket();
		$this->pointer  = 0;
		$this->isFilled = false;
		return $this;
	}

	/**
	 * @param  string   $columnName
	 * @param  mixed    $value
	 * @param  int|null $pointer default this::getPointer()
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
	 * @param  string   $columnName
	 * @param  int|null $pointer default this::getPointer()
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
	 * @param  string   $columnName
	 * @param  mixed    $value
	 * @param  int|null $limit
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
	 * make MySQL INSERT
	 *
	 * @throws Exception
	 * @return bool
	 */
	final private function insert() {
		$request = new Request($this->config);
		$success = true;

		for ($i = 0; $i < $this->content->getGroupCount(); $i++) {
			$insert = new Insert($this->getTableName());

			foreach ($this->columnList as $columnName => $options) {
				if ($this->content->isNull($i, $columnName)) {
					if ($options['hasDefault'] === false) {
						throw new Exception('column `'.$columnName.'` is null (pointer: `'.$i.'`)');
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
	 * make MySQL UPDATE
	 *
	 * @return bool
	 */
	final private function update() {
		# TODO make MYSQL UPDATE
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	final public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * @param  string $name
	 * @return mixed
	 */
	final public function __get($name) {
		return $this->get($name);
	}

}
