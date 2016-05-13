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
class InstanceException extends MAPException {

	public function __construct(ClassObject $children, ClassObject $parent) {
		parent::__construct('Children-Class isn\'t implementation of Parent-Class.');

		$this->setData('children', $children);
		$this->setData('parent', $parent);
	}

}
