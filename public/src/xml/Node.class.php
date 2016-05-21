<?php
namespace xml;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Node {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $attributeList = array();

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var Node[]
	 */
	protected $childList = array();

	public function __construct(string $name) {
		$this->setName($name);
	}

	public function setName(string $name):Node {
		$this->name = $name;
		return $this;
	}

	public function getName():string {
		return $this->name;
	}

	public function setAttribute(string $name, string $value):Node {
		$this->attributeList[$name] = $value;
		return $this;
	}

	public function getAttributeList():array {
		return $this->attributeList;
	}

	public function getAttribute(string $name, string $default = ''):string {
		return $this->attributeList[$name] ?? $default;
	}

	public function removeAttribute(string ...$name):Node {
		foreach ($name as $n) {
			unset($this->attributeList[$n]);
		}
		return $this;
	}

	public function setContent(string $content):Node {
		$this->content = $content;
		return $this;
	}

	public function getContent():string {
		return $this->content;
	}

	/**
	 * @return Node child-node
	 */
	public function addChild(Node $child):Node {
		$this->childList[] = $child;
		return $child;
	}

	/**
	 * @return Node this-node
	 */
	public function withChild(Node ...$child):Node {
		foreach ($child as $c) {
			$this->addChild($c);
		}
		return $this;
	}

	/**
	 * @return Node[]
	 */
	public function getChildList(string $nameFilter = null):array {
		foreach ($this->childList as $child) {
			if ($nameFilter === null || $child->getName() === $nameFilter) {
				$filteredChildList[] = $child;
			}
		}
		return $filteredChildList ?? array();
	}

	public function childrenCount():int {
		return count($this->getChildList());
	}

	public function toSource(bool $indent = true, string $prefix = '', string $prefixChar = "\t"):string {
		$source = $prefix.'<'.$this->getName();
		foreach ($this->getAttributeList() as $name => $value) {
			$source .= ' '.$name.'="'.$value.'"';
		}

		if (!$this->hasContent() && !$this->hasChildren()) {
			return $source.' />';
		}

		if ($this->hasContent()) {
			$source .= $this->getContent();
		}
		else {
			$source .= '>'.PHP_EOL;
			foreach ($this->getChildList() as $child) {
				$source .= $child->toSource($indent, $indent === true ? $prefix.$prefixChar : '', $prefixChar).PHP_EOL;
			}
		}
		return $source.'</'.$this->getName().'>';
	}

	public function __toString():string {
		return $this->toSource();
	}

	final public function hasAttribute(string ...$name):bool {
		foreach ($name as $n) {
			if (!isset($this->attributeList[$n])) {
				return false;
			}
		}
		return true;
	}

	final public function hasContent():bool {
		return $this->content !== null;
	}

	final public function hasChildren():bool {
		return $this->childrenCount() !== 0;
	}

	/**
	 * @throws MAPException
	 */
	final public function assertHasContent() {
		if (!$this->hasContent()) {
			throw (new MAPException('Expected content'))
					->setData('node', $this);
		}
	}

	/**
	 * @throws MAPException
	 */
	final public function assertHasChildren() {
		if (!$this->hasChildren()) {
			throw (new MAPException('Expected children'))
					->setData('node', $this);
		}
	}

}
