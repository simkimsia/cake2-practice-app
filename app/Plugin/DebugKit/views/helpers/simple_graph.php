<?php
/**
 * Simple Graph Helper
 *
 * Allows creation and display of extremely simple graphing elements
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org
 * @package       debug_kit
 * @subpackage    debug_kit.views.helpers
 * @since         DebugKit 1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/
App::import('Helper', 'Html');

class SimpleGraphHelper extends AppHelper {
/**
 * Helpers
 *
 * @var array
 */
	var $helpers = array('Html');
/**
 * Default settings to be applied to each Simple Graph
 *
 * Allowed options:
 *
 * - max => (int) Maximum value in the graphs
 * - width => (int)
 * - valueType => string (value, percentage)
 * - style => array
 *
 * @var array
 */
	var $__defaultSettings = array(
		'max' => 100,
		'width' => 350,
		'valueType' => 'value',
	);
/**
 * bar method
 *
 * @param $value Value to be graphed
 * @param $offset how much indentation
 * @param $options Graph options
 * @return string Html graph
 * @access public
 */
	function bar($value, $offset, $options = array()) {
		$settings = array_merge($this->__defaultSettings, $options);
		extract($settings);

		$graphValue = ($value / $max) * $width;
		$graphValue = max(round($graphValue), 1);

		if ($valueType == 'percentage') {
			$graphOffset = 0;
		} else {
			$graphOffset = ($offset / $max) * $width;
			$graphOffset = round($graphOffset);
		}
		return $this->Html->div(
			'debug-kit-graph-bar',
				$this->Html->div(
					'debug-kit-graph-bar-value',
					' ',
					array(
						'style' => "margin-left: {$graphOffset}px; width: {$graphValue}px",
						'title' => sprintf(__d('debug_kit', "Starting %sms into the request, taking %sms"), $offset, $value),
					)
				),
			array('style' => "width: {$width}px;"),
			false
		);
	}
}
