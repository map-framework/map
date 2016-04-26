<?php

use store\data\net\Url;

class UrlTest extends PHPUnit_Framework_TestCase {

	const URL_FULLY_VALUES = array(
			'total'    => 'ftp://frog:quak@example.org:33/dir/file.html?the#end',
			'scheme'   => 'ftp',
			'user'     => 'frog',
			'pass'     => 'quak',
			'host'     => 'example.org',
			'port'     => 33,
			'path'     => '/dir/file.html',
			'query'    => 'the',
			'fragment' => 'end',
	);

	const URL_EMPTY_VALUES = array(
			'total'    => '',
			'scheme'   => null,
			'user'     => null,
			'pass'     => null,
			'host'     => null,
			'port'     => null,
			'path'     => null,
			'query'    => null,
			'fragment' => null,
	);

	public function testGetter_fully_success() {
		$this->callGetter(new Url(self::URL_FULLY_VALUES['total']), self::URL_FULLY_VALUES);
	}

	public function testSetter_fully_success() {
		$this->callSetter(self::URL_FULLY_VALUES);
	}

	public function testGetter_empty_success() {
		$this->callGetter(new Url(), self::URL_EMPTY_VALUES);
	}

	public function testSetter_empty_success() {
		$this->callSetter(self::URL_EMPTY_VALUES);
	}

	private function callGetter(Url $url, array $expect) {
		$this->assertEquals($expect['total'] ?? null, $url->get());
		$this->assertEquals($expect['scheme'] ?? null, $url->getScheme());
		$this->assertEquals($expect['user'] ?? null, $url->getUser());
		$this->assertEquals($expect['pass'] ?? null, $url->getPass());
		$this->assertEquals($expect['host'] ?? null, $url->getHost());
		$this->assertEquals($expect['port'] ?? null, $url->getPort());
		$this->assertEquals($expect['path'] ?? null, $url->getPath());
		$this->assertEquals($expect['query'] ?? null, $url->getQuery());
		$this->assertEquals($expect['fragment'] ?? null, $url->getFragment());
	}

	private function callSetter(array $callList) {
		$this->assertEquals(
				(new Url($callList['total']))->get(),
				$callList['total']
		);
		$this->assertEquals(
				(new Url())
						->setScheme($callList['scheme'] ?? null)
						->setUser($callList['user'] ?? null)
						->setPass($callList['pass'] ?? null)
						->setHost($callList['host'] ?? null)
						->setPort($callList['port'] ?? null)
						->setPath($callList['path'] ?? null)
						->setQuery($callList['query'] ?? null)
						->setFragment($callList['fragment'] ?? null)
						->get(),
				$callList['total']
		);
	}

}
