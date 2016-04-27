<?php
namespace exception\file;

use exception\MAPException;
use store\data\File;
use Throwable;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class FileNotFoundException extends MAPException implements Throwable {

	public function __construct(File ...$file) {
		parent::__construct('File(s) not found.');

		$this->setData('fileList', $file);
	}

}
