<?php
namespace xml;

use Exception;
use store\data\File;
use XSLTProcessor;
use DOMDocument;

class XSLProcessor {

	/**
	 * @var array { string => string }
	 */
	protected $params = array();

	/**
	 * @var array { DOMDocument }
	 */
	protected $styleSheets = array();

	/**
	 * @var DOMDocument
	 */
	protected $document = null;

	/**
	 * @param  string $name
	 * @param  string $value
	 * @return XSLProcessor this
	 */
	final public function setParam($name, $value) {
		$this->params[$name] = $value;
		return $this;
	}

	/**
	 * @param  DOMDocument $styleSheet
	 * @return XSLProcessor this
	 */
	final public function addStyleSheetDoc(DOMDocument $styleSheet) {
		$this->styleSheets[] = $styleSheet;
		return $this;
	}

	/**
	 * @param  File $styleSheetFile
	 * @throws Exception
	 * @return XSLProcessor this
	 */
	final public function addStyleSheetFile(File $styleSheetFile) {
		if (!$styleSheetFile->isFile()) {
			throw new Exception('file `'.$styleSheetFile.'` not found');
		}
		$styleSheet = new DOMDocument();
		$styleSheet->load($styleSheetFile);
		$this->styleSheets[] = $styleSheet;
		return $this;
	}

	/**
	 * @param  DOMDocument $document
	 * @return XSLProcessor this
	 */
	final public function setDocumentDoc(DOMDocument $document) {
		$this->document = $document;
		return $this;
	}

	/**
	 * @param  File $document
	 * @throws Exception
	 * @return XSLProcessor this
	 */
	final public function setDocumentFile(File $document) {
		if (!$document->isFile()) {
			throw new Exception('file `'.$document.'` not found');
		}
		$this->document = new DOMDocument();
		$this->document->load($document);
		return $this;
	}

	/**
	 * run the xsl transformation
	 * @throws Exception
	 * @return string
	 */
	public function transform() {
		$processor = new XSLTProcessor();

		foreach ($this->styleSheets as $styleSheet) {
			$processor->importStylesheet($styleSheet);
		}
		foreach ($this->params as $name => $value) {
			$processor->setParameter('', $name, $value);
		}

		$result = $processor->transformToXml($this->document);
		if ($result === false) {
			throw new Exception('XSL transformation error');
		}
		return $result;
	}

}
