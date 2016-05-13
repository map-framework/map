<?php
namespace xml;

use DOMDocument;

/**
 * @TODO migrate to PHP 7 (#32)
 */
class Tree {

	/**
	 * @var Node
	 */
	protected $rootNode = null;

	/**
	 * xml version
	 *
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * xml encoding
	 *
	 * @var null|string
	 */
	protected $encoding = null;

	/**
	 * @var null|string('yes'|'no')
	 */
	protected $standAlone = null;

	/**
	 * @param string $rootName
	 */
	public function __construct($rootName) {
		$this->rootNode = new Node($rootName);
	}

	/**
	 * @return Node
	 */
	final public function getRootNode() {
		return $this->rootNode;
	}

	/**
	 * @param  string $encoding
	 * @return $this
	 */
	final public function setEncoding($encoding) {
		$this->encoding = $encoding;
		return $this;
	}

	/**
	 * @param  $standAlone
	 * @return $this
	 */
	final public function setStandAlone($standAlone) {
		$this->standAlone = $standAlone;
		return $this;
	}

	/**
	 * get XML prolog
	 *
	 * @return string
	 */
	final public function getProlog() {
		$prolog = '<?xml version="'.$this->version.'"';
		if ($this->encoding !== null) {
			$prolog .= ' encoding="'.$this->encoding.'"';
		}
		if ($this->standAlone !== null) {
			$prolog .= ' standalone="'.$this->standAlone.'"';
		}
		return $prolog.'?>'.PHP_EOL;
	}

	/**
	 * @param  bool $indent
	 * @return string
	 * @TODO rename to 'toSource' (+ change in Add-Ons)
	 */
	public function getSource($indent = true) {
		return
				$this->getProlog()
				.$this->rootNode->toSource($indent);
	}

	/**
	 * convert to DOMDocument
	 *
	 * @return DOMDocument
	 */
	final public function toDomDoc() {
		$doc = new DOMDocument();
		$doc->loadXML($this->getSource());
		return $doc;
	}

	/**
	 * @return string
	 */
	final public function __toString() {
		return $this->getSource();
	}

}
