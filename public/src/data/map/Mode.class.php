<?php
namespace data\map;

use data\AbstractData;
use data\InvalidDataException;
use data\net\MimeType;
use data\oop\ClassNotFoundException;
use data\oop\ClassObject;
use data\oop\InstanceException;
use handler\mode\AbstractModeHandler;
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

	final public function getSetting(Bucket $config, string $key, $default = null) {
		return $this->getSettings($config)[$key] ?? $default;
	}

	final public function hasSetting(Bucket $config, string $key):bool {
		return $this->getSetting($config, $key) !== null;
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function getType(Bucket $config):MimeType {
		return new MimeType($this->getSetting($config, 'type'));
	}

	/**
	 * @throws InvalidDataException
	 */
	final public function getHandler(Bucket $config):ClassObject {
		return new ClassObject($this->getSetting($config, 'handler'));
	}

	final public function exists(Bucket $config):bool {
		try {
			$type = $this->getType($config);
		}
		catch (InvalidDataException $e) {
			$reason = 'content type is invalid';
		}

		if (!isset($reason)) {
			try {
				$handler = $this->getHandler($config);
				$handler->assertIsChildOf(new ClassObject(AbstractModeHandler::class));
				$handler->assertIsNotAbstract();
			}
			catch (InvalidDataException $e) {
				$reason = 'handler namespace is invalid';
			}
			catch (ClassNotFoundException $e) {
				$reason = 'handler not exists';
			}
			catch (InstanceException $e) {
				$reason = 'handler is not instance of '.AbstractModeHandler::class;
			}
			catch (MAPException $e) {
				$reason = 'handler is abstract';
			}
		}

		if (isset($reason)) {
			Logger::debug(
					'Mode not exists',
					array(
							'Mode'    => $this->get(),
							'Reason'  => $reason,
							'Type'    => $type ?? 'unknown',
							'Handler' => $handler ?? 'unknown'
					)
			);
			return false;
		}
		return true;
	}

	/**
	 * @throws MAPException
	 */
	final public function assertHasSetting(Bucket $config, string $name) {
		if (!$this->hasSetting($config, $name)) {
			throw (new MAPException('Required Setting-Item.'))
					->setData('mode', $this)
					->setData('settings', $this->getSettings($config))
					->setData('settingItem', $name);
		}
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
