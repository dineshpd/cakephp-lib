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

use Cake\Network\Http\FormData\Part;

/**
 * Provides an interface for building
 * multipart/form-encoded message bodies.
 *
 * Used by Http\Client to upload POST/PUT data
 * and files.
 *
 */
class FormData implements \Countable {

/**
 * Boundary marker.
 *
 * @var string
 */
	protected $_boundary;

/**
 * The parts in the form data.
 *
 * @var array
 */
	protected $_parts = [];

/**
 * Get the boundary marker
 *
 * @return string
 */
	public function boundary() {
		if ($this->_boundary) {
			return $this->_boundary;
		}
		$this->_boundary = md5(uniqid(time()));
		return $this->_boundary;
	}

/**
 * Method for creating new instances of Part
 *
 * @param string $name The name of the part.
 * @param string $value The value to add.
 */
	public function newPart($name, $value) {
		return new Part($name, $value);
	}

/**
 * Add a new part to the data.
 *
 * The value for a part can be a string, array, int,
 * float, filehandle, or object implementing __toString()
 *
 * If the $value is an array, multiple parts will be added.
 * Files will be read from their current position and saved in memory.
 *
 * @param string $name The name of the part.
 * @param mixed $value The value for the part.
 * @return FormData $this
 */
	public function add($name, $value) {
		if (is_array($value)) {
			$this->addRecursive($name, $value);
		} elseif (is_resource($value)) {
			$this->_parts[] = $this->addFile($name, $value);
		} elseif (is_string($value) && $value[0] === '@') {
			$this->_parts[] = $this->addFile($name, $value);
		} else {
			$this->_parts[] = $this->newPart($name, $value);
		}
		return $this;
	}

	public function addFile($name, $value) {

	}

/**
 * Recursively add data.
 *
 * @param string $name The name to use.
 * @param mixed $value The value to add.
 */
	public function addRecursive($name, $value) {
		foreach ($value as $key => $value) {
			$key = $name . '[' . $key . ']';
			$this->add($key, $value);
		}
	}

/**
 * Returns the count of parts inside this object.
 *
 * @return int
 */
	public function count() {
		return count($this->_parts);
	}

/**
 * Converts the FormData and its parts into a string suitable
 * for use in an HTTP request.
 *
 * @return string
 */
	public function __toString() {
		$boundary = $this->boundary();
		$out = '';
		foreach ($this->_parts as $part) {
			$out .= "--$boundary\r\n";
			$out .= (string)$part;
			$out .= "\r\n";
		}
		$out .= "--$boundary--\r\n\r\n";
		return $out;
	}

}
