<?php
namespace handler\mode;

use handler\AbstractHandler;
use store\data\net\MAPUrl;
use store\data\net\Url;

abstract class AbstractModeHandler extends AbstractHandler {

	/**
	 * @param  MAPUrl $request
	 * @param  array $modeSettings
	 * @return AbstractModeHandler
	 */
	abstract public function handle(MAPUrl $request, $modeSettings);

	/**
	 * @param  string $mimeType
	 * @return AbstractModeHandler
	 */
	final protected function setMimeType($mimeType) {
		header('Content-Type: '.$mimeType);
		return $this;
	}

	/**
	 * @param  Url $address
	 * @return AbstractModeHandler
	 */
	final protected function setLocation(Url $address) {
		header('Location: '.$address);
		return $this;
	}

}