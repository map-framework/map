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

	const ERROR_403 = 'Forbidden';
	const ERROR_404 = 'Not Found';

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
		$this->setMimeType($settings['type']);

		$this->request = $request;
		$this->settings = $settings;
		parent::__construct($config);
	}

	/**
	 * @param  string $mimeType
	 * @return AbstractModeHandler this
	 */
	final protected function setMimeType($mimeType) {
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
		if (!isset($this->settings['prefix'], $this->settings['suffix'])) {
			throw new RuntimeException('mode invalid: expect `prefix` and `suffix`');
		}

		$fileList = array(
				new File('private/src/area/'.$this->request->getArea().'/app'),
				new File('private/src/common/app')
		);
		foreach ($fileList as $file) {
			if (!($file instanceof File)) {
				continue;
			}
			$file
					->attach($this->settings['prefix'])
					->attach($this->request->getPage().$this->settings['suffix']);
			if ($file->isFile()) {
				return $file;
			}
		}
		return null;
	}

	/**
	 * @return Bucket
	 */
	final protected function getText() {
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
	 * @param  string $message
	 * @return SiteModeHandler this
	 */
	protected function error($code, $message) {
		http_response_code($code);
		if (isset($this->settings['error'.$code])) {
			$errSettings = $this->settings['error'.$code];

			# pipe to url
			if (isset($errSettings['pipe'])) {
				$this->setLocation(new Url($errSettings['pipe']));
				return $this;
			}
		}

		# default error-output
		$this->setMimeType('text/plain');
		echo '['.$code.'] '.$message;
		return $this;
	}

}
