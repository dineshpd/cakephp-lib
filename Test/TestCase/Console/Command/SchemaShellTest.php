<?php
/**
 * SchemaShellTest Test file
 *
 * PHP 5
 *
 * CakePHP : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc.
 * @link          http://cakephp.org CakePHP Project
 * @package       Cake.Test.Case.Console.Command
 * @since         CakePHP v 1.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\Console\Command;

use Cake\Console\Command\SchemaShell;
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Model\ConnectionManager;
use Cake\Model\Schema;
use Cake\TestSuite\TestCase;
use Cake\Utility\File;
use Cake\Utility\Inflector;

/**
 * Test for Schema database management
 *
 * @package       Cake.Test.Case.Console.Command
 */
class SchemaShellTestSchema extends Schema {

/**
 * name property
 *
 * @var string 'MyApp'
 */
	public $name = 'SchemaShellTest';

/**
 * connection property
 *
 * @var string 'test'
 */
	public $connection = 'test';

/**
 * comments property
 *
 * @var array
 */
	public $comments = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => 0, 'key' => 'primary'),
		'post_id' => array('type' => 'integer', 'null' => false, 'default' => 0),
		'user_id' => array('type' => 'integer', 'null' => false),
		'title' => array('type' => 'string', 'null' => false, 'length' => 100),
		'comment' => array('type' => 'text', 'null' => false, 'default' => null),
		'published' => array('type' => 'string', 'null' => true, 'default' => 'N', 'length' => 1),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => true)),
	);

/**
 * posts property
 *
 * @var array
 */
	public $articles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => 0, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => ''),
		'title' => array('type' => 'string', 'null' => false, 'default' => 'Title'),
		'body' => array('type' => 'text', 'null' => true, 'default' => null),
		'summary' => array('type' => 'text', 'null' => true),
		'published' => array('type' => 'string', 'null' => true, 'default' => 'Y', 'length' => 1),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => true)),
	);
}

/**
 * SchemaShellTest class
 *
 * @package       Cake.Test.Case.Console.Command
 */
class SchemaShellTest extends TestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('core.article', 'core.user', 'core.post', 'core.auth_user', 'core.author',
		'core.comment', 'core.test_plugin_comment'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->markTestIncomplete('SchemaShell is not working as models are not working.');

		$out = $this->getMock('Cake\Console\ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('Cake\Console\ConsoleInput', array(), array(), '', false);
		$this->Shell = $this->getMock(
			'Cake\Console\Command\SchemaShell',
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop'),
			array($out, $out, $in)
		);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		if (!empty($this->file) && $this->file instanceof File) {
			$this->file->delete();
			unset($this->file);
		}
	}

/**
 * test startup method
 *
 * @return void
 */
	public function testStartup() {
		$this->Shell->startup();
		$this->assertTrue(isset($this->Shell->Schema));
		$this->assertTrue(is_a($this->Shell->Schema, 'Cake\Model\Schema'));
		$this->assertEquals(Inflector::camelize(Inflector::slug(APP_DIR)), $this->Shell->Schema->name);
		$this->assertEquals('schema.php', $this->Shell->Schema->file);

		$this->Shell->Schema = null;
		$this->Shell->params = array(
			'name' => 'TestSchema'
		);
		$this->Shell->startup();
		$this->assertEquals('TestSchema', $this->Shell->Schema->name);
		$this->assertEquals('test_schema.php', $this->Shell->Schema->file);
		$this->assertEquals('default', $this->Shell->Schema->connection);
		$this->assertEquals(APP . 'Config/Schema', $this->Shell->Schema->path);

		$this->Shell->Schema = null;
		$this->Shell->params = array(
			'file' => 'other_file.php',
			'connection' => 'test',
			'path' => '/test/path'
		);
		$this->Shell->startup();
		$this->assertEquals(Inflector::camelize(Inflector::slug(APP_DIR)), $this->Shell->Schema->name);
		$this->assertEquals('other_file.php', $this->Shell->Schema->file);
		$this->assertEquals('test', $this->Shell->Schema->connection);
		$this->assertEquals('/test/path', $this->Shell->Schema->path);
	}

/**
 * Test View - and that it dumps the schema file to stdout
 *
 * @return void
 */
	public function testView() {
		$this->Shell->startup();
		$this->Shell->Schema->path = APP . 'Config/Schema';
		$this->Shell->params['file'] = 'i18n.php';
		$this->Shell->expects($this->once())->method('_stop');
		$this->Shell->expects($this->once())->method('out');
		$this->Shell->view();
	}

/**
 * test that view() can find plugin schema files.
 *
 * @return void
 */
	public function testViewWithPlugins() {
		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		));
		Plugin::load('TestPlugin');
		$this->Shell->args = array('TestPlugin.schema');
		$this->Shell->startup();
		$this->Shell->expects($this->exactly(2))->method('_stop');
		$this->Shell->expects($this->atLeastOnce())->method('out');
		$this->Shell->view();

		$this->Shell->args = array();
		$this->Shell->params = array('plugin' => 'TestPlugin');
		$this->Shell->startup();
		$this->Shell->view();

		App::build();
		Plugin::unload();
	}

/**
 * test dump() with sql file generation
 *
 * @return void
 */
	public function testDumpWithFileWriting() {
		$this->Shell->params = array(
			'name' => 'i18n',
			'connection' => 'test',
			'write' => TMP . 'tests/i18n.sql'
		);
		$this->Shell->expects($this->once())->method('_stop');
		$this->Shell->startup();
		$this->Shell->dump();

		$this->file = new File(TMP . 'tests/i18n.sql');
		$contents = $this->file->read();
		$this->assertRegExp('/DROP TABLE/', $contents);
		$this->assertRegExp('/CREATE TABLE.*?i18n/', $contents);
		$this->assertRegExp('/id/', $contents);
		$this->assertRegExp('/model/', $contents);
		$this->assertRegExp('/field/', $contents);
		$this->assertRegExp('/locale/', $contents);
		$this->assertRegExp('/foreign_key/', $contents);
		$this->assertRegExp('/content/', $contents);
	}

/**
 * test that dump() can find and work with plugin schema files.
 *
 * @return void
 */
	public function testDumpFileWritingWithPlugins() {
		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		));
		Plugin::load('TestPlugin');
		$this->Shell->args = array('TestPlugin.TestPluginApp');
		$this->Shell->params = array(
			'connection' => 'test',
			'write' => TMP . 'tests/dump_test.sql'
		);
		$this->Shell->startup();
		$this->Shell->expects($this->once())->method('_stop');
		$this->Shell->dump();

		$this->file = new File(TMP . 'tests/dump_test.sql');
		$contents = $this->file->read();

		$this->assertRegExp('/CREATE TABLE.*?test_plugin_acos/', $contents);
		$this->assertRegExp('/id/', $contents);
		$this->assertRegExp('/model/', $contents);

		$this->file->delete();
		App::build();
		Plugin::unload();
	}

/**
 * test generate with snapshot generation
 *
 * @return void
 */
	public function testGenerateSnapshot() {
		$this->Shell->path = TMP;
		$this->Shell->params['file'] = 'schema.php';
		$this->Shell->params['force'] = false;
		$this->Shell->args = array('snapshot');
		$this->Shell->Schema = $this->getMock('Cake\Model\Schema');
		$this->Shell->Schema->expects($this->at(0))->method('read')->will($this->returnValue(array('schema data')));
		$this->Shell->Schema->expects($this->at(0))->method('write')->will($this->returnValue(true));

		$this->Shell->Schema->expects($this->at(1))->method('read');
		$this->Shell->Schema->expects($this->at(1))->method('write')->with(array('schema data', 'file' => 'schema_0.php'));

		$this->Shell->generate();
	}

/**
 * test generate without a snapshot.
 *
 * @return void
 */
	public function testGenerateNoOverwrite() {
		touch(TMP . 'schema.php');
		$this->Shell->params['file'] = 'schema.php';
		$this->Shell->params['force'] = false;
		$this->Shell->args = array();

		$this->Shell->expects($this->once())->method('in')->will($this->returnValue('q'));
		$this->Shell->Schema = $this->getMock('Cake\Model\Schema');
		$this->Shell->Schema->path = TMP;
		$this->Shell->Schema->expects($this->never())->method('read');

		$result = $this->Shell->generate();
		unlink(TMP . 'schema.php');
	}

/**
 * test generate with overwriting of the schema files.
 *
 * @return void
 */
	public function testGenerateOverwrite() {
		touch(TMP . 'schema.php');
		$this->Shell->params['file'] = 'schema.php';
		$this->Shell->params['force'] = false;
		$this->Shell->args = array();

		$this->Shell->expects($this->once())->method('in')->will($this->returnValue('o'));

		$this->Shell->expects($this->at(2))->method('out')
			->with(new \PHPUnit_Framework_Constraint_PCREMatch('/Schema file:\s[a-z\.]+\sgenerated/'));

		$this->Shell->Schema = $this->getMock('Cake\Model\Schema');
		$this->Shell->Schema->path = TMP;
		$this->Shell->Schema->expects($this->once())->method('read')->will($this->returnValue(array('schema data')));
		$this->Shell->Schema->expects($this->once())->method('write')->will($this->returnValue(true));

		$this->Shell->Schema->expects($this->once())->method('read');
		$this->Shell->Schema->expects($this->once())->method('write')
			->with(array('schema data', 'file' => 'schema.php'));

		$this->Shell->generate();
		unlink(TMP . 'schema.php');
	}

/**
 * test that generate() can read plugin dirs and generate schema files for the models
 * in a plugin.
 *
 * @return void
 */
	public function testGenerateWithPlugins() {
		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		), App::RESET);
		Plugin::load('TestPlugin');

		$this->db->cacheSources = false;
		$this->Shell->params = array(
			'plugin' => 'TestPlugin',
			'connection' => 'test',
			'force' => false
		);
		$this->Shell->startup();
		$this->Shell->Schema->path = TMP . 'tests/';

		$this->Shell->generate();
		$this->file = new File(TMP . 'tests/schema.php');
		$contents = $this->file->read();

		$this->assertRegExp('/class TestPluginSchema/', $contents);
		$this->assertRegExp('/public \$posts/', $contents);
		$this->assertRegExp('/public \$auth_users/', $contents);
		$this->assertRegExp('/public \$authors/', $contents);
		$this->assertRegExp('/public \$test_plugin_comments/', $contents);
		$this->assertNotRegExp('/public \$users/', $contents);
		$this->assertNotRegExp('/public \$articles/', $contents);
		Plugin::unload();
	}

/**
 * test generate with specific models
 *
 * @return void
 */
	public function testGenerateModels() {
		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		), App::RESET);
		Plugin::load('TestPlugin');

		$this->db->cacheSources = false;
		$this->Shell->params = array(
			'plugin' => 'TestPlugin',
			'connection' => 'test',
			'models' => 'TestPluginComment',
			'force' => false,
			'overwrite' => true
		);
		$this->Shell->startup();
		$this->Shell->Schema->path = TMP . 'tests/';

		$this->Shell->generate();
		$this->file = new File(TMP . 'tests/schema.php');
		$contents = $this->file->read();

		$this->assertRegExp('/class TestPluginSchema/', $contents);
		$this->assertRegExp('/public \$test_plugin_comments/', $contents);
		$this->assertNotRegExp('/public \$authors/', $contents);
		$this->assertNotRegExp('/public \$auth_users/', $contents);
		$this->assertNotRegExp('/public \$posts/', $contents);
		Plugin::unload();
	}

/**
 * Test schema run create with no table args.
 *
 * @return void
 */
	public function testCreateNoArgs() {
		$this->Shell->params = array(
			'connection' => 'test'
		);
		$this->Shell->args = array('i18n');
		$this->Shell->startup();
		$this->Shell->expects($this->any())->method('in')->will($this->returnValue('y'));
		$this->Shell->create();

		$db = ConnectionManager::getDataSource('test');

		$db->cacheSources = false;
		$sources = $db->listSources();
		$this->assertTrue(in_array($db->config['prefix'] . 'i18n', $sources));

		$schema = new i18nSchema();
		$db->execute($db->dropSchema($schema));
	}

/**
 * Test schema run create with no table args.
 *
 * @return void
 */
	public function testCreateWithTableArgs() {
		$db = ConnectionManager::getDataSource('test');
		$sources = $db->listSources();
		if (in_array('acos', $sources)) {
			$this->markTestSkipped('acos table already exists, cannot try to create it again.');
		}
		$this->Shell->params = array(
			'connection' => 'test',
			'name' => 'DbAcl',
			'path' => APP . 'Config/Schema'
		);
		$this->Shell->args = array('DbAcl', 'acos');
		$this->Shell->startup();
		$this->Shell->expects($this->any())->method('in')->will($this->returnValue('y'));
		$this->Shell->create();

		$db = ConnectionManager::getDataSource('test');
		$db->cacheSources = false;
		$sources = $db->listSources();
		$this->assertTrue(in_array($db->config['prefix'] . 'acos', $sources), 'acos should be present.');
		$this->assertFalse(in_array($db->config['prefix'] . 'aros', $sources), 'aros should not be found.');
		$this->assertFalse(in_array('aros_acos', $sources), 'aros_acos should not be found.');

		$schema = new DbAclSchema();
		$db->execute($db->dropSchema($schema, 'acos'));
	}

/**
 * test run update with a table arg.
 *
 * @return void
 */
	public function testUpdateWithTable() {
		$this->Shell = $this->getMock(
			'SchemaShell',
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_run'),
			array(&$this->Dispatcher)
		);

		$this->Shell->params = array(
			'connection' => 'test',
			'force' => true
		);
		$this->Shell->args = array('SchemaShellTest', 'articles');
		$this->Shell->startup();
		$this->Shell->expects($this->any())->method('in')->will($this->returnValue('y'));
		$this->Shell->expects($this->once())->method('_run')
			->with($this->arrayHasKey('articles'), 'update', $this->isInstanceOf('Cake\Model\Schema'));

		$this->Shell->update();
	}

/**
 * test that the plugin param creates the correct path in the schema object.
 *
 * @return void
 */
	public function testPluginParam() {
		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		));
		Plugin::load('TestPlugin');
		$this->Shell->params = array(
			'plugin' => 'TestPlugin',
			'connection' => 'test'
		);
		$this->Shell->startup();
		$expected = CAKE . 'Test/TestApp/Plugin/TestPlugin/Config/Schema';
		$this->assertEquals($expected, $this->Shell->Schema->path);
		Plugin::unload();
	}

/**
 * test that underscored names also result in CamelCased class names
 *
 * @return void
 */
	public function testName() {
		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		));
		Plugin::load('TestPlugin');
		$this->Shell->params = array(
			'plugin' => 'TestPlugin',
			'connection' => 'test',
			'name' => 'custom_name',
			'force' => false,
			'overwrite' => true,
		);
		$this->Shell->startup();
		if (file_exists($this->Shell->Schema->path . DS . 'custom_name.php')) {
			unlink($this->Shell->Schema->path . DS . 'custom_name.php');
		}
		$this->Shell->generate();

		$contents = file_get_contents($this->Shell->Schema->path . DS . 'custom_name.php');
		$this->assertRegExp('/class CustomNameSchema/', $contents);
		unlink($this->Shell->Schema->path . DS . 'custom_name.php');
		Plugin::unload();
	}

/**
 * test that using Plugin.name with write.
 *
 * @return void
 */
	public function testPluginDotSyntaxWithCreate() {
		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		));
		Plugin::load('TestPlugin');
		$this->Shell->params = array(
			'connection' => 'test'
		);
		$this->Shell->args = array('TestPlugin.TestPluginApp');
		$this->Shell->startup();
		$this->Shell->expects($this->any())->method('in')->will($this->returnValue('y'));
		$this->Shell->create();

		$db = ConnectionManager::getDataSource('test');
		$sources = $db->listSources();
		$this->assertTrue(in_array($db->config['prefix'] . 'test_plugin_acos', $sources));

		$schema = new TestPluginAppSchema();
		$db->execute($db->dropSchema($schema, 'test_plugin_acos'));
		Plugin::unload();
	}
}
