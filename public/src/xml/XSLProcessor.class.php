<?php
namespace xml;

use data\file\File;
use data\file\ForbiddenException;
use data\file\NotFoundException;
use data\file\UnexpectedTypeException;
use DOMDocument;
use util\Logger;
use util\MAPException;
use XSLTProcessor;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class XSLProcessor {

	/**
	 * @var string[]
	 */
	protected $parameterList = array();

	/**
	 * @var DOMDocument
	 */
	protected $stylesheet;

	/**
	 * @var DOMDocument
	 */
	protected $document;

	public function setParameter(string $name, string $value):XSLProcessor {
		$this->parameterList[$name] = $value;
		return $this;
	}

	public function setStylesheet(DOMDocument $stylesheet):XSLProcessor {
		$this->stylesheet = $stylesheet;
		return $this;
	}

	/**
	 * @throws ForbiddenException
	 * @throws NotFoundException
	 * @throws UnexpectedTypeException
	 */
	public function setStylesheetFile(File $file):XSLProcessor {
		$file->assertExists();
		$file->assertIsFile();
		$file->assertIsReadable();

		$stylesheet = new DOMDocument();
		$stylesheet->load($file->getRealPath());
		return $this->setStylesheet($stylesheet);
	}

	public function setDocument(DOMDocument $document):XSLProcessor {
		$this->document = $document;
		return $this;
	}

	/**
	 * @throws ForbiddenException
	 * @throws NotFoundException
	 * @throws UnexpectedTypeException
	 */
	final public function setDocumentFile(File $file):XSLProcessor {
		$file->assertExists();
		$file->assertIsFile();
		$file->assertIsReadable();

		$document = new DOMDocument();
		$document->load($file->getRealPath());
		return $this->setDocument($document);
	}

	/**
	 * Start the XSL-Transformation.
	 *
	 * @throws MAPException
	 */
	public function transform():string {
		$processor = new XSLTProcessor();
		$processor->importStylesheet($this->stylesheet);
		$processor->registerPHPFunctions();

		foreach ($this->parameterList as $parameterName => $parameterValue) {
			if (!$processor->setParameter('', $parameterName, $parameterValue)) {
				throw new MAPException(
						'Failed to set parameter',
						array(
								'parameterName'  => $parameterName,
								'parameterValue' => $parameterValue
						)
				);
			}
		}

		$result = $processor->transformToXml($this->document);
		if ($result === false) {
			throw new MAPException(
					'XSL-Transformation failed',
					array(
							'stylesheetFile' => Logger::storeText($this->stylesheet->saveXML()),
							'documentFile'   => Logger::storeText($this->document->saveXML()),
							'parameterList'  => $this->parameterList
					)
			);
		}
		return $result;
	}

}
