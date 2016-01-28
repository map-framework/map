<?php
namespace handler\mode;

use handler\AbstractHandler;
use store\data\net\Url;

abstract class AbstractModeHandler extends AbstractHandler {

	const PATTERN_X_CONFIG = '/^x-[a-zA-Z0-9\-_#]+$/';

	/**
	 * @var string MIME-Type
	 */
	private $type = null;

	/**
	 * @var string[] X-Config
	 */
	private $xConfig = array();

	/**
	 * @param $type string
	 * @param $xConfig string[]
	 */
	final public function __construct($type, $xConfig) {
		$this->type = $type;

		foreach ($xConfig as $key => $value) {
			if (preg_match(self::PATTERN_X_CONFIG, $key)) {
				$this->xConfig[substr($key, 2)] = $value;
			}
		}
	}

	/**
	 * @param  Url $data
	 * @return bool
	 */
	abstract public function handle(Url $data);

}