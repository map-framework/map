<?php
namespace handler\mode;

use handler\AbstractHandler;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\data\net\Url;
use store\Logger;

abstract class AbstractModeHandler extends AbstractHandler {

	const TEXT_PREFIX   = '{';
	const TEXT_SPLITTER = '#';
	const TEXT_SUFFIX   = '}';

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
	abstract public function handle();

	/**
	 * @throws RuntimeException
	 * @param  Bucket $config
	 * @param  MAPUrl $request
	 * @param  array  $settings { string => mixed }
	 */
	public function __construct(Bucket $config, MAPUrl $request, $settings) {
		if (!isset($settings['type'])) {
			throw new RuntimeException('mode invalid: expect `type`');
		}
		$this->setContentType($settings['type']);

		$this->request  = $request;
		$this->settings = $settings;
		parent::__construct($config);
	}

	/**
	 * @param  string $mimeType
	 * @return AbstractModeHandler this
	 */
	final protected function setContentType($mimeType) {
		header('Content-Type: '.$mimeType);
		return $this;
	}

	/**
	 * @param  Url $address
	 * @return AbstractModeHandler this
	 */
	final protected function setLocation(Url $address) {
		header('Location: '.$address);
		return $this;
	}

	/**
	 * get file in app folder
	 *
	 * @throws RuntimeException
	 * @return null|File
	 */
	final protected function getFile() {
		if (!isset($this->settings['folder'], $this->settings['extension'])) {
			throw new RuntimeException('mode invalid: expect `folder` and `extension`');
		}

		$fileList = array(
				new File('private/src/area/'.$this->request->getArea().'/app'),
				new File('private/src/common/app')
		);

		$page = implode('/', array_merge(array($this->request->getPage()), $this->request->getInputList()));

		foreach ($fileList as $file) {
			if (!($file instanceof File)) {
				continue;
			}
			$file
					->attach($this->settings['folder'])
					->attach($page.$this->settings['extension']);
			if ($file->isFile()) {
				return $file;
			}
		}
		return null;
	}

	/**
	 * @param  string $text
	 * @return string
	 */
	final protected function translate($text) {
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

			if (!isset($locateTexts[$group])) {
				$locateTexts[$group] = array();
			}
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
	final protected function getTextBucket() {
		$texts = new Bucket();
		# is enabled
		if (isset($this->settings['multiLang']) && $this->settings['multiLang'] === true) {
			# get text file paths
			if ($this->config->isArray('multiLang', 'texts')) {
				$textFileList = $this->config->get('multiLang', 'texts');
			}
			else {
				$textFileList = array();
			}

			# is autoPageTexts enabled
			if ($this->config->isTrue('multiLang', 'autoPageTexts')) {
				$textFileList[] = $this->request->getPage().'.ini';
			}

			# apply text files
			foreach ($textFileList as $textFile) {
				$path       = '/text/'.$this->config->get('display', 'language').'/';
				$areaFile   = (new File('private/src/area/'.$this->request->getArea().$path))->attach($textFile);
				$commonFile = (new File('private/src/common'.$path))->attach($textFile);

				if ($areaFile->isFile()) {
					$texts->applyIni($areaFile);
				}
				elseif ($commonFile->isFile()) {
					$texts->applyIni($commonFile);
				}
				else {
					Logger::warning('lang-file `'.$textFile.'` not found');
				}
			}
		}
		return $texts;
	}

	/**
	 * @param  int    $code
	 * @return AbstractModeHandler this
	 */
	protected function error($code) {
		http_response_code($code);

		# pipe to URL
		if (isset($this->settings['err'.$code.'-pipe'])) {
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

		$this->setContentType('text/plain');
		echo '['.$code.'] '.$message;
		return $this;
	}

}
