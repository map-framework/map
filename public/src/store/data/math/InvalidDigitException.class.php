<?php
namespace store\data\math\exception;

use exception\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class InvalidDigitException extends MAPException {

	public function __construct(string $subject, array $digitList) {
		parent::__construct('The subject contains invalid digits.');

		$this->setData('subject', $subject);
		$this->setData('digitList', $digitList);
	}

}
