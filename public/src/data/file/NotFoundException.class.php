<?php
namespace data\file;

use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class NotFoundException extends MAPException {

	public function __construct(File ...$file) {
		parent::__construct('Required at least one File of this list.');

		$this->setData('fileList', $file);
	}

}
