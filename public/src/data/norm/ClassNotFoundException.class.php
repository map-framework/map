<?php
namespace data\norm;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class ClassNotFoundException extends MAPException {

	public function __construct(ClassObject $classObject) {
		parent::__construct('Class not found.');

		$this->setData('class', $classObject);
	}

}
