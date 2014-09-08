<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Questions Hook
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

class questionsbox {

	public function __construct()
	{
		$block = array(
			"classname" => "questionsbox",
			"name" => "Questions",
			"description" => "Show question and answer forum."
		);

		blocks::register($block);
			
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}

	public function add()
	{
		// Add nav tab
		Event::add('ushahidi_filter.nav_main_tabs', array($this, '_add_questions_tab'));
	}

	public function block()
	{
		$view = View::factory('questions/questions_block');
		$view->questions = ORM::factory('question')->limit(2)->orderby('date', 'DESC')->find_all();;
		$view->render(TRUE);
	}

	//adds a tab for the big map on the front end
	public function _add_questions_tab()
	{
		$menu_items = Event::$data;

		//$menu_items[] = array('page' => 'questions', 'url' => url::site('smap'), 'name' => 'Rapid Assessments');
		
		Event::$data = $menu_items;
	}

}

//instatiation of hook
new questionsbox;
