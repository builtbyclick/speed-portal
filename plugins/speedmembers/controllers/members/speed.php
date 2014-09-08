<?php defined('SYSPATH') or die('No direct script access.');
/**
 * "My Profile" - allows member to configure their settings
 *
 * @author	   Ushahidi Team <team@ushahidi.com>
 * @package	   Ushahidi - http://source.ushahididev.com
 * @subpackage Members
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */


//require Kohana::find_file('profile', 'controllers/members');
class Speed_Controller extends Main_Controller
{

	public $auto_render = FALSE;
	
	public $template = 'layout';

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->template->this_page = 'profile';

				// ugly hack to reuse admin/reports controller
		require_once(APPPATH.'controllers/members/profile.php');
		$profile_controller = new Profile_Controller;
		$profile_controller->template = $this->template;
		$profile_controller->template->this_page = 'profile';
		$profile_controller->index();
		$profile_controller->template->content->set_filename("members/profile_speed");
		$profile_controller->template->content->loggedin_role = $this->user->dashboard();
		// Add js for bootstrap tooltips
		$this->themes->js = '; $(document).ready(function () { $("a[data-toggle=tooltip]").tooltip(); });';
		
		// Kohana handles auto rendering the profile controller - no action needed
	}
}