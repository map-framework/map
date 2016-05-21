<?php
namespace xml;

use DOMDocument;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Tree {

	/**
	 * @var Node
	 */
	protected $rootNode;

	/**
	 * XML-Version
	 *
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * XML-Encoding
	 *
	 * @var string
	 */
	protected $encoding;

	/**
	 * XML-StandAlone
	 *
	 * @var bool
	 */
	protected $standAlone;

	public function __construct(string $rootName) {
		$this->rootNode = new Node($rootName);
	}

	public function getRootNode():Node {
		return $this->rootNode;
	}

	public function setEncoding(string $encoding):Tree {
		$this->encoding = $encoding;
		return $this;
	}

	public function setStandAlone(bool $standAlone):Tree {
		$this->standAlone = $standAlone;
		return $this;
	}

	public function getProlog():string {
		$prolog = '<?xml version="'.$this->version.'"';

		if ($this->encoding !== null) {
			$prolog .= ' encoding="'.$this->encoding.'"';
		}

		if ($this->standAlone !== null) {
			$prolog .= ' standalone="'.($this->standAlone ? 'yes' : 'no').'"';
		}

		return $prolog.'?>';
	}

	public function toSource(bool $indent = true):string {
		return $this->getProlog().PHP_EOL.$this->rootNode->toSource($indent);
	}

	public function toDomDoc():DOMDocument {
		$doc = new DOMDocument();
		$doc->loadXML($this->toSource());
		return $doc;
	}

	final public function __toString():string {
		return $this->toSource();
	}

}
