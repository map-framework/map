<?php
namespace peer\mysql;

use DateTime;
use MySQLi;
use RuntimeException;

final class Query {

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

	# Custom
	const TYPE_TABLE  = 'TABLE';
	const TYPE_COLUMN = 'COLUMN';

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
	const LIST_CUSTOM = array(
			self::TYPE_TABLE,
			self::TYPE_COLUMN
	);

	# Format Patterns
	const FORMAT_DATETIME = 'Y-m-d h:i:s';
	const FORMAT_DATE     = 'Y-m-d';
	const FORMAT_TIME     = 'h:i:s';
	const FORMAT_YEAR     = 'Y';

	# Place-Holder
	const REPLACE_PREFIX = '%(';
	const REPLACE_SUFFIX = ')';

	# Boundary
	const BOUNDARY_TABLE  = '`';
	const BOUNDARY_COLUMN = '`';
	const BOUNDARY_STRING = '\'';

	/**
	 * @var string
	 */
	private $query = null;

	/**
	 * @var array { int => array { 'type' => string, 'value' => mixed } }
	 */
	private $placeHolderList = array();

	/**
	 * @param string $query
	 */
	public function __construct($query = null) {
		if ($query !== null) {
			$this->setQuery($query);
		}
	}

	/**
	 * @param  string $query
	 * @return Query this
	 */
	public function setQuery($query) {
		$this->query = $query;
		return $this;
	}

	/**
	 * @param  string $type
	 * @param  mixed  $value
	 * @throws RuntimeException
	 * @return string placeholder
	 */
	public function placeHolder($type, $value) {
		$this->addPlaceHolder($type, $value);
		return self::REPLACE_PREFIX.(count($this->placeHolderList) - 1).self::REPLACE_SUFFIX;
	}

	/**
	 * alias for Query::placeHolder
	 *
	 * @param  string $type
	 * @param  mixed  $value
	 * @throws RuntimeException
	 * @return string placeholder
	 */
	public function ph($type, $value) {
		return $this->placeHolder($type, $value);
	}

	/**
	 * @param  string $type
	 * @param  mixed  $value
	 * @throws RuntimeException
	 * @return Query this
	 */
	public function addPlaceHolder($type, $value) {
		if ($value !== null) {
			if (in_array($type, self::LIST_INT)) {
				$value = (int) $value;
			}
			elseif (in_array($type, self::LIST_FLOAT)) {
				$value = (float) $value;
			}
			elseif (in_array($type, self::LIST_STRING)) {
				$value = (string) $value;
			}
			elseif (in_array($type, self::LIST_DATE)) {
				if (!($value instanceof DateTime)) {
					throw new RuntimeException('value is not instance of `DateTime`');
				}

				if ($type === self::TYPE_DATETIME) {
					$value = $value->format(self::FORMAT_DATETIME);
				}
				elseif ($type === self::TYPE_DATE) {
					$value = $value->format(self::FORMAT_DATE);
				}
				elseif ($type === self::TYPE_TIME) {
					$value = $value->format(self::FORMAT_TIME);
				}
				elseif ($type === self::TYPE_YEAR) {
					$value = $value->format(self::FORMAT_YEAR);
				}
				else {
					$value = $value->getTimestamp();
				}
			}
			elseif (in_array($type, self::LIST_CUSTOM)) {
				if ($type === self::TYPE_COLUMN) {
					$value = self::BOUNDARY_COLUMN.$value.self::BOUNDARY_COLUMN;
				}
				elseif ($type === self::TYPE_TABLE) {
					$value = self::BOUNDARY_TABLE.$value.self::BOUNDARY_TABLE;
				}
			}
			else {
				throw new RuntimeException('type `'.$type.'` is invalid');
			}
		}

		$this->placeHolderList[] = array(
				'type'  => $type,
				'value' => $value
		);
		return $this;
	}

	/**
	 * @see    this::addPlaceHolder()
	 * @param  string $type
	 * @param  mixed  $value
	 * @throws RuntimeException
	 * @return Query
	 */
	public function addPH($type, $value) {
		return $this->addPlaceHolder($type, $value);
	}

	/**
	 * @param  MySQLi $mysqli
	 * @return string
	 */
	public function getQuery(MySQLi $mysqli) {
		$query = $this->query;
		if (!is_string($query)) {
			throw new RuntimeException('query is not a string');
		}
		foreach ($this->placeHolderList as $number => $placeHolder) {
			if (!in_array($placeHolder['type'], self::LIST_CUSTOM) && is_string($placeHolder['value'])) {
				$value = self::BOUNDARY_STRING.$mysqli->escape_string($placeHolder['value']).self::BOUNDARY_STRING;
			}
			elseif ($placeHolder['value'] === null) {
				$value = 'NULL';
			}
			else {
				$value = $placeHolder['value'];
			}
			$query = str_replace(self::REPLACE_PREFIX.$number.self::REPLACE_SUFFIX, $value, $query);
		}
		return $query;
	}

}
