<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Public Report Hook
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

class public_report_hook {

	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}

	public function add()
	{
		// Add nav tab
		//Event::add('ushahidi_action.user_edit', array($this, 'strip_user_login_role');
		//
		// Hook into the event for the reports::fetch_incidents() method
		Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_incident_filters'));

		// Add extra permissions checks for
		Event::add('ushahidi_filter.controller_whitelist', array($this, '_check_extra_perms'));
	}

	public function strip_user_login_role()
	{
		$user = Event::$data;

		// If the user has the publicapi role, remove login role
		if ($user->has(ORM::factory('role', 'publicapi')))
		{
			$user->remove(ORM::factory('role', 'login'));
		}
	}

	public function _add_incident_filters()
	{
		$auth = Auth::instance();
		$user = $auth->get_user();

		$public_category = (int)Settings_Model::get_setting('public_reports_category_id');

		// User has publicapi permission and not member_ui permission
		// @todo maybe limit all access based on member_ui
		if (! $auth->has_permission('member_ui') AND
			! $auth->has_permission('admin_ui') AND
			$auth->has_permission('public_api') AND
			$public_category
			)
		{
			$sharing_plugin = ORM::factory('plugin')->where('plugin_active', 1)->where('plugin_name', 'sharing_two')->find();
			if ($sharing_plugin->loaded)
			{
				array_push(Event::$data, "
					(
						(i.id IN (SELECT sharing_incident_id FROM sharing_combined_incident_category ic WHERE category_id = {$public_category}) AND i.source != 'main') OR
						(i.id IN (SELECT incident_id FROM sharing_combined_incident_category ic WHERE category_id = {$public_category})  AND i.source = 'main')
					)");
			}
			else
			{
				array_push(Event::$data, '(i.id IN (SELECT incident_id FROM incident_category ic WHERE category_id = '.$public_category.') )');
			}
		}
	}

	public function _check_extra_perms()
	{
		$controller_whitelist = Event::$data;
		$auth = Auth::instance();

		if (Kohana::config('settings.private_deployment'))
		{
			// If we don't have member or admin access, and we're not in the whitelist - send to login
			if ( $auth->logged_in() AND
				! $auth->has_permission('member_ui') AND
				! $auth->has_permission('admin_ui') AND
				! in_array(Router::$controller, $controller_whitelist)
			)
			{
				$auth->logout();
				// Redirect to login form
				url::redirect('login');
			}
		}
	}

}

//instantiation of hook
new public_report_hook;
