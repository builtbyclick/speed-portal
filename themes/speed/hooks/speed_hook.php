<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Speed Hook
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

class speed_hook {

	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}

	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		Event::add('ushahidi_filter.view_pre_render.layout', array($this, 'pre_render'));


		Event::add('ushahidi_action.themes_add_requirements', array($this, 'add_requirements'));
	}
	
	public function add_requirements()
	{
		$themes = Event::$data;
		
		//$themes->treeview_enabled = TRUE;
		//$themes->requirements();
		
		//Requirements::clear('openlayers.css');
		//Requirements::clear('base.css');
		//Requirements::themedCSS('base.css');
		//Requirements::css('media/css/openlayers.css');
		
		
	}
	
	public function pre_render()
	{
		$view = Event::$data;
		
		$view['header']->live_updates = View::factory('live-updates');
	}

}

new speed_hook;
