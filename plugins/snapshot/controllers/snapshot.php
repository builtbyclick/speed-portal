<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Snapshot Controller
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

 //SJF: extending Main_Controller makes this use the ush framework for views
 //extending Controller just gives you a view black hole.

class SnapShot_Controller extends Main_Controller
{
	public function index()
	{

		//check for user pressing buttons...
		$timespan = (isset($_GET['timespan'])) ? $_GET['timespan'] : '24';

		//Get reports for specified time period (or last day, if time period is blank)
		$date_from = date('Y-m-d H:i:s', time() - intval($timespan)*60*60);

		$reports = ORM::factory('incident')
			->where('incident_active', '1')
			->where('incident_date >= "'.$date_from.'"')
			->orderby(array('incident_date'=>'DESC'))
			->find_all();
		$totalreports = count($reports);

		//Get and filter map
		$div_map = new View('main/map');
		Event::run('ushahidi_filter.map_main', $div_map);

		// Map Settings
		$marker_radius = Kohana::config('map.marker_radius');
		$marker_opacity = Kohana::config('map.marker_opacity');
		$marker_stroke_width = Kohana::config('map.marker_stroke_width');
		$marker_stroke_opacity = Kohana::config('map.marker_stroke_opacity');

		$this->themes->map_enabled = TRUE;
		$this->themes->js = new View('snapshot/main_js');
		$this->themes->js->marker_radius = ($marker_radius >=1 AND $marker_radius <= 10 )
		    ? $marker_radius
		    : 5;

		$this->themes->js->marker_opacity = ($marker_opacity >=1 AND $marker_opacity <= 10 )
		    ? $marker_opacity * 0.1
		    : 0.9;

		$this->themes->js->marker_stroke_width = ($marker_stroke_width >=1 AND $marker_stroke_width <= 5)
		    ? $marker_stroke_width
		    : 2;

		$this->themes->js->marker_stroke_opacity = ($marker_stroke_opacity >=1 AND $marker_stroke_opacity <= 10)
		    ? $marker_stroke_opacity * 0.1
		    : 0.9;

		$this->themes->js->active_startDate = strtotime($date_from);
		$this->themes->js->active_endDate = time();



		//Get all categories used in specified time period
		$ind_categories = ORM::factory('incident_category')
			->join('incident', 'incident_category.incident_id', 'incident.id','INNER')
			->join('category', 'incident_category.category_id', 'category.id','INNER')
			->where('incident.incident_active', '1')
			->where('incident.incident_date >= "'.$date_from.'"')
			->find_all();

		//count category ids in indicator_categories list
		$totalindcats = count($ind_categories);
		$catcounts = array();
		foreach($ind_categories as $indcat)
		{
			if (array_key_exists($indcat->category_id, $catcounts))
			{
				$catcounts[$indcat->category_id]['count']++;
			}
			else
			{
				$catcounts[$indcat->category_id]['count'] = 1;
				$catcounts[$indcat->category_id]['categoryname'] = $indcat->category->category_title;
			}
		}

		//Add category name and percentage to each count
		$catnames = array();
		$cattotals = array();
		foreach ($catcounts as $category => $catdata)
		{
		  $catcounts[$category]['percentage'] = round($catcounts[$category]['count'] * 100 / $totalindcats);
			$catnames[] = $catcounts[$category]['categoryname'];
			$cattotals[] = intval($catcounts[$category]['count']);
		}

		//Count all sources used in reports - NB incident_mode is being deprecated in favour of services
		$sources = array(
			'1' => Kohana::lang("ui_main.web"),
			'2' => Kohana::lang("ui_main.sms"),
			'3' => Kohana::lang("ui_main.email"),
			'4' => Kohana::lang("ui_main.twitter"));
		$sourcecounts = array();
		foreach ($reports as $report)
		{
			if (array_key_exists($report->incident_mode, $sourcecounts))
			{
				$sourcecounts[$report->incident_mode]['count']++;
			}
			else
			{
				if (array_key_exists($report->incident_mode, $sources))
				{
					$sourcecounts[$report->incident_mode]['count'] = 1;
					$sourcecounts[$report->incident_mode]['sourcename'] = $sources[$report->incident_mode];
				}
			}
		}
		$sourcenames = array();
		$sourcetotals = array();
		foreach ($sourcecounts as $source => $sourcedata)
		{
			$sourcecounts[$source]['percentage'] = round($sourcecounts[$source]['count'] * 100 / $totalreports);
			$sourcenames[] = $sourcecounts[$source]['sourcename'];
			$sourcetotals[] = intval($sourcecounts[$source]['count']);
			}

		//Get summary box text
		$summary_settings = ORM::factory('summarybox_settings')->find(1);
		$summarybox_title = $summary_settings->summarybox_title;
		$summarybox_text  = $summary_settings->summarybox_text;

		//Get statistics box text
		$statsbox_settings = ORM::factory('statisticsbox_settings', 1);
		$statsbox_title1 = $statsbox_settings->statisticsbox_title1;
		$statsbox_title2 = $statsbox_settings->statisticsbox_title2;
		$statsbox_title3 = $statsbox_settings->statisticsbox_title3;
		$statsbox_num1 = $statsbox_settings->statisticsbox_num1;
		$statsbox_num2 = $statsbox_settings->statisticsbox_num2;
		$statsbox_num3 = $statsbox_settings->statisticsbox_num3;

		//Get questions
		$recent_questions = ORM::factory('question')
			 ->select("DISTINCT question.*")
			 ->orderby('question.date', 'desc')
			 ->find_all(5, 0);
		$question_authors = array();
		foreach ($recent_questions as $question)
		{
			if ($question->user_id)
			{
				$question_user = ORM::factory('User', $question->user_id);
				$question_authors[$question->id] = $question_user->name;
			}
			else
			{
				$question_authors[$question->id] = $question->author;
			}
		}

		//Set up view
		$this->template->content = new View('snapshot/main');
		$this->template->content->totalreports = $totalreports;
		$this->template->content->div_map = $div_map;
		$this->template->content->catcounts = $catcounts;
		$this->template->content->catnames = $catnames;
		$this->template->content->cattotals= $cattotals;
		$this->template->content->sourcecounts = $sourcecounts;
		$this->template->content->sourcenames = $sourcenames;
		$this->template->content->sourcetotals = $sourcetotals;
		$this->template->content->summarybox_title = $summarybox_title;
		$this->template->content->summarybox_text = $summarybox_text;
		$this->template->content->statsbox_title1 = $statsbox_title1;
		$this->template->content->statsbox_title2 = $statsbox_title2;
		$this->template->content->statsbox_title3 = $statsbox_title3;
		$this->template->content->statsbox_num1 = $statsbox_num1;
		$this->template->content->statsbox_num2 = $statsbox_num2;
		$this->template->content->statsbox_num3 = $statsbox_num3;
		$this->template->content->recent_questions = $recent_questions;
		$this->template->content->question_authors = $question_authors;
		$this->template->content->start_date = strtotime($date_from);
		$this->template->content->end_date = time();
		$this->template->content->timespan = $timespan;


 }
}