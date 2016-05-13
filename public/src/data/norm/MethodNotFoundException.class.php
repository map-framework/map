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
class MethodNotFoundException extends MAPException {

	public function __construct(MethodObject ...$methodObject) {
		parent::__construct('Methods not found');

		foreach ($methodObject as $m) {
			$this->addMethodObject($m);
		}
	}

	public function addMethodObject(MethodObject $methodObject):MethodNotFoundException {
		return $this->addData('methodObjectList', $methodObject);
	}

}
