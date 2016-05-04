<?php
namespace handler\mode;

use data\file\NotFoundException;
use exception\InvalidValueException;
use exception\MAPException;
use util\Logger;

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
	 */
	public function handle() {
		try {
			$file = $this->getFile();
		}
		catch (NotFoundException $e) {
			return $this->error(404);
		}
		catch (InvalidValueException $e) {
			Logger::error($e);
			return $this->error(500);
		}

		$file->output();
		$this->setContentLength($file->getSize());
	}

	final protected function setContentLength(int $length):ImageModeHandler {
		header('Content-Length: '.$length);
		return $this;
	}

}
