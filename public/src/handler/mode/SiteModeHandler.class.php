<?php
namespace handler\mode;

use Exception;
use exception\file\FileNotFoundException;
use exception\MAPException;
use extension\AbstractSitePage;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\Logger;
use xml\Node;
use xml\XSLProcessor;

class SiteModeHandler extends AbstractModeHandler {

	const FORM_ID_LENGTH = 16;
	const TEMP_DIR       = 'map';
	const TEMP_FILE      = 'lastSiteResponse.xml';

	/**
	 * @var Bucket
	 */
	protected $storedForms;

	public function __construct(Bucket $config, MAPUrl $request, array $settings) {
		parent::__construct($config, $request, $settings);

		# load stored forms from session
		if (!isset($_SESSION['form'])) {
			$_SESSION['form'] = array();
		}
		$this->storedForms = new Bucket($_SESSION['form']);
	}

	public function __destruct() {
		# save stored forms into session
		$_SESSION['form'] = $this->storedForms->toArray();
	}

	public function handle() {
		try {
			$pageData = $this->getPageData();
		}
		catch (MAPException $e) {
			return $this->error(404);
		}

		$formStatus  = $this->getFormStatus();
		$requestData = $formStatus === null ? $_POST : array();

		$page = new $pageData['nameSpace']($this->config, $requestData);
		if (!($page instanceof AbstractSitePage)) {
			throw new RuntimeException('class `'.$pageData['nameSpace'].'` isn\'t instance of `'.AbstractSitePage::class.'`');
		}

		if ($page->access() !== true) {
			return $this->error(403);
		}

		if ($formStatus !== null) {
			# formStatus == INIT, RESTORED or REPEATED
			$page->setUp();
		}
		else {
			# formStatus == ACCEPTED or REJECTED
			if ($page->checkExpectation() === true && $page->check() === true) {
				$formStatus = AbstractSitePage::STATUS_ACCEPTED;
				$this->closeStoredForm($requestData['formId']);
			}
			else {
				$formStatus = AbstractSitePage::STATUS_REJECTED;
				$this->saveForm($requestData);
			}
		}

		if ($formStatus === AbstractSitePage::STATUS_RESTORED) {
			$responseFormData = $this->getStoredFormData();
		}
		elseif ($formStatus === AbstractSitePage::STATUS_REJECTED) {
			$responseFormData = $requestData;
		}

		if (isset($responseFormData)) {
			# formStatus == RESTORED or REJECTED
			foreach ($responseFormData as $name => $value) {
				$page->setResponseFormItem($name, $value);
			}
		}
		else {
			# formStatus == INIT, ACCEPTED or REPEATED
			$page->responseForm->addChild((new Node('formId'))->setContent(self::generateFormId()));
		}

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
	}

	/**
	 * @throws FileNotFoundException
	 * @throws MAPException
	 */
	protected function getPageData():array {
		$className  = ucfirst($this->request->getPage()).'Page';
		$nameSpace  = 'area\\'.$this->request->getArea().'\logic\site\\'.$className;
		$styleSheet = new File(
				'private/src/area/'.$this->request->getArea().'/app/view/site/'.$this->request->getPage().'.xsl'
		);

		if (!class_exists($nameSpace)) {
			Logger::debug('HTTP-Status 404 because: class `'.$nameSpace.'` not found');
			throw new MAPException('class `'.$nameSpace.'` not found.', 1);
		}
		if (!$styleSheet->isFile()) {
			Logger::debug('HTTP-Status 404 because: file `'.$styleSheet.'` not found');
			throw new FileNotFoundException($styleSheet);
		}

		return array(
				'className'  => $className,
				'nameSpace'  => $nameSpace,
				'styleSheet' => $styleSheet
		);
	}

	/**
	 * Returns status RESTORED, REPEATED or INIT.
	 * Returns null if status unknown (ACCEPTED or REJECTED).
	 *
	 * @return mixed
	 */
	protected function getFormStatus() {
		if (!count($_POST)) {
			if (count($this->getStoredFormData())) {
				$formId = $this->getStoredFormData()['formId'];
				if ($this->isStoredForm($formId) && !$this->isStoredFormClose($formId)) {
					return AbstractSitePage::STATUS_RESTORED;
				}
			}
			return AbstractSitePage::STATUS_INIT;
		}
		if (isset($_POST['formId'])) {
			if ($this->isStoredForm($_POST['formId']) && $this->isStoredFormClose($_POST['formId'])) {
				return AbstractSitePage::STATUS_REPEATED;
			}
		}
		return null;
	}

	protected function getTextNode(string $nodeName = 'text'):Node {
		return $this->getTextBucket()->toNode($nodeName)->setAttribute(
				'language',
				$this->config->get('multiLang', 'language')
		);
	}

	protected function saveForm(array $data, bool $close = false):SiteModeHandler {
		$form = array(
				'data'  => $data,
				'close' => $close
		);
		$this->storedForms->set($this->request->getArea(), $this->request->getPage(), $form);
		return $this;
	}

	protected function closeStoredForm(string $formId):SiteModeHandler {
		if (self::isFormId($formId)) {
			$this->saveForm(['formId' => $formId], true);
		}
		return $this;
	}

	protected function getStoredFormData():array {
		if ($this->storedForms->isArray($this->request->getArea(), $this->request->getPage())) {
			return $this->storedForms->get($this->request->getArea(), $this->request->getPage())['data'];
		}
		return array();
	}

	protected function isStoredForm(string $formId):bool {
		$formData = $this->getStoredFormData();
		return count($formData) && $formData['formId'] === $formId;
	}

	protected function isStoredFormClose(string $formId):bool {
		if (!$this->isStoredForm($formId)) {
			throw new RuntimeException('form not found (formId: `'.$formId.'`)');
		}
		return $this->storedForms->get($this->request->getArea(), $this->request->getPage())['close'];
	}

	protected function removeStoredForm():SiteModeHandler {
		$this->storedForms->remove($this->request->getArea(), $this->request->getPage());
		return $this;
	}

	final public static function isFormId(string $formId):bool {
		return strlen($formId) === self::FORM_ID_LENGTH && ctype_xdigit($formId);
	}

	final public static function generateFormId():string {
		return bin2hex(random_bytes(self::FORM_ID_LENGTH / 2));
	}

}
