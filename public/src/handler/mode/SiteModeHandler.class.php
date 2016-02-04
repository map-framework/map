<?php
namespace handler\mode;

use DOMDocument;
use RuntimeException;
use extension\AbstractPage;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\data\net\Url;
use XMLWriter;
use XSLTProcessor;

class SiteModeHandler extends AbstractModeHandler {

	/**
	 * @var MAPUrl
	 */
	protected $request;
	protected $modeSettings;

	/**
	 * @see    AbstractModeHandler::handle()
	 * @param  MAPUrl $request
	 * @param  array $modeSettings
	 * @return SiteModeHandler
	 */
	public function handle(MAPUrl $request, $modeSettings)	{
		$this->request      = $request;
		$this->modeSettings = $modeSettings;

		$pageClassName   = ucfirst($request->getPage()).'Page';
		$pageNameSpace   = 'area\\'.$request->getArea().'\logic\site\\'.$pageClassName;

		$pageXSLFile     = new File('private/src/area/'.$request->getArea().'/app/view/site/'.$request->getPage().'.xsl');

		if (!class_exists($pageNameSpace) || !$pageXSLFile->isFile()) {
			return $this->error(404, 'Not Found');
		}

		$status = $this->analyzeStatus();
		if (is_array($_POST) && $status === null) {
			$request = $_POST;
		}
		else {
			$request = array();
		}

		$page = new $pageNameSpace($request);
		if (!($page instanceof AbstractPage)) {
			throw new RuntimeException('class `'.$pageNameSpace.'` isn\'t instance of `'.AbstractPage::class.'`');
		}

		if (!$page->access()) {
			$this->error(403, 'Forbidden');
		}

		if ($status !== null) {

			if ($status === AbstractPage::STATUS_RESTORED) {
				foreach ($this->getStoredFormData() as $name => $value) {
					$page->response->set('formData', $name, $value);
				}
			}
			$page->setUp();
		}
		else {

			if ($page->checkExpectation() === true && $page->check() === true) {
				$status = AbstractPage::STATUS_ACCEPTED;
				$this->closeStoredForm($request['formId']);
			}
			else {
				$status = AbstractPage::STATUS_REJECTED;
				$this->setStoredForm($request);
			}
		}

		$page->response->set('formStatus', 'id', $status);
		return $this->printResult($pageXSLFile, $page->response);
	}

	/**
	 * @param  File $pageXSLFile
	 * @param  Bucket $response
	 * @return SiteModeHandler
	 */
	protected function printResult(File $pageXSLFile, Bucket $response) {
		$pathPrefixList = array(
			'private/src/common/text/',
			'private/src/area/'.$this->request->getArea().'/text/'
		);

		$texts = new Bucket();

		foreach ($pathPrefixList as $pathPrefix) {
			foreach ($this->config->get('display', 'texts') as $fileName) {
				$file = (new File($pathPrefix))
						->attach($this->config->get('display', 'language'))
						->attach($fileName);

				if ($file->isFile()) {
					$texts->applyIni($file);
				}
			}
		}

		include_once new File('public/src/misc/functions.php');
		define('TEXTS', serialize($texts));

		$xslt = new XSLTProcessor();
		$xslt->registerPHPFunctions();

		$xslPage = new DOMDocument();
		$xslPage->load($pageXSLFile);
		$xslt->importStylesheet($xslPage);

		$xslLayout = new DOMDocument();
		$xslLayout->load($this->getLayout());
		$xslt->importStylesheet($xslLayout);

		echo $xslt->transformToXml($response->toDOMDocument('response'));
		return $this;
	}

	/**
	 * @param  string $name
	 * @param  array $items
	 * @param  XMLWriter $writer
	 * @return string|null
	 */
	protected function arrayToXML($name, $items, XMLWriter $writer = null) {
		if ($writer === null) {
			$writer = new XMLWriter();
			$writer->openMemory();
		}

		$writer->startElement($name);

		foreach ($items as $key => $value) {
			if (is_array($value)) {
				$this->arrayToXML($key, $value, $writer);
			}
			elseif (is_int($key) && is_string($value)) {
				$writer->writeElement($value);
			}
			else {
				$writer->writeElement($key, $value);
			}
		}

		$writer->endElement();

		if (func_num_args() === 2) {
			return $writer->outputMemory();
		}
		return null;
	}

	/**
	 * @throws RuntimeException if not found
	 * @return File
	 */
	protected function getLayout() {
		$layoutCommon    = (new File('private/src/common/'))->attach($this->modeSettings['layout']);
		$layoutArea      = (new File('private/src/area/'.$this->request->getArea()))->attach($this->modeSettings['layout']);

		if ($layoutArea->isFile()) {
			return $layoutArea;
		}
		elseif ($layoutCommon->isFile()) {
			return $layoutCommon;
		}
		else {
			throw new RuntimeException('layout `'.$this->modeSettings['layout'].'` not found');
		}
	}

	/**
	 * get form status INIT, RESTORED, REPEATED or null (ACCEPTED or REJECTED)
	 * @return null|string
	 */
	protected function analyzeStatus() {
		if (!count($_POST)) {
			if ($this->getStoredFormData() !== null) {
				return AbstractPage::STATUS_RESTORED;
			}
			return AbstractPage::STATUS_INIT;
		}
		if ($this->isStoredFormClose($_POST['formId'])) {
			return AbstractPage::STATUS_REPEATED;
		}
		return null;
	}

	/**
	 * @param  int $code
	 * @param  string $message
	 * @return false
	 */
	protected function error($code, $message) {
		if (isset($this->modeSettings['error'.$code])) {
			$errorSettings = $this->modeSettings['error'.$code];

			# pipe to url
			if (isset($errorSettings['pipe'])) {
				$this->setLocation(new Url($errorSettings['pipe']));
				return false;
			}

		}

		# default error-output
		$this->setMimeType('text/plain');
		echo '['.$code.'] '.$message;
		return false;
	}

	/**
	 * @return array|null
	 */
	protected function getStoredFormData() {
		$session = new Bucket($_SESSION);
		$form = $session->get('form', $this->request->getArea().'#'.$this->request->getPage());
		if ($form === null) {
			return null;
		}
		else {
			return $form['data'];
		}
	}

	/**
	 * @param  string $formId
	 * @return bool
	 */
	protected function isStoredFormClose($formId) {
		$form = $this->getStoredFormData();
		if ($form === null || $form['data']['formId'] !== $formId) {
			return false;
		}
		return $form['close'];
	}

	/**
	 * @param  array $formData
	 * @param  bool $close
	 * @return SiteModeHandler
	 */
	protected function setStoredForm($formData, $close = false) {
		$session = new Bucket($_SESSION);
		$form = array(
				'data'  => $formData,
				'close' => $close
		);
		$session->set('form', $this->request->getArea().'#'.$this->request->getPage(), $form);
		$_SESSION = $session->toArray();
		return $this;
	}

	/**
	 * @param  string $formId
	 * @return SiteModeHandler
	 */
	protected function closeStoredForm($formId) {
		$formData = $this->getStoredFormData();
		if ($formData !== null) {
			if ($formData['formId'] === $formId) {
				$this->setStoredForm($formData, true);
			}
		}
		return $this;
	}

}