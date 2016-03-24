<?php
namespace handler\mode;

use exception\file\FileNotFoundException;
use exception\InvalidValueException;
use handler\AbstractHandler;
use peer\http\HttpConst;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\data\net\Url;
use store\Logger;

/**
 * This file is part of the MAP-Framework.
 *
 * @author    Michael Piontkowski <mail@mpiontkowski.de>
 * @copyright Copyright 2016 Michael Piontkowski
 * @license   https://raw.githubusercontent.com/map-framework/map/master/LICENSE.txt Apache License 2.0
 */
abstract class AbstractModeHandler extends AbstractHandler {

	const PATTERN_MIME_TYPE = '^(text|image|video|audio|application|multipart|message|model|x\-[A-Za-z0-9\-])\/[A-Za-z0-9\-]+$';

	const TEXT_PREFIX   = '{';
	const TEXT_SPLITTER = '#';
	const TEXT_SUFFIX   = '}';
	const LANG_VAR      = '%(lang)';

	/**
	 * @var MAPUrl
	 */
	protected $request = null;

	/**
	 * mode settings
	 *
	 * @var array { string => mixed }
	 */
	protected $settings = array();

	/**
	 * @return AbstractModeHandler this
	 */
	abstract public function handle():AbstractModeHandler;

	/**
	 * @param  Bucket $config
	 * @param  MAPUrl $request
	 * @param  array  $settings { string => mixed }
	 * @throws InvalidValueException
	 */
	public function __construct(Bucket $config, MAPUrl $request, $settings) {
		$this->setContentType($settings['type']);

		$this->request  = $request;
		$this->settings = $settings;
		parent::__construct($config);
	}

	/**
	 * @param  string $mimeType
	 * @throws InvalidValueException
	 * @return AbstractModeHandler this
	 */
	final protected function setContentType(string $mimeType):AbstractModeHandler {
		if (!preg_match('/'.self::PATTERN_MIME_TYPE.'/', $mimeType)) {
			throw new InvalidValueException('MIME-Type', $mimeType);
		}

		header('Content-Type: '.$mimeType);
		return $this;
	}

	/**
	 * @param  Url $address
	 * @return AbstractModeHandler this
	 */
	final protected function setLocation(Url $address):AbstractModeHandler {
		header('Location: '.$address);
		return $this;
	}

	/**
	 * get file in app folder
	 *
	 * @throws InvalidValueException
	 * @throws FileNotFoundException
	 * @return File
	 */
	final protected function getFile():File {
		if (!isset($this->settings['folder']) || !is_string($this->settings['folder'])) {
			throw new InvalidValueException('folder', $this->settings['folder'] ?? null);
		}
		if (!isset($this->settings['extension']) || !is_string($this->settings['extension'])) {
			throw new InvalidValueException('File-Extension', $this->settings['extension'] ?? null);
		}

		$page = implode('/', array_merge(array($this->request->getPage()), $this->request->getInputList()))
				.$this->settings['extension'];

		$fileInArea   = (new File('private/src/area/'.$this->request->getArea().'/app'))
				->attach($this->settings['folder'])
				->attach($page);
		$fileInCommon = (new File('private/src/common/app'))
				->attach($this->settings['folder'])
				->attach($page);

		if ($fileInArea->isFile()) {
			return $fileInArea;
		}
		elseif ($fileInCommon->isFile()) {
			return $fileInCommon;
		}
		throw new FileNotFoundException();
	}

	/**
	 * @param  string $text
	 * @return string
	 */
	final protected function translate(string $text):string {
		$locateTexts = array();

		$suffixPosition = -1;
		while (true) {
			$prefixPosition   = strpos($text, self::TEXT_PREFIX, $suffixPosition + 1);
			$splitterPosition = strpos($text, self::TEXT_SPLITTER, $prefixPosition + 2);
			$suffixPosition   = strpos($text, self::TEXT_SUFFIX, $splitterPosition + 2);

			if ($prefixPosition === false || $splitterPosition === false || $suffixPosition === false) {
				break;
			}

			$group = substr($text, $prefixPosition + 1, $splitterPosition - $prefixPosition - 1);
			$key   = substr($text, $splitterPosition + 1, $suffixPosition - $splitterPosition - 1);

			$locateTexts[$group] = $locateTexts[$group] ?? array();
			if (!in_array($key, $locateTexts[$group])) {
				$locateTexts[$group][] = $key;
			}
		}

		$textBucket = $this->getTextBucket();
		foreach ($locateTexts as $group => $keyList) {
			foreach ($keyList as $key) {
				if ($textBucket->isString($group, $key)) {
					$pattern = self::TEXT_PREFIX.$group.self::TEXT_SPLITTER.$key.self::TEXT_SUFFIX;
					$text    = str_replace($pattern, $textBucket->get($group, $key), $text);
				}
			}
		}
		return $text;
	}

	/**
	 * @return Bucket
	 */
	final protected function getTextBucket():Bucket {
		$texts = new Bucket();

		if (isset($this->settings['multiLang']) && $this->settings['multiLang'] === true) {
			# additional lang-files
			if ($this->config->isArray('multiLang', 'loadList')) {
				$loadPathList = $this->config->get('multiLang', 'loadList');
			}
			else {
				$loadPathList = array();
			}

			# auto-loading lang-file
			if ($this->config->isTrue('multiLang', 'autoLoading')) {
				$loadPathList[] = sprintf(
						'private/src/area/%s/text/%s/page/%s.ini',
						$this->request->getArea(),
						$this->config->get('multiLang', 'language'),
						$this->request->getPage()
				);
			}

			foreach ($loadPathList as $loadPath) {
				$loadFile = new File(str_replace(self::LANG_VAR, $this->config->get('multiLang', 'language'), $loadPath));

				if ($loadFile->isFile()) {
					$texts->applyIni($loadFile);
				}
				else {
					Logger::warning('Lang-File `'.$loadFile.'` not found');
				}
			}
		}
		return $texts;
	}

	/**
	 * @param  int $code
	 * @return AbstractModeHandler this
	 */
	protected function error(int $code):AbstractModeHandler {
		if (!HttpConst::isStatus($code)) {
			throw new RuntimeException('unknown HTTP-Status Code `'.$code.'`');
		}
		http_response_code($code);

		# pipe to URL
		if (isset($this->settings['err'.$code.'-pipe'])) {
			$target = new MAPUrl($this->settings['err'.$code.'-pipe'], $this->config);

			if ($target->get() === $this->request->get()) {
				Logger::error('endless pipe-loop (status: `'.$code.'`) - interrupted with HTTP-Status `508`');
				return $this->error(508);
			}

			$this->setLocation(new Url($this->settings['err'.$code.'-pipe']));
			return $this;
		}

		# default error output
		if (defined('peer\http\HttpConst::STATUS_'.$code)) {
			$message = constant('peer\http\HttpConst::STATUS_'.$code);
		}
		else {
			$message = 'N/A';
		}

		printf('[%d] %s', $code, $message);
		return $this->setContentType('text/plain');
	}

}
