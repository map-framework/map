<?php
namespace store;

use RuntimeException;
use store\data\File;
use xml\Node;

/**
 * @TODO write unit-tests
 */
class Bucket {
	
	const PATTERN_GROUP = '/^[A-Za-z0-9_-]{1,32}$/';
	const PATTERN_KEY = '/^[A-Za-z0-9_-]{1,32}$/';
	
	private $data = array();

	/**
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
	 * @param  string $group
	 * @param  string $key
	 * @return bool
	 */
	final public function isNull($group, $key) {
		return is_null($this->get($group, $key));
	}

	/**
	 * @param  string $group
	 * @param  string $key
	 * @return bool
	 */
	final public function isArray($group, $key) {
		return is_array($this->get($group, $key));
	}

	/**
	 * @param  string $group
	 * @param  string $key
	 * @return bool
	 */
	final public function isString($group, $key) {
		return is_string($this->get($group, $key));
	}

	/**
	 * @param  string $group
	 * @param  string $key
	 * @return bool
	 */
	final public function isInt($group, $key) {
		return is_int($this->get($group, $key));
	}

	/**
	 * @param  string $group
	 * @param  string $key
	 * @return bool
	 */
	final public function isBool($group, $key) {
		return is_bool($this->get($group, $key));
	}

	/**
	 * is exactly true
	 * @param  $group
	 * @param  $key
	 * @return bool
	 */
	final public function isTrue($group, $key) {
		return $this->get($group, $key) === true;
	}

	/**
	 * is exactly false
	 * @param  $group
	 * @param  $key
	 * @return bool
	 */
	final public function isFalse($group, $key) {
		return $this->get($group, $key) === false;
	}
	
	/**
	 * @param  string $group
	 * @param  string $key
	 * @param  string $default
	 * @return mixed
	 */
	final public function get($group, $key, $default = null) {
		if (isset($this->data[$group][$key])) {
			return $this->data[$group][$key];
		}
		return $default;
	}

	/**
	 * @param  string $group
	 * @param  string $key
	 * @param  mixed $value
	 * @throws RuntimeException if group or key invalid
	 * @return Bucket
	 */
	final public function set($group, $key, $value) {
		if (!preg_match(self::PATTERN_GROUP, $group)) {
			throw new RuntimeException('Invalid group `'.$group.'`.');
		}
		if (!preg_match(self::PATTERN_KEY, $key)) {
			throw new RuntimeException('Invalid key `'.$key.'`.');
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
	 * @param  array $data
	 * @throws RuntimeException if data invalid
	 * @return Bucket
	 */
	final public function applyArray($data)	{
		if (!is_array($data)) {
			throw new RuntimeException('data is invalid');
		}
		foreach ($data as $group => $keyList) {
			if (!is_array($keyList)) {
				# ignore keys without group
				continue;
			}
			foreach ($keyList as $key => $value) {
				$this->set($group, $key, $value);
			}
		}
		return $this;
	}

	/**
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
	
}