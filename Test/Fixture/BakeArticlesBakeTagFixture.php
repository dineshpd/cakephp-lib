<?php
/**
 * BakeCommentFixture
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @package       Cake.Test.Fixture
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 *
 * @package       Cake.Test.Fixture
 */
class BakeArticlesBakeTagFixture extends TestFixture {

/**
 * name property
 *
 * @var string 'ArticlesTag'
 */
	public $name = 'BakeArticlesBakeTag';

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'bake_article_id' => array('type' => 'integer', 'null' => false),
		'bake_tag_id' => array('type' => 'integer', 'null' => false),
		'indexes' => array('UNIQUE_TAG' => array('column' => array('bake_article_id', 'bake_tag_id'), 'unique' => 1))
	);

/**
 * records property
 *
 * @var array
 */
	public $records = array();
}
