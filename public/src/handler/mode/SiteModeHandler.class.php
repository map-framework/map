<?php
namespace handler\mode;

use extension\AbstractSitePage;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\Logger;
use xml\Node;
use xml\XSLProcessor;

class SiteModeHandler extends AbstractModeHandler {

	const FORM_ID_CHARS   = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const FORM_ID_LENGTH  = 16;
	const FORM_ID_PATTERN = '['.self::FORM_ID_CHARS.']{'.self::FORM_ID_LENGTH.'}';

	const TEMP_DIR  = 'map';
	const TEMP_FILE = 'lastSiteResponse.xml';

	/**
	 * @var Bucket
	 */
	protected $storedForms = null;

	/**
	 * load stored forms
	 *
	 * @see   AbstractModeHandler::__construct
	 * @param Bucket $config
	 * @param MAPUrl $request
	 * @param array  $settings { string => mixed }
	 */
	public function __construct(Bucket $config, MAPUrl $request, $settings) {
		parent::__construct($config, $request, $settings);
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
	 * @throws RuntimeException
	 * @return SiteModeHandler this
	 */
	public function handle() {
		$className = ucfirst($this->request->getPage()).'Page';

		$nameSpace  = 'area\\'.$this->request->getArea().'\logic\site\\'.$className;
		$styleSheet = new File(
				'private/src/area/'.$this->request->getArea().'/app/view/site/'.$this->request->getPage().'.xsl'
		);

		if (!class_exists($nameSpace)) {
			$reason404 = 'class `'.$nameSpace.'`';
		}
		elseif (!$styleSheet->isFile()) {
			$reason404 = 'stylesheet `'.$styleSheet.'`';
		}

		if (isset($reason404)) {
			Logger::debug('HTTP-404 because: '.$reason404.' not found');
			return $this->error(404);
		}

		$formStatus = $this->getFormStatus();
		if ($formStatus === null) {
			$requestData = $_POST;
		}
		else {
			$requestData = array();
		}

		$page = new $nameSpace($this->config, $requestData);
		if (!($page instanceof AbstractSitePage)) {
			throw new RuntimeException('class `'.$nameSpace.'` isn\'t instance of `'.AbstractSitePage::class.'`');
		}

		if ($page->access() !== true) {
			return $this->error(403);
		}

		if ($formStatus !== null) {
			$page->setUp();
		}
		else {
			if ($page->checkExpectation() === true && $page->check() === true) {
				$formStatus = AbstractSitePage::STATUS_ACCEPTED;
				$this->closeStoredForm($requestData['formId']);
			}
			else {
				$formStatus = AbstractSitePage::STATUS_REJECTED;
				$this->saveForm($requestData);
			}
		}

		# set form data (RESTORED & REJECTED)
		if ($formStatus === AbstractSitePage::STATUS_RESTORED) {
			$formDataList = $this->getStoredFormData();
		}
		elseif ($formStatus === AbstractSitePage::STATUS_REJECTED) {
			$formDataList = $requestData;
		}
		else {
			$formDataList = array();
		}
		foreach ($formDataList as $name => $value) {
			$page->setFormData($name, $value);
		}
		$page->formData->setAttribute('status', $formStatus);

		# set form id (INIT, ACCEPTED & REPEATED)
		if ($formStatus === AbstractSitePage::STATUS_INIT
				|| $formStatus === AbstractSitePage::STATUS_ACCEPTED
				|| $formStatus === AbstractSitePage::STATUS_REPEATED
		) {
			$page->formData->addChild((new Node('formId'))->setContent($this->generateFormId()));
		}

		# set form status
		$page->response->getRootNode()->addChild($this->getTextNode());

		echo (new XSLProcessor())
				->setStyleSheetFile($styleSheet)
				->setDocumentDoc($page->response->toDomDoc())
				->transform();

		# output: XML-Tree into temporary File
		if ($this->settings['tempXMLFile'] === true) {
			(new File(sys_get_temp_dir()))
					->attach(self::TEMP_DIR)
					->makeDir()
					->attach(self::TEMP_FILE)
					->putContents($page->response->getSource(true), false);
		}
		return $this;
	}

	/**
	 * null = unknown/undefined (ACCEPTED or REJECTED)
	 *
	 * @see    AbstractSitePage::STATUS_INIT
	 * @see    AbstractSitePage::STATUS_RESTORED
	 * @see    AbstractSitePage::STATUS_REPEATED
	 * @see    AbstractSitePage::STATUS_ACCEPTED
	 * @see    AbstractSitePage::STATUS_REJECTED
	 * @return null|string
	 */
	protected function getFormStatus() {
		if (!count($_POST)) {
			if ($this->getStoredFormData() !== null) {
				return AbstractSitePage::STATUS_RESTORED;
			}
			return AbstractSitePage::STATUS_INIT;
		}
		if (isset($_POST['formId']) && $this->isStoredFormClose($_POST['formId'])) {
			return AbstractSitePage::STATUS_REPEATED;
		}
		return null;
	}

	/**
	 * @param  string $nodeName
	 * @return Node
	 */
	protected function getTextNode($nodeName = 'text') {
		return $this->getTextBucket()->toNode($nodeName)->setAttribute(
				'language',
				$this->config->get('multiLang', 'language')
		);
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
		return (bool) preg_match('/^'.self::FORM_ID_CHARS.'$/', $formId);
	}

	/**
	 * @return string
	 */
	final protected function generateFormId() {
		return substr(str_shuffle(self::FORM_ID_CHARS), 0, self::FORM_ID_LENGTH);
	}

}
