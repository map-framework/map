<?php
namespace handler\mode;

use store\data\net\MAPUrl;

class TextModeHandler extends AbstractModeHandler {

	/**
	 * @param  MAPUrl $request
	 * @return TextModeHandler this
	 */
	public function handle(MAPUrl $request) {
		$sourceFile = $this->getFile($request);
		if ($sourceFile === null) {
			return $this->error(404, self::ERROR_404);
		}

		echo $sourceFile->getContents();
		return $this;
	}

}
