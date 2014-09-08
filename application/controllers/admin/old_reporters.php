<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Reporters Controller.
 * This controller will take care of adding and editing reporters in the Admin section.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com>
 * @package	   Ushahidi - http://source.ushahididev.com
 * @subpackage Admin
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Reporters_Controller extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->template->this_page = 'reporters';
		$this->params = array('all_reporters' => TRUE);
	}


	/**
	 * Lists the reporters.
	 *
	 * @param int $page
	 */
	public function index($page = 1)
	{
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("reporters_view"))
		{
			url::redirect(url::site().'admin/dashboard');
		}

		$this->template->content = new View('admin/reporters/main');
		$this->template->content->title = Kohana::lang('ui_admin.reporters');

		// Database table prefix
		$table_prefix = Kohana::config('database.default.table_prefix');

		$status = "0";
		
		// Handler sort/order fields
		$order_field = 'date'; $sort = 'DESC';
		if (isset($_GET['order']))
		{
			$order_field = html::escape($_GET['order']);
		}
		if (isset($_GET['sort']))
		{
			$sort = (strtoupper($_GET['sort']) == 'ASC') ? 'ASC' : 'DESC';
		}

		// Check, has the form been submitted?
		$form_error = FALSE;
		$errors = array();
		$form_saved = FALSE;
		$form_action = "";

		if ($_POST)
		{
			$post = Validation::factory($_POST);

			 //	Add some filters
			$post->pre_filter('trim', TRUE);

			// Add some rules, the input field, followed by a list of checks,
			// carried out in order
			$post->add_rules('action','required', 'alpha', 'length[1,1]');
			$post->add_rules('reporter_id.*','required','numeric');
						
			if ($post->action == 'd' AND ! Auth::instance()->has_permission('reporters_edit'))
			{
				$post->add_error('action','permission');
			}
			
			if ($post->validate())
			{
				// Delete Action
				if ($post->action == 'd')
				{
					foreach ($post->reporter_id as $item)
					{
						$update = new Reporter_Model($item);
						if ($update->loaded)
						{
							$update->delete();
						}
					}
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				$form_saved = TRUE;
			}
			else
			{
				// Repopulate the form fields
				//$form = arr::overwrite($form, $post->as_array());

				// Populate the error fields, if any
				$errors = $post->errors('reporters');
				$form_error = TRUE;
			}
		}

		// Fetch all reporters
		$reporters = reporters::fetch_reporters(TRUE, Kohana::config('settings.items_per_page_admin'));

		Event::run('ushahidi_filter.filter_reporters',$reporters);

		$this->template->content->countries = Country_Model::get_countries_list();
		$this->template->content->reporters = $reporters;
		$this->template->content->pagination = reporters::$pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->errors = $errors;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;

		// Total Reporters
		$this->template->content->total_items = reporters::$pagination->total_items;

		// Status Tab
		$this->template->content->status = $status;
		$this->template->content->order_field = $order_field;
		$this->template->content->sort = $sort;
		
		$this->themes->map_enabled = TRUE;
		$this->themes->json2_enabled = TRUE;
		$this->themes->treeview_enabled = TRUE;

		// Javascript Header
		$this->themes->js = new View('admin/reporters/reporters_js');
	}

	/**
	 * Edit a reporter
	 * @param bool|int $id The id no. of the report
	 * @param bool|string $saved
	 */
	public function edit($id = FALSE, $saved = FALSE)
	{
		$db = new Database();

		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("reporters_edit"))
		{
			url::redirect('admin/dashboard');
		}

		$this->template->content = new View('admin/reporters/edit');
		$this->template->content->title = Kohana::lang('ui_admin.create_reporter');
		
		// Setup and initialize form field names
		$form = array(
			'locale' => '',
			'user_id' => '',
			'level_id' => '',
			'service_id' => '',
			'service_account' => '',
			'reporter_first' => '',
			'reporter_last' => '',
			'reporter_email' => '',
			'reporter_phone' => '',
			'reporter_ip' => '',
			'reporter_date' =>'',
			'location_id' => '',
			'location_name' => '',
			'latitude' => '',
			'longitude' => '',
			'country_id' => '',
			'country_name' => ''
		);

		// Copy the form as errors, so the errors will be stored with keys
		// corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = ($saved == 'saved');

		// Initialize Default Values
		$form['locale'] = Kohana::config('locale.language');
		$form['reporter_date'] = date("m/d/Y",time());
		$form['country_id'] = Kohana::config('settings.default_country');

		// Locale (Language) Array
		$this->template->content->locale_array = Kohana::config('locale.all_languages');

		$this->template->content->stroke_width_array = $this->_stroke_width_array();

		// Get Countries
		$countries = array();
		foreach (ORM::factory('country')->orderby('country')->find_all() as $country)
		{
			// Create a list of all countries
			$this_country = $country->country;
			if (strlen($this_country) > 35)
			{
				$this_country = substr($this_country, 0, 35) . "...";
			}
			$countries[$country->id] = $this_country;
		}

		// Initialize Default Value for Hidden Field Country Name, 
		// just incase Reverse Geo coding yields no result
		$form['country_name'] = $countries[$form['country_id']];
		$this->template->content->countries = $countries;

		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite 
			// $_POST fields with our own things
			$post = array_merge($_POST, $_FILES);

			// Check if the service id exists
			if (isset($service_id) AND intval($service_id) > 0)
			{
				$post = array_merge($post, array('service_id' => $service_id));
			}

			// Check if the reporter id is valid an add it to the post data
			if (Reporter_Model::is_valid_reporter($id, FALSE))
			{
				$post = array_merge($post, array('reporter_id' => $id));
			}

			/**
			 * NOTES - E.Kala July 27, 2011
			 *
			 * Previously, the $post parameter for this event was a Validation
			 * object. Now it's an array (i.e. the raw data without any validation rules applied to them).
			 * As such, all plugins making use of this event shall have to be updated
			 */

			// Action::report_submit_admin - Report Posted
			Event::run('ushahidi_action.reporter_submit_admin', $post);

			// Validate
			if (reporters::validate($post))
			{
				// Yes! everything is valid
				$location_id = $post->location_id;

				// STEP 1: SAVE LOCATION
				$location = new Location_Model($location_id);
				reporters::save_location($post, $location);

				// STEP 2: SAVE REPORTER
				$reporter = new Reporter_Model($id);
				reporters::save_reporter($post, $reporter, $location->id);

				// Action::reporter_edit - Edited a Reporter
				Event::run('ushahidi_action.reporter_edit', $reporter);

				// SAVE AND CLOSE?
				switch ($post->save)
				{
					case 1:
					case 'dontclose':
						// Save but don't close
						url::redirect('admin/reporters/edit/'. $reporter->id .'/saved');
						break;
					case 'addnew':
						// Save and add new
						url::redirect('admin/reporters/edit/0/saved');
						break;
					default:
						// Save and close
						url::redirect('admin/reporters/');
				}
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
		else
		{
		echo "testme edit page5";
			if (Reporter_Model::is_valid_reporter($id, FALSE))
			{
		echo "testme edit page6";
				// Retrieve Current Reporter
				$reporter = ORM::factory('reporter', $id);
				if ($reporter->loaded == TRUE)
				{
		echo "testme edit page7";
					// Combine Everything
					$reporter_arr = array(
						'reporter_id' => $reporter->id,
						'level_id' => $reporter->level_id,
						'service_id' => $reporter->service_id,
						'service_account' => $reporter->service_account,
						'reporter_first' => $reporter->reporter_first,
						'reporter_last' => $reporter->reporter_last,
						'reporter_email' => $reporter->reporter_email,
						'reporter_phone' => $reporter->reporter_phone,
						'location_id' => $reporter->location_id,
						'reporter_ip' => $reporter->reporter_ip,
						'reporter_date' => date('m/d/Y', strtotime($reporter->reporter_date)),
						'country_id' => $reporter->location->country_id,
						'latitude' => $reporter->location->latitude,
						'longitude' => $reporter->location->longitude,
						'location_name' => $reporter->location->location_name
					);

					// Merge To Form Array For Display
					$form = arr::overwrite($form, $reporter_arr);
				}
				else
				{
					// Redirect
					url::redirect('admin/reporters/');
				}

			}
		}

		$this->template->content->id = $id;
		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;		

		// Retrieve Previous & Next Records
		$previous = ORM::factory('reporter')->where('id < ', $id)->orderby('id','desc')->find();
		$previous_url = $previous->loaded
		    ? url::base().'admin/reporters/edit/'.$previous->id
		    : url::base().'admin/reporters/';
		$next = ORM::factory('reporter')->where('id > ', $id)->orderby('id','desc')->find();
		$next_url = $next->loaded
		    ? url::base().'admin/reporters/edit/'.$next->id
		    : url::base().'admin/reporters/';
		$this->template->content->previous_url = $previous_url;
		$this->template->content->next_url = $next_url;

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		$this->themes->colorpicker_enabled = TRUE;
		$this->themes->treeview_enabled = TRUE;
		$this->themes->json2_enabled = TRUE;

		$this->themes->js = new View('reporters/submit_edit_js');
		$this->themes->js->edit_mode = TRUE;
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');

		echo "testme edit page8";
		if ( ! $form['latitude'] OR !$form['latitude'])
		{
			$this->themes->js->latitude = Kohana::config('settings.default_lat');
			$this->themes->js->longitude = Kohana::config('settings.default_lon');
		}
		else
		{
			$this->themes->js->latitude = $form['latitude'];
			$this->themes->js->longitude = $form['longitude'];
		}

		// Inline Javascript
		$this->template->content->date_picker_js = $this->_date_picker_js();
		$this->template->content->color_picker_js = $this->_color_picker_js();
		$this->template->content->new_category_toggle_js = $this->_new_category_toggle_js();

		// Pack Javascript
		$myPacker = new javascriptpacker($this->themes->js , 'Normal', FALSE, FALSE);
		$this->themes->js = $myPacker->pack();
	}


	/**
	 * Download Reporters in CSV format
	 */
	public function download()
	{
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("reporters_download"))
		{
			url::redirect(url::site().'admin/dashboard');
		}

		$this->template->content = new View('admin/reporters/download');
		$this->template->content->title = Kohana::lang('ui_admin.download_reporters');

		$errors = $form = array(
			'format' =>'',
			'data_include' => array(),
			'from_date'	   => '',
			'to_date'	   => '',
			'form_auth_token'=> ''
		);

		// Default to all selected
		$form['data_include'] = array(1,2,3,4,5,6,7);
		
		$form_error = FALSE;

		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = array_merge($_POST, $_FILES);

			// Test to see if things passed the rule checks
			if (download::validate($post))
			{
				// Retrieve reporters
				$reporters = ORM::factory('reporter')->orderby('reporter_dateadd', 'desc')->find_all();

				// If CSV format is selected
				if($post->format == 'csv')
					{
					$report_csv = download::download_csv($post, $reporters, $custom_forms);

				// Output to browser
				header("Content-type: text/x-csv");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Disposition: attachment; filename=" . time() . ".csv");
				header("Content-Length: " . strlen($report_csv));
				echo $report_csv;
				exit;
					
			}

				// If XML format is selected
				if($post->format == 'xml')
				{ 
					header('Content-type: text/xml; charset=UTF-8');
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-Disposition: attachment; filename=" . time() . ".xml");	
					$content = download::download_xml($post, $reporters, $categories, $forms);
					echo $content;
					exit;
				}
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

		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;

		// Javascript Header
		$this->themes->js = new View('admin/reporters/download_js');
		$this->themes->js->calendar_img = url::base() . "media/img/icon-calendar.gif";
	}

	public function upload()
	{
		$form = array(
			'uploadfile' => '',
		);
		
		$errors = array();
		$notices = array();
		
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("reporters_upload"))
		{
			url::redirect(url::site().'admin/dashboard');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->template->content = new View('admin/reporters/upload');
			$this->template->content->title = 'Upload Reporters';
			$this->template->content->form_error = FALSE;
		}

		if ($_SERVER['REQUEST_METHOD']=='POST')
		{
			$post = array_merge($_POST, $_FILES);

			// Set up validation
			$post = Validation::factory($post)
					->add_rules('uploadfile', 'upload::valid', 'upload::required', 'upload::type[xml,csv]', 'upload::size[3M]');
					
			if($post->validate(TRUE))
					{
				// Establish if file to be uploaded is .xml or .csv format
				$fileinfo = pathinfo($post['uploadfile']['name']);
				$extension = $fileinfo['extension'];
				$allowable_extensions = array ('csv', 'xml');

				if (file_exists($_FILES['uploadfile']['tmp_name']))
						{
					// If file type uploaded is CSV or XML
					if (in_array($extension, $allowable_extensions))
					{
						// Pick the corresponding import library
						$importer = $extension == 'csv' ? new CSVReporterImporter : new XMLReporterImporter;
						if($importer->import($_FILES['uploadfile']['tmp_name']))
						{
							$this->template->content = new View('admin/reporters/upload_success');
							$this->template->content->title = 'Upload Reporters';
							$this->template->content->rowcount = $importer->totalreporters;
							$this->template->content->imported = $importer->importedreporters;
							$this->template->content->notices = $importer->notices;
						}
						
						else
						{
							$errors = $importer->errors;
						}
					}
					
					else
					{
						$errors[] = Kohana::lang('reporters.uploadfile.type');
					}
				}

				// File doesn't exist
				else
				{
					$errors[] = Kohana::lang('ui_admin.file_not_found_upload');
				}
			}

			else
			{
				foreach($post->errors('reporters') as $error)
				{
					$errors[] = $error;
			}

			}
			
			if (count($errors))
			{
				$this->template->content = new View('admin/reporters/upload');
				$this->template->content->title = Kohana::lang('ui_admin.upload_reporters');
				$this->template->content->errors = $errors;
				$this->template->content->form_error = 1;
			}
		}
	}


	/* private functions */

	private function _stroke_width_array()
	{
		for ($i = 0.5; $i <= 8 ; $i += 0.5)
		{
			$stroke_width_array["$i"] = $i;
		}
		return $stroke_width_array;
	}

	// Javascript functions
	private function _color_picker_js()
	{
		 return "<script type=\"text/javascript\">
					$(document).ready(function() {
					$('#category_color').ColorPicker({
							onSubmit: function(hsb, hex, rgb) {
								$('#category_color').val(hex);
							},
							onChange: function(hsb, hex, rgb) {
								$('#category_color').val(hex);
							},
							onBeforeShow: function () {
								$(this).ColorPickerSetColor(this.value);
							}
						})
					.bind('keyup', function(){
						$(this).ColorPickerSetColor(this.value);
					});
					});
				</script>";
	}

	private function _date_picker_js()
	{
		return "<script type=\"text/javascript\">
				$(document).ready(function() {
				$(\"#reporter_date\").datepicker({
				showOn: \"both\",
				buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\",
				buttonImageOnly: TRUE
				});
				});
			</script>";
	}

	/**
	 * Checks if translation for this reporter & locale exists
	 * @param Validation $post $_POST variable with validation rules
	 * @param int $iid The unique reporter_id of the original report
	 */
	public function translate_exists_chk(Validation $post)
	{
		// If add->rules validation found any errors, get me out of here!
		if (array_key_exists('locale', $post->errors()))
			return;

		$iid = (isset($_GET['iid']) AND intval($_GTE['iid'] > 0))? intval($_GET['iid']) : 0;

		// Load translation
		$translate = ORM::factory('reporter_lang')
						->where('reporter_id',$iid)
						->where('locale',$post->locale)
						->find();

		if ($translate->loaded)
		{
			$post->add_error( 'locale', 'exists');
		}
		else
		{
			// Not found
			return;
		}
	}
}
