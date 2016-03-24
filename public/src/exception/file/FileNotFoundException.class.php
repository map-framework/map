<?php
namespace exception\file;

use store\data\File;
use Throwable;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class FileNotFoundException extends FileException implements Throwable {

	/**
	 * @param File|\store\data\File[] ...$file
	 */
	public function __construct(File ...$file) {
		$pathList = array_map(
				function(File $n):string {
					return $this->export($n->get());
				},
				$file
		);

		if (count($pathList) === 1) {
			parent::__construct('file '.$pathList[0].' not found');
		}
		else {
			parent::__construct('required one of the following files: '.implode(', ', $pathList));
		}
	}

}
