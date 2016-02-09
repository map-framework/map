<?php
namespace handler\mode;

use extension\AbstractPage;
use RuntimeException;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\data\net\Url;
use xml\XSLProcessor;

class SiteModeHandler extends AbstractModeHandler {

	const
			ERROR_403 = 'Forbidden',
			ERROR_404 = 'Not Found';

	const
			PATTERN_FORM_ID = '^[a-zA-Z0-9]+$';

	/**
	 * @var MAPUrl
	 */
	protected $request = null;

	/**
	 * @var array { string => mixed }
	 */
	protected $modeSettings = array();

	/**
	 * @var Bucket
	 */
	protected $session = null;

	/**
	 * @see   AbstractModeHandler::__construct()
	 * @param Bucket $config
	 */
	public function __construct(Bucket $config) {
		$this->session = new Bucket($_SESSION);
		parent::__construct($config);
	}

	/**
	 * save session
	 */
	public function __destruct() {
		$_SESSION = $this->session->toArray();
	}

	/**
	 * @see    AbstractModeHandler::handle
	 * @param  MAPUrl $request
	 * @param  array $modeSettings
	 * @throws RuntimeException
	 * @return AbstractModeHandler this
	 */
	public function handle(MAPUrl $request, $modeSettings) {
		$this->request = $request;
		$this->modeSettings = $modeSettings;

		$className = ucfirst($request->getPage()).'Page';

		$nameSpace = 'area\\'.$request->getArea().'\logic\site\\'.$className;
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
		echo (new XSLProcessor())
				->addStyleSheetFile($styleSheet)
				->addStyleSheetFile($this->getLayout())
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
	 *
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
	 * @throws RuntimeException
	 * @return File
	 */
	protected function getLayout() {
		$layout = null;
		if (isset($this->modeSettings['layout']) && is_string($this->modeSettings['layout'])) {
			$layout = $this->modeSettings['layout'];
			$layoutCommon = (new File('private/src/common/'))->attach($layout);
			$layoutArea = (new File('private/src/area/'.$this->request->getArea()))->attach($layout);

			if ($layoutArea->isFile()) {
				return $layoutArea;
			}
			elseif ($layoutCommon->isFile()) {
				return $layoutCommon;
			}
		}
		throw new RuntimeException('layout `'.$layout.'` not found');
	}

	/**
	 * @return null|array { string => string }
	 */
	protected function getStoredFormData() {
		$form = $this->session->get('form', $this->getPageId());
		if ($form === null) {
			return $form;
		}
		return $form['data'];
	}

	/**
	 * @param  array $data { string => string }
	 * @param  bool $close
	 * @return SiteModeHandler this
	 */
	protected function saveForm($data, $close = false) {
		$form = array(
				'data' => $data,
				'close' => $close
		);
		$this->session->set('form', $this->getPageId(), $form);
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
		return $this->session->get('form', $this->getPageId())['close'];
	}

	/**
	 * @return string
	 */
	final protected function getPageId() {
		return $this->request->getArea().'#'.$this->request->getPage();
	}

	/**
	 * @param  string $formId
	 * @return bool
	 */
	final protected function isFormId($formId) {
		return (bool)preg_match('/'.self::PATTERN_FORM_ID.'/', $formId);
	}

	/**
	 * @param  int $code
	 * @param  string $message
	 * @return SiteModeHandler this
	 */
	protected function error($code, $message) {
		if (isset($this->modeSettings['error'.$code])) {
			$errSettings = $this->modeSettings['error'.$code];

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
