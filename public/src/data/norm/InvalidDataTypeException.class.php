<?php
namespace data\norm;

use exception\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class InvalidDataTypeException extends MAPException {

	public function __construct(DataTypeEnum $expected, ...$compared) {
		parent::__construct('Expected that the list items are equal the expected type.');

		$this->setData('expected', $expected);
		$this->setData('comparedList', $compared);
	}

}
