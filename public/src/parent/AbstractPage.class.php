<?php
namespace parent;

use exception\request\RejectedException;
use RuntimeException;

abstract class AbstractPage {

	/**
	 * initialized question
	 */
	const STATUS_INIT     = 'INIT';

	/**
	 * restored / reloaded question
	 */
	const STATUS_RESTORED = 'RESTORED';

	/**
	 * negative reply
	 * @see exception\request\AcceptedException
	 */
	const STATUS_REJECTED = 'REJECTED';

	/**
	 * positive reply
	 * @see exception\request\AcceptedException
	 */
	const STATUS_ACCEPTED = 'ACCEPTED';

	/**
	 * neutral reply
	 * @see exception\request\AcceptedException
	 */
	const STATUS_REPEATED = 'REPEATED';

	/**
	 * @var string[]
	 */
	protected $formData   = array();

	/**
	 * @var string[]
	 */
	private $expect       = array();

	/**
	 * response parameter list
	 * @var string[]
	 */
	private $paramList    = array();

	/**
	 * check if user is entitled
	 * @return bool
	 */
	abstract public function access();

	/**
	 * call each request
	 * @return void
	 */
	abstract public function setUp();

	/**
	 * @param  string[] $formData
	 * @throws RuntimeException if formData isn't an array
	 * @throws RejectedException if formData invalid
	 */
	public function __construct($formData) {
		if (!is_array($formData)) {
			throw new RuntimeException('expect an array in formData');
		}

		foreach ($this->expect as $formItemName => $pattern) {
			if (!isset($this->formData[$formItemName]) || !preg_match('/^'.$pattern.'$/', $this->formData[$formItemName])) {
				throw new RejectedException();
			}
		}

		$this->formData = $formData;
	}

	/**
	 * @param  string $formItemName
	 * @param  string $pattern
	 * @return AbstractPage
	 */
	final protected function addExpect($formItemName, $pattern = '.*') {
		$this->expect[$formItemName] = $pattern;
	}

	/**
	 * add item to response parameter list
	 * @param $param
	 * @return AbstractPage
	 */
	final protected function addParam($param) {
		$this->paramList[] = $param;
		return $this;
	}

	/**
	 * get response parameter list
	 * @return string[]
	 */
	final public function getParamList() {
		return $this->paramList;
	}

}
