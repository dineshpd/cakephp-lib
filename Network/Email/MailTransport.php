<?php
/**
 * Send mail using mail() function
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Network.Email
 * @since         CakePHP(tm) v 2.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Network\Email;

use Cake\Error;

/**
 * Send mail using mail() function
 *
 * @package       Cake.Network.Email
 */
class MailTransport extends AbstractTransport {

/**
 * Send mail
 *
 * @param Cake\Network\Email\Email $email Cake Email
 * @return array
 * @throws SocketException When mail cannot be sent.
 */
	public function send(Email $email) {
		$eol = PHP_EOL;
		if (isset($this->_config['eol'])) {
			$eol = $this->_config['eol'];
		}
		$headers = $email->getHeaders(array('from', 'sender', 'replyTo', 'readReceipt', 'returnPath', 'to', 'cc', 'bcc'));
		$to = $headers['To'];
		unset($headers['To']);
		$headers = $this->_headersToString($headers, $eol);
		$message = implode($eol, $email->message());

		$params = null;
		if (!ini_get('safe_mode')) {
			$params = isset($this->_config['additionalParameters']) ? $this->_config['additionalParameters'] : null;
		}

		$this->_mail($to, $email->subject(), $message, $headers, $params);
		return array('headers' => $headers, 'message' => $message);
	}

/**
 * Wraps internal function mail() and throws exception instead of errors if anything goes wrong
 *
 * @param string $to email's recipient
 * @param string $subject email's subject
 * @param string $message email's body
 * @param string $headers email's custom headers
 * @param string $params additional params for sending email
 * @throws Cake\Error\SocketException if mail could not be sent
 * @return void
 */
	protected function _mail($to, $subject, $message, $headers, $params = null) {
		//@codingStandardsIgnoreStart
		if (!@mail($to, $subject, $message, $headers, $params)) {
			throw new Error\SocketException(__d('cake_dev', 'Could not send email.'));
		}
		//@codingStandardsIgnoreEnd
	}

}
