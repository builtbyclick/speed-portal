<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SummaryBox Hook
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

class summarybox {

	public function __construct()
	{
		$block = array(
			"classname" => "summarybox",
			"name" => "Summary Box",
			"description" => "Show summary."
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
		$view = View::factory('summarybox/summary_html');
		$settings = ORM::factory('summarybox_settings')->find(1);
		$view->summarybox_title = $settings->summarybox_title;
		$view->summarybox_text = $settings->summarybox_text;
		$view->render(TRUE);
	}

}

//instatiation of hook
new summarybox;
