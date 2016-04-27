<?php
namespace exception;

use Exception;
use ReflectionClass;
use Throwable;
use xml\Node;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class MAPException extends Exception implements Throwable {

	/**
	 * @var array
	 */
	private $data = array();

	public function __construct(string $message) {
		parent::__construct($message);
	}

	final public function setData(string $name, $value):MAPException {
		$this->data[$name] = is_object($value) ? clone $value : $value;
		return $this;
	}

	final public function getData(string $name) {
		return is_object($this->data[$name]) ? clone $this->data[$name] : $this->data[$name];
	}

	final public function toNode(Node $rootNode = null):Node {
		if ($rootNode === null) {
			$rootNode = new Node('exception');
		}

		$rootNode
				->setAttribute('name', (new ReflectionClass($this))->getShortName())
				->setAttribute('code', $this->getCode())
				->withChild((new Node('message'))->setContent($this->getMessage()))
				->withChild(
						(new Node('file'))
								->setContent($this->getFile())
								->setAttribute('line', $this->getLine())
				);

		$dataListNode = $rootNode->addChild(new Node('dataList'));
		foreach ($this->data as $name => $value) {
			$dataListNode->addChild(
					(new Node('data'))
							->setAttribute('name', $name)
							->setContent(self::export($value))
			);
		}

		$traceListNode = $rootNode->addChild(new Node('traceList'));
		foreach ($this->getTrace() as $trace) {
			$traceListNode->addChild(
					(new Node('trace'))
							->setAttribute('type', $trace['type'])
							->withChild(
									(new Node('file'))
											->setContent($trace['file'])
											->setAttribute('line', $trace['line'])
							)
							->withChild(
									(new Node('class'))
											->setContent($trace['class'])
											->setAttribute('function', $trace['function'])
							)
							->withChild(
									(new Node('args'))
											->setContent(self::export($trace['args']))
							)
			);
		}
		return $rootNode;
	}

	final public static function export($data):string {
		if (is_null($data)) {
			return 'NULL';
		}
		if (is_bool($data)) {
			return $data === true ? 'TRUE' : 'FALSE';
		}
		if (is_integer($data) || is_float($data)) {
			return $data;
		}
		if (is_string($data)) {
			return '"'.$data.'"';
		}
		if (is_array($data)) {
			$itemList = array();
			foreach ($data as $key => $value) {
				$itemList[] = $key.' => '.self::export($value);
			}
			return 'ARRAY['.implode(', ', $itemList).']';
		}
		if (is_resource($data)) {
			return 'RESOURCE';
		}
		if (is_object($data)) {
			return get_class($data).(method_exists($data, '__toString') ? '("'.$data.'")' : '');
		}
		return 'UNKNOWN';
	}

}
