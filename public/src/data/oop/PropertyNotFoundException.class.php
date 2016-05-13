<?php
namespace data\oop;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class PropertyNotFoundException extends MAPException {

	public function __construct(PropertyObject ...$propertyObject) {
		parent::__construct('Properties not found');

		foreach ($propertyObject as $p) {
			$this->addPropertyObject($p);
		}
	}

	public function addPropertyObject(PropertyObject $propertyObject):PropertyNotFoundException {
		return $this->addData('propertyObjectList', $propertyObject);
	}

}
