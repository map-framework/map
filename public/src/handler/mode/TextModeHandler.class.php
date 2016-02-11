<?php
namespace handler\mode;

class TextModeHandler extends AbstractModeHandler {

	/**
	 * @return TextModeHandler this
	 */
	public function handle() {
		$sourceFile = $this->getFile();
		if ($sourceFile === null) {
			return $this->error(404, self::ERROR_404);
		}

		echo $this->translate($sourceFile->getContents());
		return $this;
	}

}
