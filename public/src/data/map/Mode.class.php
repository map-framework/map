<?php
namespace data\map;

use data\AbstractData;
use data\InvalidDataException;
use data\net\MimeType;
use data\norm\ClassObject;
use handler\AbstractHandler;
use util\Bucket;
use util\Logger;
use util\MAPException;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
class Mode extends AbstractData {

	const PATTERN_NAME = '^[0-9A-Za-z_\-+]{1,32}$';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @throws InvalidDataException
	 */
	public function set(string $name) {
		self::assertIsName($name);

		$this->name = $name;
	}

	public function get():string {
		return $this->name;
	}

	final public function getSettings(Bucket $config):array {
		return $config->get('mode', $this->get(), array());
	}

	final public function getSettingItem(Bucket $config, string $key) {
		return $this->getSettings($config)[$key] ?? null;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function getType(Bucket $config):MimeType {
		return new MimeType($this->getSettingItem($config, 'type'));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function getHandler(Bucket $config):ClassObject {
		return new ClassObject($this->getSettingItem($config, 'handler'));
	}

	final public function exists(Bucket $config):bool {
		$type    = $this->getSettingItem($config, 'type');
		$handler = $this->getSettingItem($config, 'handler');

		if ($type === null) {
			$reason = 'missing type';
		}
		elseif ($handler === null) {
			$reason = 'missing handler';
		}
		elseif (!MimeType::isType($type)) {
			$reason = 'invalid type';
		}
		elseif (class_exists($handler)) {
			$reason = 'handler-class not found';
		}
		else {
			$handlerObject = new $handler();
			if ($handlerObject instanceof AbstractHandler) {
				return true;
			}
			$reason = 'handler-class not instance of '.AbstractHandler::class;
		}

		Logger::debug(
				'Mode not exists',
				array(
						'Mode'    => $this->get(),
						'Reason'  => $reason,
						'Type'    => $type,
						'Handler' => $handler
				)
		);
		return false;
	}

	/**
	 * @throws MAPException
	 */
	final public function assertExists(Bucket $config) {
		if (!$this->exists($config)) {
			throw (new MAPException('Expected existing mode.'))
					->setData('mode', $this)
					->setData('settings', $this->getSettings($config));
		}
	}

	final public static function isName(string $name):bool {
		return self::isMatching(self::PATTERN_NAME, $name);
	}

	/**
	 * @throws InvalidDataException
	 */
	final public static function assertIsName(string $name) {
		self::assertIsMatching(self::PATTERN_NAME, $name);
	}

}
