<?php
namespace handler\mode;

use extension\AbstractRestPage;
use peer\http\HttpConst;
use store\Logger;

class RestModeHandler extends AbstractModeHandler {

	/**
	 * @return RestModeHandler this
	 */
	public function handle() {
		$nameSpace = $this->getNameSpace();
		if (!class_exists($nameSpace)) {
			return $this->error(404);
		}

		$requestMethod = $this->getRequestMethod();
		if ($requestMethod === null) {
			return $this->error(405);
		}

		$object = new $nameSpace($this->config);
		if (!($object instanceof AbstractRestPage)) {
			Logger::error('class `'.$nameSpace.'` is not instance of `'.AbstractRestPage::class.'`');
			return $this->error(500);
		}

		# @TODO implement RestModeHandler

		return $this;
	}

	/**
	 * @return null|string
	 */
	protected function getRequestMethod() {
		$method = $_SERVER['REQUEST_METHOD'];
		if (!HttpConst::isMethod($method)) {
			return null;
		}
		return $method;
	}

	/**
	 * @return string
	 */
	protected function getNameSpace() {
		$className = ucfirst($this->request->getPage()).'Page';
		$nameSpace = 'area\\'.$this->request->getArea().'\logic\rest\\'.$className;
		return $nameSpace;
	}

}
