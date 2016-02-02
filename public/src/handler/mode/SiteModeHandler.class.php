<?php
namespace handler\mode;

use exception\request\AcceptedException;
use exception\request\RejectedException;
use RuntimeException;
use parent\AbstractPage;
use store\Bucket;
use store\data\File;
use store\data\net\MAPUrl;
use store\data\net\Url;

class SiteModeHandler extends AbstractModeHandler {

	/**
	 * @var MAPUrl
	 */
	private $request;

	/**
	 * @see    AbstractModeHandler::handle()
	 * @param  MAPUrl $request
	 * @param  array $settings
	 * @return bool
	 */
	public function handle(MAPUrl $request, $settings)	{
		$this->request = $request;

		$page           = strtolower($request->getPage());
		$pageClassName  = ucfirst($page).'Page';
		$pageNamespace  = 'area\\'.$request->getArea().'\logic\site\\'.$pageClassName;
		$pageXSLFile    = new File('private/area/'.$request->getArea().'/app/view/site/'.$page.'.xsl');

		if (!class_exists($pageNamespace) || !$pageXSLFile->isFile()) {
			return $this->error($settings, 404, 'Not Found');
		}

		$formStatus = $this->analyzeStatus();

		if ($formStatus === AbstractPage::STATUS_RESTORED) {
			$formData = (new Bucket($_SESSION))->get('form', $request->getArea().'#'.$request->getPage())['data'];
		}
		elseif ($formStatus === null) {
			$formData = $_POST;
		}
		else {
			$formData = array();
		}

		try {
			$pageObject = new $pageNamespace($formData);
			if (!($pageObject instanceof AbstractPage)) {
				throw new RuntimeException('class `'.$pageNamespace.'` isn\'t instance of `parent\AbstractPage`');
			}

			if (!$pageObject->access()) {
				return $this->error($settings, 403, 'Forbidden');
			}

			if ($formStatus !== null) {
				$pageObject->setUp();
			}
			else {
				if ($pageObject->check() === true) {
					$formStatus = AbstractPage::STATUS_ACCEPTED;
				}
				else {
					$formStatus = AbstractPage::STATUS_REJECTED;
				}
			}
		}
		catch (AcceptedException $e) {
			$formStatus = AbstractPage::STATUS_ACCEPTED;
		}
		catch (RejectedException $e) {
			$formStatus = AbstractPage::STATUS_REJECTED;
		}

		if ($formStatus === AbstractPage::STATUS_ACCEPTED) {
			$this->closeStoredForm($formData['formId']);
		}
		elseif ($formStatus === AbstractPage::STATUS_REJECTED) {
			$this->setStoredForm($formData);
		}

		# @todo load XSLT
		# @todo write tests

		return true;
	}

	/**
	 * get form status INIT, RESTORED, REPEATED or null (ACCEPTED or REJECTED)
	 * @return null|string
	 */
	final protected function analyzeStatus() {
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
	 * @param  array $settings
	 * @param  int $code
	 * @param  string $message
	 * @return false
	 */
	protected function error($settings, $code, $message) {
		if (isset($settings, $settings['error'.$code])) {
			$errorSettings = $settings['error'.$code];

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
	final protected function getStoredFormData() {
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
	final protected function isStoredFormClose($formId) {
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
	final protected function setStoredForm($formData, $close = false) {
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
	final protected function closeStoredForm($formId) {
		$formData = $this->getStoredFormData();
		if ($formData !== null) {
			if ($formData['formId'] === $formId) {
				$this->setStoredForm($formData, true);
			}
		}
		return $this;
	}

}