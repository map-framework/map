<?php
namespace handler\mode;

use store\Logger;
use extension\AbstractPage;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use xml\Node;
use xml\XSLProcessor;

class SiteModeHandler extends AbstractModeHandler {

	const  PATTERN_FORM_ID = '^[a-zA-Z0-9]+$';

	/**
	 * @var MAPUrl
	 */
	protected $request = null;

	/**
	 * @var Bucket
	 */
	protected $storedForms = null;

	/**
	 * load stored forms
	 *
	 * @see   AbstractModeHandler::__construct()
	 * @param Bucket $config
	 * @param array  $settings { string => mixed }
	 */
	public function __construct(Bucket $config, $settings) {
		parent::__construct($config, $settings);
		if (!isset($_SESSION['form'])) {
			$_SESSION['form'] = array();
		}
		$this->storedForms = new Bucket($_SESSION['form']);
	}

	/**
	 * save stored forms in session
	 */
	public function __destruct() {
		$_SESSION['form'] = $this->storedForms->toArray();
	}

	/**
	 * @see    AbstractModeHandler::handle
	 * @param  MAPUrl $request
	 * @throws RuntimeException
	 * @return AbstractModeHandler this
	 */
	public function handle(MAPUrl $request) {
		$this->request = $request;

		$className = ucfirst($request->getPage()).'Page';

		$nameSpace  = 'area\\'.$request->getArea().'\logic\site\\'.$className;
		$styleSheet = new File('private/src/area/'.$request->getArea().'/app/view/site/'.$request->getPage().'.xsl');

		if (!class_exists($nameSpace) || !$styleSheet->isFile()) {
			return $this->error(404, self::ERROR_404);
		}

		$formStatus = $this->getFormStatus();
		if ($formStatus === null) {
			$requestData = $_POST;
		}
		else {
			$requestData = array();
		}

		$page = new $nameSpace($requestData);
		if (!($page instanceof AbstractPage)) {
			throw new RuntimeException('class `'.$nameSpace.'` isn\'t instance of `'.AbstractPage::class.'`');
		}

		if ($page->access() !== true) {
			return $this->error(403, self::ERROR_403);
		}

		if ($formStatus !== null) {
			if ($formStatus === AbstractPage::STATUS_RESTORED) {
				foreach ($this->getStoredFormData() as $name => $value) {
					$page->setFormData($name, $value);
				}
			}
			$page->setUp();
		}
		else {
			if ($page->checkExpectation() === true && $page->check() === true) {
				$formStatus = AbstractPage::STATUS_ACCEPTED;
				$this->closeStoredForm($requestData['formId']);
			}
			else {
				$formStatus = AbstractPage::STATUS_REJECTED;
				$this->saveForm($requestData);
			}
		}

		$page->formData->setAttribute('status', $formStatus);
		$page->response->getRootNode()->addChild($this->getTextNode());

		echo (new XSLProcessor())
				->setStyleSheetFile($styleSheet)
				->setDocumentDoc($page->response->toDomDoc())
				->transform();
		return $this;
	}

	/**
	 * @see AbstractPage::STATUS_INIT
	 * @see AbstractPage::STATUS_RESTORED
	 * @see AbstractPage::STATUS_REPEATED
	 * @see AbstractPage::STATUS_ACCEPTED
	 * @see AbstractPage::STATUS_REJECTED
	 * null = unknown (ACCEPTED or REJECTED)
	 * @return null|string
	 */
	protected function getFormStatus() {
		if (!count($_POST)) {
			if ($this->getStoredFormData() !== null) {
				return AbstractPage::STATUS_RESTORED;
			}
			return AbstractPage::STATUS_INIT;
		}
		if (isset($_POST['formId']) && $this->isStoredFormClose($_POST['formId'])) {
			return AbstractPage::STATUS_REPEATED;
		}
		return null;
	}

	/**
	 * @param  string $nodeName
	 * @return Node
	 */
	protected function getTextNode($nodeName = 'text') {
		$texts = new Bucket();

		# is enabled
		if ($this->config->isTrue('multiLang', 'enabled')) {
			# get text file paths
			if ($this->config->isArray('multiLang', 'texts')) {
				$textFileList = $this->config->get('multiLang', 'texts');
			}
			else {
				$textFileList = array();
				Logger::warning('expect `array { string }` in config: `display` -> `texts`');
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
					Logger::warning('text file `'.$textFile.'` not found');
				}
			}
		}

		# create node
		return $texts->toNode($nodeName)->setAttribute('language', $this->config->get('display', 'language'));
	}

	/**
	 * @return null|array { string => string }
	 */
	protected function getStoredFormData() {
		$form = $this->storedForms->get($this->request->getArea(), $this->request->getPage());
		if ($form === null) {
			return $form;
		}
		return $form['data'];
	}

	/**
	 * @param  array $data { string => string }
	 * @param  bool  $close
	 * @return SiteModeHandler this
	 */
	protected function saveForm($data, $close = false) {
		$form = array(
				'data'  => $data,
				'close' => $close
		);
		$this->storedForms->set($this->request->getArea(), $this->request->getPage(), $form);
		return $this;
	}

	/**
	 * @param  string $formId
	 * @return SiteModeHandler this
	 */
	protected function closeStoredForm($formId) {
		if ($this->isFormId($formId)) {
			$this->saveForm(['formId' => $formId], true);
		}
		return $this;
	}

	/**
	 * @param  string $formId
	 * @return bool
	 */
	protected function isStoredFormClose($formId) {
		$form = $this->getStoredFormData();
		if ($form === null || !$this->isFormId($formId) || $form['formId'] !== $formId) {
			return false;
		}
		return $this->storedForms->get($this->request->getArea(), $this->request->getPage())['close'];
	}

	/**
	 * @param  string $formId
	 * @return bool
	 */
	final protected function isFormId($formId) {
		return (bool) preg_match('/'.self::PATTERN_FORM_ID.'/', $formId);
	}

}
