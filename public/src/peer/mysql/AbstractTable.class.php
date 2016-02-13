<?php
namespace peer\mysql;

use DateTime;
use RuntimeException;
use store\Bucket;

abstract class AbstractTable {

	# Integer
	const TYPE_TINYINT   = 'TINYINT';
	const TYPE_SMALLINT  = 'SMALLINT';
	const TYPE_MEDIUMINT = 'MEDIUMINT';
	const TYPE_INT       = 'INT';
	const TYPE_BIGINT    = 'BIGINT';

	# Float-Number
	const TYPE_FLOAT   = 'FLOAT';
	const TYPE_DOUBLE  = 'DOUBLE';
	const TYPE_DECIMAL = 'DECIMAL';

	# String
	const TYPE_CHAR    = 'CHAR';
	const TYPE_VARCHAR = 'VARCHAR';
	const TYPE_TEXT    = 'TEXT';

	# Date & Time
	const TYPE_TIMESTAMP = 'TIMESTAMP';
	const TYPE_DATETIME  = 'DATETIME';
	const TYPE_DATE      = 'DATE';
	const TYPE_TIME      = 'TIME';
	const TYPE_YEAR      = 'YEAR';

	# Lists
	const LIST_INT    = array(
			self::TYPE_TINYINT,
			self::TYPE_SMALLINT,
			self::TYPE_MEDIUMINT,
			self::TYPE_INT,
			self::TYPE_BIGINT
	);
	const LIST_FLOAT  = array(
			self::TYPE_FLOAT,
			self::TYPE_DOUBLE,
			self::TYPE_DECIMAL
	);
	const LIST_STRING = array(
			self::TYPE_CHAR,
			self::TYPE_VARCHAR,
			self::TYPE_TEXT
	);
	const LIST_DATE   = array(
			self::TYPE_TIMESTAMP,
			self::TYPE_DATETIME,
			self::TYPE_DATE,
			self::TYPE_TIME,
			self::TYPE_YEAR
	);

	# Format Patterns
	const FORMAT_DATETIME = 'Y-m-d h:i:s';
	const FORMAT_DATE     = 'Y-m-d';
	const FORMAT_TIME     = 'h:i:s';
	const FORMAT_YEAR     = 'Y';

	/**
	 * @var Bucket
	 */
	protected $config = null;

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
	 * is filled
	 *
	 * @var bool
	 */
	private $filled = false;

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
		$this->content = new Bucket();
		$this->pointer = 0;
		return $this;
	}

	/**
	 * @param  string $columnName
	 * @param  mixed  $content
	 * @param  int    $pointer default self::getPointer()
	 * @throws RuntimeException
	 * @return AbstractTable this
	 */
	final public function set($columnName, $content, $pointer = null) {
		if (!isset($this->columnList[$columnName])) {
			throw new RuntimeException('column `'.$columnName.'` not exists');
		}
		$type = $this->columnList[$columnName]['type'];

		if (in_array($type, self::LIST_INT)) {
			# Integer
			$content = (int) $content;
		}
		elseif (in_array($type, self::LIST_FLOAT)) {
			# Float-Number
			$content = (float) $content;
		}
		elseif (in_array($type, self::LIST_STRING)) {
			# String
			$content = (string) $content;
		}
		elseif (in_array($type, self::LIST_DATE)) {
			# Date & Time
			if (!($content instanceof DateTime)) {
				throw new RuntimeException('content is not instance of `\DateTime`');
			}

			if ($type === self::TYPE_DATETIME) {
				$content = $content->format(self::FORMAT_DATETIME);
			}
			elseif ($type === self::TYPE_DATE) {
				$content = $content->format(self::FORMAT_DATE);
			}
			elseif ($type === self::TYPE_TIME) {
				$content = $content->format(self::FORMAT_TIME);
			}
			elseif ($type === self::TYPE_YEAR) {
				$content = $content->format(self::FORMAT_YEAR);
			}
			else {
				$content = $content->getTimestamp();
			}
		}
		else {
			throw new RuntimeException('type `'.$type.'` not valid');
		}

		if ($pointer === null) {
			$pointer = $this->getPointer();
		}
		$this->content->set($pointer, $columnName, $content);
		return $this;
	}

	/**
	 * @param  string   $columnName
	 * @param  int|null $pointer
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

		# @TODO set self::filled = true on success
	}

	/**
	 * make MYSQL UPDATE or INSERT
	 *
	 * @return bool
	 */
	final public function save() {
		if ($this->filled) {
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
