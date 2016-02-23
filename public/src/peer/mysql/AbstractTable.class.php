<?php
namespace peer\mysql;

use DateTime;
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
	 * array[columnName : string]['type']       : string
	 * array[columnName : string]['hasDefault'] : bool
	 *
	 * @var array see above
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
		return (new ReflectionClass($this))->getShortName();
	}

	/**
	 * @param  string $name
	 * @param  string $type
	 * @param  bool   $hasDefault
	 * @return AbstractTable this
	 */
	final protected function addColumn($name, $type, $hasDefault) {
		$this->columnList[$name] = array(
				'type'       => $type,
				'hasDefault' => $hasDefault
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
	 * @return AbstractTable this
	 */
	final public function next() {
		return $this->setPointer($this->getPointer() + 1);
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
	 * make MYSQL SELECT
	 *
	 * @param  string $columnName
	 * @param  mixed  $content
	 * @return bool
	 */
	final public function fillBy($columnName, $content) {
		$this->clear();

		# @TODO make MYSQL SELECT

		# @TODO set self::isFilled = true on success
	}

	/**
	 * make MYSQL UPDATE or INSERT
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
	 * make MYSQL INSERT
	 *
	 * @return bool
	 */
	final private function insert() {
		# @TODO make MYSQL INSERT
	}

	/**
	 * make MYSQL UPDATE
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
