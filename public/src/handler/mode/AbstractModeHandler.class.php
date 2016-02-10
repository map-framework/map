<?php
namespace handler\mode;

use handler\AbstractHandler;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\data\net\Url;

abstract class AbstractModeHandler extends AbstractHandler {

	const ERROR_403 = 'Forbidden';
	const ERROR_404 = 'Not Found';

	/**
	 * mode settings
	 *
	 * @var array { string => mixed }
	 */
	protected $settings = array();

	/**
	 * @param  MAPUrl $request
	 * @return AbstractModeHandler this
	 */
	abstract public function handle(MAPUrl $request);

	/**
	 * @throws RuntimeException
	 * @param  Bucket $config
	 * @param  array  $settings { string => mixed }
	 */
	public function __construct(Bucket $config, $settings) {
		if (!isset($settings['type'])) {
			throw new RuntimeException('mode invalid: expect `type`');
		}
		$this->setMimeType($settings['type']);

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
	 * @param  MAPUrl $request
	 * @throws RuntimeException
	 * @return null|File
	 */
	final protected function getFile(MAPUrl $request) {
		if (!isset($this->settings['prefix'], $this->settings['suffix'])) {
			throw new RuntimeException('mode invalid: expect `prefix` and `suffix`');
		}

		$fileList = array(
				new File('private/src/area/'.$request->getArea().'/app'),
				new File('private/src/common/app')
		);
		foreach ($fileList as $file) {
			if (!($file instanceof File)) {
				continue;
			}
			$file
					->attach($this->settings['prefix'])
					->attach($request->getPage().$this->settings['suffix']);
			if ($file->isFile()) {
				return $file;
			}
		}
		return null;
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
