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
namespace Cake\Network\Http;

use Cake\Network\Http\Request;
use Cake\Network\Http\Response;

/**
 * Container class for cookies used in Http\Client.
 *
 * Provides cookie jar like features for storing cookies between
 * requests, as well as appending cookies to new requests.
 */
class Cookies {

/**
 * The cookies stored in this jar.
 *
 * @var array
 */
	protected $_cookies = [];

/**
 * Store the cookies from a response.
 *
 * Store the cookies that haven't expired. If a cookie has been expired
 * and is currently stored, it will be removed.
 *
 * @param Response $response The response to read cookies from
 * @param string $url The request URL used for default host/path values.
 * @return void
 */
	public function store(Response $response, $url) {
		$host = parse_url($url, PHP_URL_HOST);
		$path = parse_url($url, PHP_URL_PATH);
		$path = $path ?: '/';

		$cookies = $response->cookies();
		foreach ($cookies as $name => $cookie) {
			$expires = isset($cookie['expires']) ? $cookie['expires'] : false;
			if ($expires) {
				$expires = \DateTime::createFromFormat('D, j-M-Y H:i:s e', $expires);
			}
			if ($expires && $expires->getTimestamp() <= time()) {
				continue;
			}
			if (empty($cookie['domain'])) {
				$cookie['domain'] = $host;
			}
			if (empty($cookie['path'])) {
				$cookie['path'] = $path;
			}
			$this->_cookies[] = $cookie;
		}
	}

/**
 * Get stored cookies for a url.
 *
 * Finds matching stored cookies and returns a simple array
 * of name => value
 *
 * @param string $url The url to find cookies for.
 * @return arraty
 */
	public function get($url) {
		$path = parse_url($url, PHP_URL_PATH) ?: '/';
		$host = parse_url($url, PHP_URL_HOST);
		$scheme = parse_url($url, PHP_URL_SCHEME);

		$out = [];
		foreach ($this->_cookies as $cookie) {
			if ($scheme === 'http' && !empty($cookie['secure'])) {
				continue;
			}
			if (strpos($path, $cookie['path']) !== 0) {
				continue;
			}
			$leadingDot = $cookie['domain'][0] === '.';
			if (!$leadingDot && $host !== $cookie['domain']) {
				continue;
			}
			if ($leadingDot) {
				$pattern = '/' . preg_quote(substr($cookie['domain'], 1), '/') . '$/';
				if (!preg_match($pattern, $host)) {
					continue;
				}
			}
			$out[$cookie['name']] = $cookie['value'];
		}
		return $out;
	}

/**
 * Get all the stored cookies.
 *
 * @return array
 */
	public function getAll() {
		return $this->_cookies;
	}

}
