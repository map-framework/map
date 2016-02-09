<?php

use store\data\net\Url;

class UrlTest extends PHPUnit_Framework_TestCase {

	const URL_FULL 					= 'ftp://frog:quak@test.ninja:33/dir/file.html?the#end';
	const URL_FULL_SCHEME   = 'ftp';
	const URL_FULL_USER     = 'frog';
	const URL_FULL_PASS     = 'quak';
	const URL_FULL_HOST     = 'test.ninja';
	const URL_FULL_PORT     = 33;
	const URL_FULL_PATH     = '/dir/file.html';
	const URL_FULL_QUERY    = 'the';
	const URL_FULL_FRAGMENT = 'end';

	const URL_NONE 					= '';
	const URL_NONE_SCHEME   = null;
	const URL_NONE_USER     = null;
	const URL_NONE_PASS     = null;
	const URL_NONE_HOST     = null;
	const URL_NONE_PORT     = null;
	const URL_NONE_PATH     = null;
	const URL_NONE_QUERY    = null;
	const URL_NONE_FRAGMENT = null;

	# constant: URL_FULL
	
	public function testGetScheme_full() {
		$this->assertEquals(self::URL_FULL_SCHEME, (new Url(self::URL_FULL))->getScheme());
	}

	public function testGetUser_full() {
		$this->assertEquals(self::URL_FULL_USER, (new Url(self::URL_FULL))->getUser());
	}

	public function testGetPass_full() {
		$this->assertEquals(self::URL_FULL_PASS, (new Url(self::URL_FULL))->getPass());
	}

	public function testGetHost_full() {
		$this->assertEquals(self::URL_FULL_HOST, (new Url(self::URL_FULL))->getHost());
	}

	public function testGetPort_full() {
		$this->assertEquals(self::URL_FULL_PORT, (new Url(self::URL_FULL))->getPort());
	}

	public function testGetPath_full() {
		$this->assertEquals(self::URL_FULL_PATH, (new Url(self::URL_FULL))->getPath());
	}

	public function testGetQuery_full() {
		$this->assertEquals(self::URL_FULL_QUERY, (new Url(self::URL_FULL))->getQuery());
	}

	public function testGetFragment_full() {
		$this->assertEquals(self::URL_FULL_FRAGMENT, (new Url(self::URL_FULL))->getFragment());
	}

	public function testSetGet_full() {
		$this->assertEquals(self::URL_FULL, (new Url(self::URL_FULL))->get());
	}

	public function testSetAllAndGet_full() {
		$url = (new Url())
				->setScheme(self::URL_FULL_SCHEME)
				->setUser(self::URL_FULL_USER)
				->setPass(self::URL_FULL_PASS)
				->setHost(self::URL_FULL_HOST)
				->setPort(self::URL_FULL_PORT)
				->setPath(self::URL_FULL_PATH)
				->setQuery(self::URL_FULL_QUERY)
				->setFragment(self::URL_FULL_FRAGMENT);
		$this->assertEquals(self::URL_FULL, $url->get());
	}

	#constant: URL_NONE
	
	public function testGetScheme_none() {
		$this->assertEquals(self::URL_NONE_SCHEME, (new Url(self::URL_NONE))->getScheme());
	}

	public function testGetUser_none() {
		$this->assertEquals(self::URL_NONE_USER, (new Url(self::URL_NONE))->getUser());
	}

	public function testGetPass_none() {
		$this->assertEquals(self::URL_NONE_PASS, (new Url(self::URL_NONE))->getPass());
	}

	public function testGetHost_none() {
		$this->assertEquals(self::URL_NONE_HOST, (new Url(self::URL_NONE))->getHost());
	}

	public function testGetPort_none() {
		$this->assertEquals(self::URL_NONE_PORT, (new Url(self::URL_NONE))->getPort());
	}

	public function testGetPath_none() {
		$this->assertEquals(self::URL_NONE_PATH, (new Url(self::URL_NONE))->getPath());
	}

	public function testGetQuery_none() {
		$this->assertEquals(self::URL_NONE_QUERY, (new Url(self::URL_NONE))->getQuery());
	}

	public function testGetFragment_none() {
		$this->assertEquals(self::URL_NONE_FRAGMENT, (new Url(self::URL_NONE))->getFragment());
	}

	public function testSetGet_none() {
		$this->assertEquals(self::URL_NONE, (new Url(self::URL_NONE))->get());
	}
	
	public function testSetScheme_none() {
		$this->assertEquals(self::URL_NONE_SCHEME, (new Url())->setScheme(self::URL_NONE_SCHEME)->getScheme());
	}
	
	public function testSetUser_none() {
		$this->assertEquals(self::URL_NONE_USER, (new Url())->setUser(self::URL_NONE_USER)->getUser());
	}
	
	public function testSetPass_none() {
		$this->assertEquals(self::URL_NONE_PASS, (new Url())->setPass(self::URL_NONE_PASS)->getPass());
	}
	
	public function testSetHost_none() {
		$this->assertEquals(self::URL_NONE_HOST, (new Url())->setHost(self::URL_NONE_HOST)->getHost());
	}
	
	public function testSetPort_none() {
		$this->assertEquals(self::URL_NONE_PORT, (new Url())->setPort(self::URL_NONE_PORT)->getPort());
	}
	
	public function testSetPath_none() {
		$this->assertEquals(self::URL_NONE_PATH, (new Url())->setPath(self::URL_NONE_PATH)->getPath());
	}
	
	public function testSetQuery_none() {
		$this->assertEquals(self::URL_NONE_QUERY, (new Url())->setQuery(self::URL_NONE_QUERY)->getQuery());
	}
	
	public function testSetFragment_none() {
		$this->assertEquals(self::URL_NONE_FRAGMENT, (new Url())->setFragment(self::URL_NONE_FRAGMENT)->getFragment());
	}

}