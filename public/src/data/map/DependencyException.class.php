<?php
namespace data\map;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class DependencyException extends MAPException {

	public function __construct(AddOn $addOn) {
		parent::__construct('Required Add-On to execute this method');

		$this->setData('name', $addOn->getName());
		$this->setData('minVersion', $addOn->hasMinVersion() ? $addOn->getMinVersion() : null);
		$this->setData('maxVersion', $addOn->hasMaxVersion() ? $addOn->getMaxVersion() : null);
	}

}
