<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Smap Controller
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

class Smap_Controller extends Reports_Controller {
    public function _show_reports()
    {
				//FIXIT: get reports from database, and display in abbreviated form in text block
		
				$settings = ORM::factory('smap_settings')->find(1);
				//Set up view parameters
        $view = View::factory('smap/smap_html');
				$view->username = $settings->smap_username;
				$view->password = $settings->smap_password;
				if ($result != null) {
					$view->smap_title = $result->desc;
				}
				else {
					$view->smap_title = 'No data available';
				}
        $view->render(TRUE);
    }
	

	public function index()
	{
		$cat_id = Settings_Model::get_setting('smap_category_id');
		$_GET['c'] = $cat_id;
		
		parent::index();
		
		$this->template->header->this_page = 'smap';
		$this->template->content->set_filename('smap/reports/main');
	}
	

	public function view($id = 0)
	{
		parent::view($id);
		
		$this->template->header->this_page = 'smap';
		$this->template->content->set_filename('smap/reports/detail');
		
		$media = ORM::Factory('media')->where('incident_id', $id)->find_all();
		$this->template->content->smap_embed = array();
		if ($media->count())
		{
			foreach ($media as $m)
			{
				// SMAP embed
				if (in_array($m->media_type, smap_helper::smap_data_type_mapping()))
				{
					$this->template->content->smap_embed[] = $m;
					$this->template->content->smap_embed_type = array_search($m->media_type, smap_helper::smap_data_type_mapping());
				}
			}
		}
		
	}

	/**
	 * Helper method to load the report listing view
	 */
	protected function _get_report_listing_view($locale = '')
	{
		$listing = parent::_get_report_listing_view($locale);
		$listing->set_filename('smap/reports/list');
		return $listing;
	}
		
		
}
