<?php defined('SYSPATH') or die('No direct script access.');
/**
 * StatisticsBox Hook
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com>
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class statisticsbox {

	public function __construct()
	{
		$block = array(
			"classname" => "statisticsbox",
			"name" => "Statistics Box",
			"description" => "Show key statistics."
		);

		blocks::register($block);
		
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}

	public function add()
	{
	}

	public function block()
	{
		$view = View::factory('statisticsbox/statistics_html');
		$settings = ORM::factory('statisticsbox_settings')->find(1);
		$view->statisticsbox_title1 = $settings->statisticsbox_title1;
		$view->statisticsbox_title2 = $settings->statisticsbox_title2;
		$view->statisticsbox_title3 = $settings->statisticsbox_title3;
		$view->statisticsbox_num1 = $settings->statisticsbox_num1;
		$view->statisticsbox_num2 = $settings->statisticsbox_num2;
		$view->statisticsbox_num3 = $settings->statisticsbox_num3;
		$view->render(TRUE);
	}

}

//instatiation of hook
new statisticsbox;
