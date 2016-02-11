<?php
namespace handler\mode;

use RuntimeException;

class ImageModeHandler extends AbstractModeHandler {

	/**
	 * @throws RuntimeException
	 * @return ImageModeHandler this
	 */
	public function handle() {
		$file = $this->getFile();
		if ($file === null) {
			return $this->error(404, self::ERROR_404);
		}

		if (!$file->printFile()) {
			throw new RuntimeException('failed to print file `'.$file.'`');
		}
		return $this->setContentLength($file->getSize());
	}

	/**
	 * @param  int $length
	 * @return ImageModeHandler this
	 */
	final protected function setContentLength($length) {
		header('Content-Length: '.$length);
		return $this;
	}

}
