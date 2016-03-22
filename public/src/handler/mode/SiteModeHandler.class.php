<?php
namespace handler\mode;

use Exception;
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
	 * array[key:string] = value:mixed
	 *
	 * @see   AbstractModeHandler::__construct
	 * @param Bucket $config
	 * @param MAPUrl $request
	 * @param array  $settings (see above)
	 */
	public function __construct(Bucket $config, MAPUrl $request, $settings) {
		parent::__construct($config, $request, $settings);

		# load stored forms from session
		if (!isset($_SESSION['form'])) {
			$_SESSION['form'] = array();
		}
		$this->storedForms = new Bucket($_SESSION['form']);
	}

	/**
	 * save stored forms into session
	 */
	public function __destruct() {
		$_SESSION['form'] = $this->storedForms->toArray();
	}

	/**
	 * @see    AbstractModeHandler::handle
	 * @return SiteModeHandler this
	 */
	public function handle() {
		# is page present
		try {
			$pageData = $this->getPageData();
		}
		catch (Exception $e) {
			return $this->error(404);
		}

		# choose form status
		$formStatus  = $this->getFormStatus();
		$requestData = $formStatus === null ? $_POST : array();

		# generate & validate page
		$page = new $pageData['nameSpace']($this->config, $requestData);
		if (!($page instanceof AbstractSitePage)) {
			# TODO: throw ExpectedInstanceException (#28)
			throw new RuntimeException('class `'.$pageData['nameSpace'].'` isn\'t instance of `'.AbstractSitePage::class.'`');
		}

		# is page accessible
		if ($page->access() !== true) {
			return $this->error(403);
		}

		# call page -> set-up (response: INIT, RESTORED or REPEATED)
		if ($formStatus !== null) {
			$page->setUp();
		}
		# call page -> check (response: ACCEPTED or REJECTED)
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

		# set response form data (if: RESTORED and REJECTED)
		if ($formStatus === AbstractSitePage::STATUS_RESTORED) {
			$responseFormData = $this->getStoredFormData();
		}
		elseif ($formStatus === AbstractSitePage::STATUS_REJECTED) {
			$responseFormData = $requestData;
		}

		if (isset($responseFormData)) {
			foreach ($responseFormData as $name => $value) {
				$page->setResponseFormItem($name, $value);
			}
		}
		else {
			# set form id (if: INIT, ACCEPTED or REPEATED)
			$page->responseForm->addChild((new Node('formId'))->setContent($this->generateFormId()));
		}

		# enrich response tree
		$page->responseForm->setAttribute('status', $formStatus);
		$page->response->getRootNode()->addChild($this->getTextNode());

		echo (new XSLProcessor())
				->setStyleSheetFile($pageData['styleSheet'])
				->setDocumentDoc($page->response->toDomDoc())
				->transform();

		# create debug file
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
	 * array['className']  = className:string
	 * array['nameSpace']  = nameSpace:string
	 * array['styleSheet'] = styleSheet:File
	 *
	 * @throws Exception
	 * @return null|array (see above)
	 */
	protected function getPageData() {
		$className  = ucfirst($this->request->getPage()).'Page';
		$nameSpace  = 'area\\'.$this->request->getArea().'\logic\site\\'.$className;
		$styleSheet = new File(
				'private/src/area/'.$this->request->getArea().'/app/view/site/'.$this->request->getPage().'.xsl'
		);

		if (!class_exists($nameSpace)) {
			Logger::debug('Returned status 404 because class `'.$nameSpace.'` not found.');
			# TODO: throw ClassNotFoundException (#28)
			throw new Exception('Class `'.$nameSpace.'` not found.', 1);
		}

		if (!$styleSheet->isFile()) {
			Logger::debug('Returned status 404 because file `'.$styleSheet.'` not found.');
			# TODO: throw FileNotFoundException (#28)
			throw new Exception('File `'.$styleSheet.'` not found.', 2);
		}

		return array(
				'className'  => $className,
				'nameSpace'  => $nameSpace,
				'styleSheet' => $styleSheet
		);
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
				$formId = $this->getStoredFormData()['formId'];
				if ($this->isStoredFormClose($formId) === false) {
					return AbstractSitePage::STATUS_RESTORED;
				}
			}
			return AbstractSitePage::STATUS_INIT;
		}
		if (isset($_POST['formId']) && $this->isStoredFormClose($_POST['formId']) === true) {
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
	 * array[name:string] = value:string
	 *
	 * @return null|array (see above)
	 */
	protected function getStoredFormData() {
		if ($this->storedForms->isArray($this->request->getArea(), $this->request->getPage())) {
			return $this->storedForms->get($this->request->getArea(), $this->request->getPage())['data'];
		}
		return null;
	}

	/**
	 * @param  string $formId
	 * @return bool|null
	 */
	protected function isStoredFormClose($formId) {
		$formData = $this->getStoredFormData();
		if ($formData === null || !$this->isFormId($formId) || $formData['formId'] !== $formId) {
			return null;
		}
		return $this->storedForms->get($this->request->getArea(), $this->request->getPage())['close'];
	}

	/**
	 * @return SiteModeHandler this
	 */
	protected function removeStoredForm() {
		$this->storedForms->remove($this->request->getArea(), $this->request->getPage());
		return $this;
	}

	/**
	 * @param  string $formId
	 * @return bool
	 */
	final protected function isFormId($formId) {
		return (bool) preg_match('/^'.self::FORM_ID_PATTERN.'$/', $formId);
	}

	/**
	 * @return string
	 */
	final protected function generateFormId() {
		return substr(str_shuffle(self::FORM_ID_CHARS), 0, self::FORM_ID_LENGTH);
	}

}
