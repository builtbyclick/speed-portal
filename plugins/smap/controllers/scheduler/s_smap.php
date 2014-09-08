<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMAP Scheduler Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @subpackage Scheduler
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
*/

class S_SMAP_Controller extends Controller {
	
	public function __construct()
  {
    parent::__construct();
	}
	
	
	/**
	 * parse feed and send feed items to database
	 */
	public function index()
	{		
	
		//Check last time the SMAP API was polled
		$last_update = $smap_settings->last_update;

		//Get list of all projects in the SMAP instance
		$smap_settings = ORM::factory('smap_settings')->find(1);
		if ($smap_settings->loaded  == TRUE)
		{
		  $project_list = smap_helper::call_smap_api($smap_settings, "surveyKPI/myProjectList");
	  	if (($project_list <> NULL) and (is_array($project_list))){
		    foreach ($project_list as $project) {
		
  		  	//Get list of all reports in the project
	    		$reports_list = smap_helper::call_smap_api($smap_settings, "surveyKPI/reports/list/".$project->id);
					if (($reports_list <> NULL) and (is_array($reports_list))){
						foreach ($reports_list as $report) {
							//get each report in json format and store anything new to the Ushahidi reports table
							$reportid = $report->smap->ident;
							$reportdetails = smap_helper::call_smap_api($smap_settings, "surveyKPI/reports/view/".$reportid.'?format=json');
							smap_helper::save_smap_report($smap_settings, $reportdetails);
						}

						//save time we started checking the records to the smap settings table
						$smap_settings->last_update = strtotime('now');
						$smap_settings->save();
					}
				}
			}
		}
	}
}
