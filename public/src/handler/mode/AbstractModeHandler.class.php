<?php
namespace handler\mode;

use data\net\MimeType;
use data\net\ParseException;
use data\peer\http\StatusEnum;
use util\Bucket;
use data\net\MAPUrl;
use data\net\Url;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
abstract class AbstractModeHandler {

	/**
	 * @var Bucket
	 */
	protected $config;

	/**
	 * @var MAPUrl
	 */
	protected $request;

	abstract public function handle();

	public function __construct(Bucket $config, MAPUrl $request) {
		$this->config  = $config;
		$this->request = $request;
	}

	/**
	 * @throws ParseException
	 */
	final public function outputFailurePage(StatusEnum $responseStatus) {
		self::setResponseStatus($responseStatus);
		$mode  = $this->request->getMode();
		$group = $mode->getConfigGroup();

		if ($this->config->isNull($group, 'error')) {
			$this->config->assertIsArray($group, 'error');

			$errorList = $this->config->get($group, 'error');
			if (isset($errorList[$responseStatus->getCode().'-pipe'])) {
				$pipeUrl = new Url($errorList[$responseStatus->getCode().'-pipe']);

				# endless loop protection
				if ($pipeUrl->get() === $this->request->get()) {
					$this->outputFailurePage(new StatusEnum(StatusEnum::LOOP_DETECTED));
					return;
				}

				$this->setLocation($pipeUrl);
				return;
			}
		}

		# default failure output
		$this->setContentType(new MimeType('text/plain'));
		printf(
				'[%d] %s',
				$responseStatus->getCode(),
				$responseStatus->getMessage()
		);
	}

	final public static function setContentType(MimeType $type) {
		header('Content-Type: '.$type);
	}

	final public static function setLocation(Url $address) {
		header('Location: '.$address);
	}

	final public static function setResponseStatus(StatusEnum $responseStatus) {
		http_response_code($responseStatus->getCode());
	}

}
