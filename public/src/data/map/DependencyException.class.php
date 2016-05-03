<?php
namespace data\map;

use exception\MAPException;

class DependencyException extends MAPException {

	public function __construct(AddOn $addOn) {
		parent::__construct('Required Add-On to execute this method.');

		$this->setData('name', $addOn->getName());
		$this->setData('minVersion', $addOn->getMinVersion());
		$this->setData('maxVersion', $addOn->getMaxVersion());
	}

}
