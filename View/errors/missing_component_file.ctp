<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake.libs.view.templates.errors
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<h2><?php echo __d('cake', 'Missing Component File'); ?></h2>
<p class="error">
	<strong><?php echo __d('cake', 'Error'); ?>: </strong>
	<?php echo __d('cake', 'The component file was not found.'); ?>
</p>
<p class="error">
	<strong><?php echo __d('cake', 'Error'); ?>: </strong>
	<?php echo __d('cake', 'Create the class %s in file: %s', '<em>' . $class . '</em>', APP_DIR . DS . 'controllers' . DS . 'components' . DS . $file); ?>
</p>
<pre>
&lt;?php
class <?php echo $class;?> extends Component {<br />

}
?&gt;
</pre>
<p class="notice">
	<strong><?php echo __d('cake', 'Notice'); ?>: </strong>
	<?php echo __d('cake', 'If you want to customize this error message, create %s', APP_DIR . DS . 'views' . DS . 'errors' . DS . 'missing_component_file.ctp'); ?>
</p>

<?php echo $this->element('exception_stack_trace'); ?>