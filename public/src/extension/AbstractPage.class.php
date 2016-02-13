<?php
namespace extension;

use store\Bucket;
use xml\Node;
use xml\Tree;

abstract class AbstractPage {

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
	 * @var string[]
	 */
	private $expect = array();

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
	public $formData = null;

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
		$this->addExpect('formId');
		$this->request = $request;
		$this->config  = $config;

		$this->response = new Tree('document');
		$this->formData = $this->response->getRootNode()->addChild(new Node('form'));
	}

	/**
	 * @param  string $name
	 * @param  string $value
	 * @return AbstractPage this
	 */
	final public function setFormData($name, $value) {
		$this->formData
				->addChild(new Node($name))
				->setContent($value);
		return $this;
	}

	/**
	 * @param  string $formItemName
	 * @param  string $pattern
	 * @return AbstractPage this
	 */
	final protected function addExpect($formItemName, $pattern = '.*') {
		$this->expect[$formItemName] = $pattern;
	}

	/**
	 * @return bool
	 */
	public function checkExpectation() {
		foreach ($this->expect as $formItemName => $pattern) {
			if (!isset($this->request[$formItemName]) || !preg_match('/^'.$pattern.'$/', $this->request[$formItemName])) {
				return false;
			}
		}
		return true;
	}

}
