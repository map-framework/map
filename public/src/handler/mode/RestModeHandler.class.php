<?php
namespace handler\mode;

use exception\MAPException;
use extension\AbstractRestPage;
use peer\http\HttpConst;
use util\Logger;

/**
 * TODO migrate to new AbstractModeHandler
 * TODO outsource into Plug-In
 * TODO implement Annotations (#35)
 */
class RestModeHandler extends AbstractModeHandler {

	public function handle() {
		$nameSpace = $this->getTargetNameSpace();
		if (!class_exists($nameSpace)) {
			return $this->error(404);
		}

		$object = new $nameSpace($this->config);
		if (!($object instanceof AbstractRestPage)) {
			Logger::error(sprintf('class %s is not instance of %s', $nameSpace, AbstractRestPage::class));
			return $this->error(500);
		}

		if ($object->access() !== true) {
			return $this->error(403);
		}

		try {
			$requestMethod = $this->getRequestMethod();
		}
		catch (MAPException $e) {
			return $this->error(405);
		}

		if (count($this->request->getInputList()) > 0) {
			$targetMethod = $requestMethod.ucfirst(strtolower($this->request->getInputList()[0]));
			if (!method_exists($object, $targetMethod)) {
				unset($targetMethod);
			}
		}
		if (!isset($targetMethod)) {
			$targetMethod = $requestMethod.'Index';
		}
		if (!method_exists($object, $targetMethod)) {
			return $this->error(404);
		}

		$responseCode = $object->$targetMethod($this->request);
		if (!HttpConst::isStatus($responseCode)) {
			Logger::error(
					sprintf(
							'invalid HTTP response code %d (returned by %s::%s)',
							$responseCode,
							get_class($object),
							$targetMethod
					)
			);
			return $this->error(505);
		}

		echo $object->getResponse()->toJson();
		http_response_code($responseCode);
	}

	/**
	 * @see    HttpConst
	 * @throws MAPException
	 * @return string
	 */
	protected function getRequestMethod():string {
		if (!HttpConst::isMethod($_SERVER['REQUEST_METHOD'])) {
			throw new MAPException('invalid request method');
		}
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * @return string
	 */
	protected function getTargetNameSpace():string {
		$className = ucfirst($this->request->getPage()).'Page';
		return 'area\\'.$this->request->getArea().'\logic\rest\\'.$className;
	}

}
