<?php
namespace data\file;

use util\MAPException;

class UnexpectedTypeException extends MAPException {

	public function __construct(File $file, TypeEnum $expected) {
		parent::__construct('Required file of type.');

		$this->setData('file', $file);
		$this->setData('expectedType', $expected);
	}

}
