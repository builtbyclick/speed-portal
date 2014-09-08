<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This controller is used to list/ view and edit reporters
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

class Reporters_Controller extends Main_Controller {
	
	/**
	 * Whether an admin console user is logged in
	 * @var bool
	 */
	var $logged_in;

	public function __construct()
	{
		parent::__construct();

		// Is the Admin Logged In?
		$this->logged_in = Auth::instance()->logged_in();
	}

	/**
	 * Displays all reporters.
	 */
	public function index()
	{
		// Cacheable Controller
		$this->is_cachable = TRUE;

		$this->template->header->this_page = 'reporters';
		$this->template->content = new View('reporters/main');
		$this->themes->js = new View('reporters/reporters_js');

		$this->template->header->page_title .= Kohana::lang('ui_main.reporters').Kohana::config('settings.title_delimiter');

		// Store any exisitng URL parameters
		$this->themes->js->url_params = json_encode($_GET);

		// Enable the map
		$this->themes->map_enabled = TRUE;

		// Set the latitude and longitude
		$this->themes->js->latitude = Kohana::config('settings.default_lat');
		$this->themes->js->longitude = Kohana::config('settings.default_lon');
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');

		// Get Default Color
		$this->themes->js->default_map_all = $this->template->content->default_map_all = Kohana::config('settings.default_map_all');
		
		// Get default icon
		$this->themes->js->default_map_all_icon = $this->template->content->default_map_all_icon = '';
		if (Kohana::config('settings.default_map_all_icon_id'))
		{
			$icon_object = ORM::factory('media')->find(Kohana::config('settings.default_map_all_icon_id'));
			$this->themes->js->default_map_all_icon = $this->template->content->default_map_all_icon = Kohana::config('upload.relative_directory')."/".$icon_object->media_thumb;
		}

		// Load the alert radius view
		$alert_radius_view = new View('alerts/radius');
		$alert_radius_view->show_usage_info = FALSE;
		$alert_radius_view->enable_find_location = FALSE;
		$alert_radius_view->css_class = "rb_location-radius";

		$this->template->content->alert_radius_view = $alert_radius_view;

		// Get locale
		$l = Kohana::config('locale.language.0');

		// Get the report listing view
		$reporter_listing_view = $this->_get_reporter_listing_view($l);

		// Set the view
		$this->template->content->reporter_listing_view = $reporter_listing_view;

		// Collect report stats
		$this->template->content->reporter_stats = new View('reporters/stats');
		
		// Total Reports
		$total_reporters = Reporter_Model::get_total_reporters(TRUE);

		// Get the date of the oldest report
		if (isset($_GET['s']) AND !empty($_GET['s']) AND intval($_GET['s']) > 0)
		{
			$oldest_timestamp =  intval($_GET['s']);
		}
		else
		{
			$oldest_timestamp = Reporter_Model::get_oldest_reporter_timestamp();
		}

		// Get the date of the latest report
		if (isset($_GET['e']) AND !empty($_GET['e']) AND intval($_GET['e']) > 0)
		{
			$latest_timestamp = intval($_GET['e']);
		}
		else
		{
			$latest_timestamp = Reporter_Model::get_latest_reporter_timestamp();
		}

		// Round the number of days up to the nearest full day
		$days_since = ceil((time() - $oldest_timestamp) / 86400);
		$avg_reporters_per_day = ($days_since < 1)? $total_reporters : round(($total_reporters / $days_since),2);

		// Additional view content
		$this->template->content->custom_forms_filter = new View('reporters/submit_custom_forms');
		$this->template->content->custom_forms_filter->disp_custom_fields = customforms::get_custom_form_fields();
		$this->template->content->custom_forms_filter->search_form = TRUE;
		$this->template->content->oldest_timestamp = $oldest_timestamp;
		$this->template->content->latest_timestamp = $latest_timestamp;
		$this->template->content->reporter_stats->total_reporters = $total_reporters;
		$this->template->content->reporter_stats->avg_reporters_per_day = $avg_reporters_per_day;
		$this->template->content->services = Service_Model::get_array();
	}

	/**
	 * Helper method to load the reporter listing view
	 */
	protected function _get_reporter_listing_view($locale = '')
	{
		// Check if the local is empty
		if (empty($locale))
		{
			$locale = Kohana::config('locale.language.0');
		}

		// Load the report listing view
		$reporter_listing = new View('reporters/list');

		// Fetch all reporters
		$reporters = reporters::fetch_reporters();

		// Pagination
		$pagination = reporters::$pagination;

		// Set the view content
		$reporter_listing->reporters = $reporters;

		//Set default as not showing pagination. Will change below if necessary.
		$reporter_listing->pagination = "";

		// Pagination and Total Num of Report Stats
		$plural = ($pagination->total_items == 1)? "" : "s";

		// Set the next and previous page numbers
		$reporter_listing->next_page = $pagination->next_page;
		$reporter_listing->previous_page = $pagination->previous_page;

		if ($pagination->total_items > 0)
		{
			$current_page = ($pagination->sql_offset / $pagination->items_per_page) + 1;
			$total_pages = ceil($pagination->total_items / $pagination->items_per_page);

			if ($total_pages >= 1)
			{
				$reporter_listing->pagination = $pagination;

				// Show the total of reporters
				// @todo This is only specific to the frontend reporters theme
				$reporter_listing->stats_breadcrumb = $pagination->current_first_item.'-'
											. $pagination->current_last_item.' of '.$pagination->total_items.' '
											. Kohana::lang('ui_main.reporters');
			}
			else
			{ 
				// If we don't want to show pagination
				$reporter_listing->stats_breadcrumb = $pagination->total_items.' '.Kohana::lang('ui_admin.reporters');
			}
		}
		else
		{
			$reporter_listing->stats_breadcrumb = '('.$pagination->total_items.' reporter'.$plural.')';
		}

		// Return
		return $reporter_listing;
	}

	public function fetch_reporters()
	{
		$this->template = "";
		$this->auto_render = FALSE;
		
		$reporter_listing_view = $this->_get_reporter_listing_view();
		print $reporter_listing_view;
	}

	/**
	 * Submits a new report.
	 */
	public function submit($id = FALSE, $saved = FALSE)
	{
		$db = new Database();

		$this->template->header->this_page = 'reporters_submit';
		$this->template->content = new View('reporters/submit');

		$this->template->header->page_title .= Kohana::lang('ui_main.reporters_submit_new')
											   .Kohana::config('settings.title_delimiter');

		//Retrieve API URL
		$this->template->api_url = Kohana::config('settings.api_url');

		// Setup and initialize form field names
		$form = array(
			'latitude' => '',
			'longitude' => '',
			'location_name' => '',
			'country_id' => '',
			'country_name'=>'',
			'username'=>'',
			'level_id' => '',
			'level_title' => '',
			'service_id' => '',
			'service_account' => '',
			'reporter_date' => '',
			'reporter_first' => '',
			'reporter_last' => '',
			'reporter_email' => '',
			'reporter_phone' => '',
			'reporter_ip' => ''
		);

		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = ($saved == 'saved');

		// Initialize Default Values
		$form['reporter_date'] = date("m/d/Y",time());
		$form['country_id'] = Kohana::config('settings.default_country');
		$form['level_id'] = 3;

		// Initialize Default Value for Hidden Field Country Name, just incase Reverse Geo coding yields no result
		$country_name = ORM::factory('country',$form['country_id']);
		$form['country_name'] = $country_name->country;


		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = array_merge($_POST, $_FILES);
			
			// Adding event for endtime plugin to hook into
			Event::run('ushahidi_action.reporter_posted_frontend', $post);

			// Test to see if things passed the rule checks
			if (reporters::validate($post))
			{

				// STEP 1: SAVE LOCATION
				$location = new Location_Model();
				reporters::save_location($post, $location);

				// STEP 2: SAVE REPORTER
				$reporter = new Reporter_Model();
				reporters::save_reporter($post, $reporter, $location->id);

				// Run events
				Event::run('ushahidi_action.reporter_submit', $post);
				Event::run('ushahidi_action.reporter_add', $reporter);

				url::redirect('reporters/thanks');
			}

			// No! We have validation errors, we need to show the form again, with the errors
			else
			{
				// Repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// Populate the error fields, if any
				$errors = arr::merge($errors, $post->errors('reporter'));
				$form_error = TRUE;
			}
		}

		// Retrieve Country Cities
		$default_country = Kohana::config('settings.default_country');
		$this->template->content->cities = $this->_get_cities($default_country);
		$this->template->content->multi_country = Kohana::config('settings.multi_country');

		$this->template->content->id = $id;
		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		
		// Pass timezone
		$this->template->content->site_timezone = Kohana::config('settings.site_timezone');

		// Pass the submit reporter message
		$this->template->content->site_submit_reporter_message = Kohana::config('settings.site_submit_reporter_message');

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		$this->themes->treeview_enabled = TRUE;
		$this->themes->colorpicker_enabled = TRUE;

		$this->themes->js = new View('reporters/submit_edit_js');
		$this->themes->js->edit_mode = FALSE;
		$this->themes->js->reporter_zoom = FALSE;
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
		if ( ! $form['latitude'] OR ! $form['latitude'])
		{
			$this->themes->js->latitude = Kohana::config('settings.default_lat');
			$this->themes->js->longitude = Kohana::config('settings.default_lon');
		}
		else
		{
			$this->themes->js->latitude = $form['latitude'];
			$this->themes->js->longitude = $form['longitude'];
		}
		$this->themes->js->geometries = $form['geometry'];

	}

	 /**
	 * Displays a reporter.
	 * @param boolean $id If id is supplied, a report with that id will be
	 * retrieved.
	 */
	public function view($id = FALSE)
	{
		$this->template->header->this_page = 'reporters';
		$this->template->content = new View('reporters/detail');

		// Load Akismet API Key (Spam Blocker)
		$api_akismet = Kohana::config('settings.api_akismet');

		// Sanitize the report id before proceeding
		$id = intval($id);

		if ($id > 0 AND Reporter_Model::is_valid_reporter($id,TRUE))
		{
			$reporter = ORM::factory('reporter')
				->where('id',$id)
				->find();
				
			// Not Found
			if ( ! $reporter->loaded) 
			{
				url::redirect('reporters/view/');
			}

			$captcha = Captcha::factory();
			$errors = $form;
			$form_error = FALSE;

			// Filters - FIXIT: removed for now, but will need to come back

			$this->template->header->page_title .= $service_account.Kohana::config('settings.title_delimiter');

			// Add Features
			$this->template->content->reporter_id = $reporter->id;
			$this->template->content->reporter_location = $reporter->location->location_name;
			$this->template->content->reporter_latitude = $reporter->location->latitude;
			$this->template->content->reporter_longitude = $reporter->location->longitude;
			$this->template->content->reporter_date = date('M j Y', strtotime($reporter->reporter_date));

		}
		else
		{
			url::redirect('main');
		}

		// Add Neighbors
		$this->template->content->reporter_neighbors = Reporter_Model::get_neighbouring_reporters($id, TRUE, 0, 5);

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		$this->themes->photoslider_enabled = TRUE;
		$this->themes->validator_enabled = TRUE;
		$this->themes->js = new View('reporters/view_js');
		$this->themes->js->incident_id = $incident->id;
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->themes->js->latitude = $incident->location->latitude;
		$this->themes->js->longitude = $incident->location->longitude;
		$this->themes->js->incident_zoom = $incident->incident_zoom;
		$this->themes->js->incident_photos = $incident_photo;

		// Initialize custom field array
		$this->template->content->custom_forms = new View('reporters/detail_custom_forms');
		$form_field_names = customforms::get_custom_form_fields($id, $incident->form_id, FALSE, "view");
		$this->template->content->custom_forms->form_field_names = $form_field_names;

		// Are we allowed to submit comments?
		$this->template->content->comments_form = "";
		if (Kohana::config('settings.allow_comments'))
		{
			$this->template->content->comments_form = new View('reporters/comments_form');
			$this->template->content->comments_form->user = $this->user;
			$this->template->content->comments_form->form = $form;
			$this->template->content->comments_form->form_field_names = $form_field_names;
			$this->template->content->comments_form->captcha = $captcha;
			$this->template->content->comments_form->errors = $errors;
			$this->template->content->comments_form->form_error = $form_error;
		}

		// If the Admin is Logged in - Allow for an edit link
		$this->template->content->logged_in = $this->logged_in;
	}

	/**
	 * Report Thanks Page
	 */
	public function thanks()
	{
		$this->template->header->this_page = 'reporters_submit';
		$this->template->content = new View('reporters/submit_thanks');
	}

	/**
	 * Report Rating.
	 * @param boolean $id If id is supplied, a rating will be applied to selected report
	 */
	public function rating($id = false)
	{
		$this->template = "";
		$this->auto_render = FALSE;

		if (!$id)
		{
			echo json_encode(array("status"=>"error", "message"=>"ERROR!"));
		}
		else
		{
			if (!empty($_POST['action']) AND !empty($_POST['type']))
			{
				$action = $_POST['action'];
				$type = $_POST['type'];

				// Is this an ADD(+1) or SUBTRACT(-1)?
				if ($action == 'add')
				{
					$action = 1;
				}
				elseif ($action == 'subtract')
				{
					$action = -1;
				}
				else
				{
					$action = 0;
				}

				if (!empty($action) AND ($type == 'original' OR $type == 'comment'))
				{
					// Has this User or IP Address rated this post before?
					if ($this->user)
					{
						$filter = array("user_id" => $this->user->id);
					}
					else
					{
						$filter = array("rating_ip" => $_SERVER['REMOTE_ADDR']);
					}

					if ($type == 'original')
					{
						$previous = ORM::factory('rating')
							->where('incident_id',$id)
							->where($filter)
							->find();
					}
					elseif ($type == 'comment')
					{
						$previous = ORM::factory('rating')
							->where('comment_id',$id)
							->where($filter)
							->find();
					}

					// If previous exits... update previous vote
					$rating = new Rating_Model($previous->id);

					// Are we rating the original post or the comments?
					if ($type == 'original')
					{
						$rating->incident_id = $id;
					}
					elseif ($type == 'comment')
					{
						$rating->comment_id = $id;
					}

					// Is there a user?
					if ($this->user)
					{
						$rating->user_id = $this->user->id;

						// User can't rate their own stuff
						if ($type == 'original')
						{
							if ($rating->incident->user_id == $this->user->id)
							{
								echo json_encode(array("status"=>"error", "message"=>"Can't rate your own Reports!"));
								exit;
							}
						}
						elseif ($type == 'comment')
						{
							if ($rating->comment->user_id == $this->user->id)
							{
								echo json_encode(array("status"=>"error", "message"=>"Can't rate your own Comments!"));
								exit;
							}
						}
					}

					$rating->rating = $action;
					$rating->rating_ip = $_SERVER['REMOTE_ADDR'];
					$rating->rating_date = date("Y-m-d H:i:s",time());
					$rating->save();

					// Get total rating and send back to json
					$total_rating = $this->_get_rating($id, $type);

					echo json_encode(array("status"=>"saved", "message"=>"SAVED!", "rating"=>$total_rating));
				}
				else
				{
					echo json_encode(array("status"=>"error", "message"=>"Nothing To Do!"));
				}
			}
			else
			{
				echo json_encode(array("status"=>"error", "message"=>"Nothing To Do!"));
			}
		}
	}

	public function geocode()
	{
		$this->template = "";
		$this->auto_render = FALSE;

		if (isset($_POST['address']) AND ! empty($_POST['address']))
		{
			$geocode_result = map::geocode($_POST['address']);
			if ($geocode_result)
			{
				echo json_encode(array_merge(
					$geocode_result, 
					array('status' => 'success')
				));
			}
			else
			{
				echo json_encode(array(
					'status' => 'error',
					'message' =>'ERROR!'
				));
			}
		}
		else
		{
			echo json_encode(array(
				'status' => 'error',
				'message' => 'ERROR!'
			));
		}
	}

	/**
	 * Retrieves Cities
	 * @param int $country_id Id of the country whose cities are to be fetched
	 * @return array
	 */
	private function _get_cities($country_id)
	{
		// Get the cities
		$cities = (Kohana::config('settings.multi_country'))
		    ? City_Model::get_all()
		    : ORM::factory('country', $country_id)->get_cities();

		$city_select = array('' => Kohana::lang('ui_main.reporters_select_city'));

		foreach ($cities as $city)
		{
			$city_select[$city->city_lon.",".$city->city_lat] = $city->city;
		}

		return $city_select;
	}

	/**
	 * Retrieves Total Rating For Specific Post
	 * Also Updates The Incident & Comment Tables (Ratings Column)
	 */
	private function _get_rating($id = FALSE, $type = NULL)
	{
		if (empty($id))
			return 0;
		
		$total_rating = 0;
		$result = FALSE;
		
		if ($type == 'original')
		{
			$result = $this->db->query('SELECT SUM(rating) as total_rating FROM '.$this->table_prefix.'rating WHERE incident_id = ?', $id);
		}
		elseif ($type == 'comment')
		{
			$result = $this->db->query('SELECT SUM(rating) as total_rating FROM '.$this->table_prefix.'rating WHERE comment_id = ?', $id);
		}
		
		if ($result->count() == 0 OR $result->current()->total_rating == NULL) return 0;
		
		$total_rating = $result->current()->total_rating;
		
		return $total_rating;
	}

	/**
	 * Validates a numeric array. All items contained in the array must be numbers or numeric strings
	 *
	 * @param array $nuemric_array Array to be verified
	 */
	private function _is_numeric_array($numeric_array=array())
	{
		if (count($numeric_array) == 0)
			return FALSE;
		else
		{
			foreach ($numeric_array as $item)
			{
				if (! is_numeric($item))
					return FALSE;
			}

			return TRUE;
		}
	}

	/**
	 * Array with Geometry Stroke Widths
    */
	private function _stroke_width_array()
	{
		for ($i = 0.5; $i <= 8 ; $i += 0.5)
		{
			$stroke_width_array["$i"] = $i;
		}

		return $stroke_width_array;
	}

	/**
	 * Ajax call to update Incident Reporting Form
	 */
	public function switch_form()
	{
		$this->template = "";
		$this->auto_render = FALSE;
		isset($_POST['form_id']) ? $form_id = $_POST['form_id'] : $form_id = "1";
		isset($_POST['incident_id']) ? $incident_id = $_POST['incident_id'] : $incident_id = "";
		
		$form_fields = customforms::switcheroo($incident_id,$form_id);
		echo json_encode(array("status"=>"success", "response"=>$form_fields));
	}

}
