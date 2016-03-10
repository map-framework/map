<?php
namespace xml;

use RuntimeException;
use store\Logger;

class Node {

	/**
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var array { string => string }
	 */
	protected $attributes = array();

	/**
	 * @var null|string
	 */
	protected $content = null;

	/**
	 * @var array { Node }
	 */
	protected $children = array();

	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->setName($name);
	}

	/**
	 * @param  string $name
	 * @return Node this
	 */
	final public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param  string $name
	 * @param  string $value
	 * @return Node this
	 */
	final public function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
		return $this;
	}

	/**
	 * @param  string $name
	 * @return null|string
	 */
	final public function getAttribute($name) {
		if (!isset($this->attributes[$name])) {
			return null;
		}
		return $this->attributes[$name];
	}

	/**
	 * @param  string $name
	 * @return bool
	 */
	final public function hasAttribute($name) {
		return $this->getAttribute($name) !== null;
	}

	/**
	 * @return array { name => value }
	 */
	final public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @return string
	 */
	final public function getName() {
		return $this->name;
	}

	/**
	 * @param  null|string $content
	 * @return Node this
	 */
	final public function setContent($content) {
		if (is_object($content) && method_exists($content, '__toString')) {
			$content = (string) $content;
		}
		if ($content === null || is_string($content) || is_int($content)) {
			$this->content = $content;
		}
		else {
			Logger::warning('ignored node content of type `'.gettype($content).'`');
		}
		return $this;
	}

	/**
	 * @return null|string
	 */
	final public function getContent() {
		return $this->content;
	}

	/**
	 * @param  Node $child
	 * @return Node child
	 */
	final public function addChild(Node $child) {
		$this->children[] = $child;
		return $child;
	}

	/**
	 * @param  Node $child
	 * @return Node this
	 */
	final public function withChild(Node $child) {
		$this->addChild($child);
		return $this;
	}

	/**
	 * @return array { Node }
	 */
	final public function getChildren() {
		return $this->children;
	}

	/**
	 * @return int
	 */
	final public function countChildren() {
		return count($this->getChildren());
	}

	/**
	 * @return bool
	 */
	final public function hasChildren() {
		return (bool) $this->countChildren();
	}

	/**
	 * @param  bool   $indent
	 * @param  string $prefix
	 * @param  string $prefixChar
	 * @return string
	 */
	public function getSource($indent = true, $prefix = '', $prefixChar = "\t") {
		$prefixThis  = $prefix;
		$prefixChild = $prefix;

		if ($indent === true) {
			$prefixThis  = $prefix;
			$prefixChild = $prefix.$prefixChar;
		}

		$xml = $prefixThis.'<'.$this->getName();
		foreach ($this->getAttributes() as $name => $value) {
			$xml .= ' '.$name.'="'.$value.'"';
		}

		$closing = '</'.$this->getName().'>';

		if ($this->getContent() !== null) {
			return $xml.'>'.$this->getContent().$closing;
		}
		elseif ($this->hasChildren()) {
			$xml .= '>'.PHP_EOL;
			foreach ($this->getChildren() as $child) {
				if ($child instanceof Node) {
					$xml .= $child->getSource($indent, $prefixChild, $prefixChar).PHP_EOL;
				}
			}
			$xml .= $prefixThis.$closing;
			return $xml;
		}
		else {
			return $xml.'/>';
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getSource();
	}

}
