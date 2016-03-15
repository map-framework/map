<?php
namespace handler\mode;

use extension\AbstractSitePage;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use xml\Node;
use xml\XSLProcessor;

class SiteModeHandler extends AbstractModeHandler {

	const PATTERN_FORM_ID = '^[a-zA-Z0-9]+$';
	const PATH_TEMP_XML   = '.siteResponse.xml';

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

		if (!class_exists($nameSpace) || !$styleSheet->isFile()) {
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
			if ($formStatus === AbstractSitePage::STATUS_RESTORED) {
				foreach ($this->getStoredFormData() as $name => $value) {
					$page->setFormData($name, $value);
				}
			}
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

		$page->formData->setAttribute('status', $formStatus);
		$page->response->getRootNode()->addChild($this->getTextNode());

		echo (new XSLProcessor())
				->setStyleSheetFile($styleSheet)
				->setDocumentDoc($page->response->toDomDoc())
				->transform();

		# output: XML-Tree into temporary File
		if (isset($this->settings['tempXMLFile']) && $this->settings['tempXMLFile'] === true) {
			$xmlFile = new File(self::PATH_TEMP_XML);
			$xmlFile->putContents($page->response->getSource(true), false);
			exit();
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
				$this->config->get('display', 'language')
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
		return (bool) preg_match('/'.self::PATTERN_FORM_ID.'/', $formId);
	}

}
