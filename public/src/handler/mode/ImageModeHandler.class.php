<?php
namespace handler\mode;

use exception\file\FileNotFoundException;
use exception\InvalidValueException;
use RuntimeException;
use store\Logger;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class ImageModeHandler extends AbstractModeHandler {

	/**
	 * @throws InvalidValueException
	 * @throws RuntimeException
	 */
	public function handle() {
		try {
			$file = $this->getFile();
		}
		catch (FileNotFoundException $e) {
			return $this->error(404);
		}

		if (!$file->printFile()) {
			Logger::error('failed to print file '.$file);
			return $this->error(500);
		}
		$this->setContentLength($file->getSize());
	}

	/**
	 * @param  int $length
	 * @return ImageModeHandler this
	 */
	final protected function setContentLength($length):ImageModeHandler {
		header('Content-Length: '.$length);
		return $this;
	}

}
