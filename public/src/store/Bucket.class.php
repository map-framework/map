<?php
namespace store;

use RuntimeException;
use store\data\File;
use xml\Node;

class Bucket {

	const PATTERN_GROUP = '/^[A-Za-z0-9_\-.]{1,32}$/';
	const PATTERN_KEY   = '/^[A-Za-z0-9_\-.]{1,32}$/';

	/**
	 * array[group:string|int][key:string|int] => value:mixed
	 *
	 * @var array (see above)
	 */
	private $data = array();

	/**
	 * @see   Bucket::applyIni
	 * @see   Bucket::applyArray
	 * @param null|File|array $applyData
	 */
	public function __construct($applyData = null) {
		if ($applyData instanceof File) {
			$this->applyIni($applyData);
		}
		elseif (is_array($applyData)) {
			$this->applyArray($applyData);
		}
	}

	/**
	 * @param  string|int $group
	 * @return bool
	 */
	final public function isGroup($group) {
		return isset($this->data[$group]);
	}

	/**
	 * @param  string|int $group
	 * @param  string|int $key
	 * @return bool
	 */
	final public function isNull($group, $key) {
		return is_null($this->get($group, $key));
	}

	/**
	 * @param  string|int $group
	 * @param  string|int $key
	 * @return bool
	 */
	final public function isArray($group, $key) {
		return is_array($this->get($group, $key));
	}

	/**
	 * @param  string|int $group
	 * @param  string|int $key
	 * @return bool
	 */
	final public function isString($group, $key) {
		return is_string($this->get($group, $key));
	}

	/**
	 * @param  string|int $group
	 * @param  string|int $key
	 * @return bool
	 */
	final public function isInt($group, $key) {
		return is_int($this->get($group, $key));
	}

	/**
	 * @param  string|int $group
	 * @param  string|int $key
	 * @return bool
	 */
	final public function isBool($group, $key) {
		return $this->isTrue($group, $key) || $this->isFalse($group, $key);
	}

	/**
	 * is exactly true
	 *
	 * @param  string|int $group
	 * @param  string|int $key
	 * @return bool
	 */
	final public function isTrue($group, $key) {
		return $this->get($group, $key) === true;
	}

	/**
	 * is exactly false
	 *
	 * @param  string|int $group
	 * @param  string|int $key
	 * @return bool
	 */
	final public function isFalse($group, $key) {
		return $this->get($group, $key) === false;
	}

	/**
	 * @return int
	 */
	final public function getGroupCount() {
		return count($this->data);
	}

	/**
	 * @param  string|int $group
	 * @return int
	 */
	final public function getKeyCount($group) {
		if (!$this->isGroup($group)) {
			return 0;
		}
		return count($this->data[$group]);
	}

	/**
	 * @param  string|int $group
	 * @param  string|int $key
	 * @param  mixed      $default
	 * @return mixed
	 */
	final public function get($group, $key, $default = null) {
		if (isset($this->data[$group][$key])) {
			return $this->data[$group][$key];
		}
		return $default;
	}

	/**
	 * @param  string|int $group
	 * @param  string|int $key
	 * @param  mixed      $value
	 * @param  boolean    $merge (arrays)
	 * @throws RuntimeException if group or key invalid
	 * @return Bucket
	 */
	final public function set($group, $key, $value, $merge = false) {
		if (!is_int($group) && !preg_match(self::PATTERN_GROUP, $group)) {
			throw new RuntimeException('Invalid group `'.$group.'`.', 1);
		}
		if (!is_int($key) && !preg_match(self::PATTERN_KEY, $key)) {
			throw new RuntimeException('Invalid key `'.$key.'`.', 2);
		}

		if ($merge === true && is_array($value) && $this->isArray($group, $key)) {
			$value = array_merge($this->get($group, $key), $value);
		}

		$this->data[$group][$key] = $value;
		return $this;
	}

	/**
	 * @param  File $iniFile
	 * @throws RuntimeException if file not exist
	 * @return Bucket
	 */
	final public function applyIni(File $iniFile) {
		if ($iniFile === null || !$iniFile->isFile()) {
			throw new RuntimeException('file not exists `'.$iniFile.'`');
		}
		return $this->applyArray(parse_ini_file($iniFile, true, INI_SCANNER_TYPED));
	}

	/**
	 * @see    Bucket::$data
	 * @param  array $data
	 * @throws RuntimeException if data invalid
	 * @return Bucket
	 */
	final public function applyArray($data) {
		if (!is_array($data)) {
			throw new RuntimeException('data is invalid');
		}
		foreach ($data as $group => $keyList) {
			if (!is_array($keyList)) {
				# ignore keys without group
				continue;
			}
			foreach ($keyList as $key => $value) {
				$this->set($group, $key, $value, true);
			}
		}
		return $this;
	}

	/**
	 * @see    Bucket::$data
	 * @return array
	 */
	final public function toArray() {
		return $this->data;
	}

	/**
	 * @param  string $nodeName
	 * @return Node
	 */
	final public function toNode($nodeName) {
		$node = new Node($nodeName);
		foreach ($this->toArray() as $group => $keyList) {
			$groupNode = $node->addChild(new Node($group));
			foreach ($keyList as $key => $value) {
				$groupNode
						->addChild(new Node($key))
						->setContent($value);
			}
		}
		return $node;
	}

	/**
	 * @return string|bool
	 */
	final public function toJson() {
		return json_encode($this->toArray());
	}

}
