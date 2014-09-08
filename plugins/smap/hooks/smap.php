<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Smap Hook
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

class smap {

	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}

	public function add()
	{
		// Hook into main_sidebar event and call the show_summary method
		//Event::add('ushahidi_action.main_sidebar', array('Smap_Controller', '_show_reports'));

		// Add nav tab
		Event::add('ushahidi_filter.nav_main_tabs', array($this, '_add_smap_tab'));
	}

	public function smap()
	{
		// Print the summary box text into the front page side bar
		View::factory('smap/smap_html')->render(TRUE);
	}

	//adds a tab for the big map on the front end
	public function _add_smap_tab()
	{
		$menu_items = Event::$data;

		//$menu_items[] = array('page' => 'smap', 'url' => url::site('smap'), 'name' => 'Rapid Assessments');
		
		Event::$data = $menu_items;
	}

}

//instantiation of hook
new smap;
