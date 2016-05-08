<?php
namespace exception;

use Throwable;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 *            
 * @TODO remove this class - use InvalidDataException, InvalidDataTypeException or InstanceException
 */
class InvalidValueException extends MAPException implements Throwable {

	public function __construct(string $expected, $value) {
		parent::__construct('invalid value '.$this->export($value).' (expected: '.$this->export($expected).')');
	}

}
