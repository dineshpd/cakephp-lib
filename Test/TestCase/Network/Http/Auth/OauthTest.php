<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 3.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\Network\Http\Auth;

use Cake\Network\Http\Auth\Oauth;
use Cake\Network\Http\Request;
use Cake\TestSuite\TestCase;

/**
 * Oauth test.
 */
class OauthTest extends TestCase {

/**
 * @expectedException Cake\Error\Exception
 */
	public function testExceptionUnknownSigningMethod() {
		$auth = new Oauth();
		$creds = [
			'consumerSecret' => 'it is secret',
			'consumerKey' => 'a key',
			'token' => 'a token value',
			'tokenSecret' => 'also secret',
			'method' => 'silly goose',
		];
		$request = new Request();
		$auth->authentication($request, $creds);
	}

/**
 * Test plain-text signing.
 *
 * @return void
 */
	public function testPlainTextSigning() {
		$auth = new Oauth();
		$creds = [
			'consumerSecret' => 'it is secret',
			'consumerKey' => 'a key',
			'token' => 'a token value',
			'tokenSecret' => 'also secret',
			'method' => 'plaintext',
		];
		$request = new Request();
		$auth->authentication($request, $creds);

		$result = $request->header('Authorization');
		$this->assertContains('Oauth', $result);
		$this->assertContains('oauth_version="1.0"', $result);
		$this->assertContains('oauth_token="a%20token%20value"', $result);
		$this->assertContains('oauth_consumer_key="a%20key"', $result);
		$this->assertContains('oauth_signature_method="PLAINTEXT"', $result);
		$this->assertContains('oauth_signature="it%20is%20secret%26also%20secret"', $result);
		$this->assertContains('oauth_timestamp=', $result);
		$this->assertContains('oauth_nonce=', $result);
	}

	public function testHmacSigning() {
		$this->markTestIncomplete();
	}

	public function testRsaSha1Signing() {
		$this->markTestIncomplete();
	}

}
