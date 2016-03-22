<?php
namespace extension;

use handler\mode\SiteModeHandler;
use store\Bucket;
use xml\Node;
use xml\Tree;

abstract class AbstractSitePage {

	/**
	 * initialized question
	 */
	const STATUS_INIT = 'INIT';

	/**
	 * restored / reloaded question
	 */
	const STATUS_RESTORED = 'RESTORED';

	/**
	 * negative reply
	 *
	 * @see exception\request\AcceptedException
	 */
	const STATUS_REJECTED = 'REJECTED';

	/**
	 * positive reply
	 *
	 * @see exception\request\AcceptedException
	 */
	const STATUS_ACCEPTED = 'ACCEPTED';

	/**
	 * neutral reply
	 *
	 * @see exception\request\AcceptedException
	 */
	const STATUS_REPEATED = 'REPEATED';

	/**
	 * @var Bucket
	 */
	protected $config = null;

	/**
	 * @var array
	 */
	protected $request = array();

	/**
	 * @var Tree
	 */
	public $response = null;

	/**
	 * @var Node
	 */
	public $responseForm = null;

	/**
	 * check if user is entitled
	 *
	 * @return bool
	 */
	abstract public function access();

	/**
	 * call if nothing submitted
	 *
	 * @return void
	 */
	abstract public function setUp();

	/**
	 * array[name:string] = pattern:string
	 *
	 * @see    AbstractSitePage::checkExpectation
	 * @return array (see above)
	 */
	abstract public function getExpectList();

	/**
	 * call if submitted
	 *
	 * @return bool
	 */
	abstract public function check();

	/**
	 * @param Bucket $config
	 * @param array  $request
	 */
	public function __construct(Bucket $config, $request) {
		$this->request = $request;
		$this->config  = $config;

		$this->response     = new Tree('document');
		$this->responseForm = $this->response->getRootNode()->addChild(new Node('form'));
	}

	/**
	 * @param  string $name
	 * @param  string $value
	 * @return AbstractSitePage this
	 */
	final public function setResponseFormItem($name, $value) {
		$this->responseForm
				->addChild(new Node($name))
				->setContent($value);
		return $this;
	}

	/**
	 * Use this method in <i>check</i> to indicate: the request was <b>successful</b>.
	 * Return this response!
	 *
	 * @param  null|string $reason
	 * @return true
	 */
	final public function accept($reason = null) {
		if ($reason !== null) {
			$this->responseForm->setAttribute('reason', $reason);
		}
		return true;
	}

	/**
	 * Use this method in <i>check</i> to indicate: the request has <b>failed</b>.
	 * Return this response!
	 *
	 * @param  null|string $reason
	 * @return false
	 */
	final public function reject($reason = null) {
		if ($reason !== null) {
			$this->responseForm->setAttribute('reason', $reason);
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function checkExpectation() {
		$expectList           = $this->getExpectList();
		$expectList['formId'] = SiteModeHandler::FORM_ID_PATTERN;

		foreach ($expectList as $name => $pattern) {
			if (!isset($this->request[$name]) || !preg_match('/^'.$pattern.'$/', $this->request[$name])) {
				return false;
			}
		}
		return true;
	}

}
