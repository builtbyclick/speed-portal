<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Speed Members Hook - Load All Events
 *
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi
 * @copyright  Ushahidi
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class speedmembers {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		
		// Try to alter routing now
		$this->routing();

		// hook into routing - in case we're running too early
		Event::add_after('system.routing', array('Router', 'find_uri'), array($this, 'routing'));
	}
	
	public function routing()
	{
		// Only add the events if we are on that controller
		if (stripos(Router::$current_uri, "members") === 0
		  AND Router::$current_uri != "members/speed")
		{
			// Redirect all members pages to custom member UI page
			Router::$current_uri = "members/speed";
		}
		
		// Redirect admin profile to members/speed
		if (stripos(Router::$current_uri, "admin/profile") === 0)
		{
			// Redirect all members pages to custom member UI page
			Router::$current_uri = "members/speed";
		}
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
	}
}
new speedmembers;
