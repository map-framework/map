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

		$requestMethod = strtolower($this->getRequestMethod());
		if ($requestMethod === null) {
			return $this->error(405);
		}

		$object = new $nameSpace($this->config);
		if (!($object instanceof AbstractRestPage)) {
			Logger::error('class `'.$nameSpace.'` is not instance of `'.AbstractRestPage::class.'`');
			return $this->error(500);
		}

		if ($object->access() !== true) {
			return $this->error(403);
		}

		if (isset($this->request->getInputList()[0])) {
			$method = $requestMethod.ucfirst(strtolower($this->request->getInputList()[0]));
			if (method_exists($object, $method)) {
				$responseCode = $object->$method($this->request);
			}
		}
		if (!isset($responseCode)) {
			$method = $requestMethod.'Index';
			if (method_exists($object, $method)) {
				$responseCode = $object->$method($this->request);
			}
		}

		if (!isset($responseCode)) {
			return $this->error(501);
		}

		if ($responseCode === true) {
			$responseCode = 200;
		}
		elseif ($responseCode === false) {
			$responseCode = 400;
		}

		if (!HttpConst::isStatus($responseCode)) {
			$dataText = var_export($responseCode, true);
			$callText = get_class($object).'::'.(isset($method) ? $method : '');
			Logger::error('invalid response `'.$dataText.'` of `'.$callText.'`');

			return $this->error(500);
		}

		echo $object->getResponse()->toJson();
		http_response_code($responseCode);
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
