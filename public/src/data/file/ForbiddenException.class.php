<?php
namespace data\file;

use exception\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class ForbiddenException extends MAPException {

	public function __construct(File $file, bool $read = false, bool $write = false, bool $execute = false) {
		parent::__construct('Required rights for file.');

		$this->setData('file', $file);
		$this->setData('read', $read);
		$this->setData('write', $write);
		$this->setData('execute', $execute);
	}

}
