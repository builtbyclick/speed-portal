<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Reporters Controller
 * Add/Edit Ushahidi Reporters
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @subpackage Admin
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Reporters_Controller extends Admin_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'reporters';
		
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("messages_reporters"))
		{
			url::redirect(url::site().'admin/dashboard');
		}
	}
	
	public function index($service_id = 0, $level_id = 0)
	{
	
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("messages_reporters"))
		{
			url::redirect('admin/dashboard');
		}

		$this->template->content = new View('admin/reporters/main');
		$this->template->content->title = Kohana::lang('ui_admin.reporters');

		// setup and initialize form field names
		$form = array
		(
			'reporter_id' => '',
			'reporter_first' => '',
			'reporter_last' => '',
			'reporter_category' => array(),
			'level_id' => '',
			'service_name' => '',
			'service_account' => '',
			'location_id' => '',
			'location_name' => '',
			'latitude' => '',
			'longitude' => ''
		);
		//  copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";

		// check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory($_POST);

			    //  Add some filters
			$post->pre_filter('trim', TRUE);

			// Add some rules, the input field, followed by a list of checks, carried out in order
			$post->add_rules('action','required', 'alpha', 'length[1,1]');
			$post->add_rules('reporter_id.*','required','numeric');
			
			if ($post->action == 'l')
			{
				$post->add_rules('level_id','required','numeric');
			}

			
			// Test to see if things passed the rule checks
			if ($post->validate())
			{	
				if( $post->action == 'd' )				// Delete Action
				{
					foreach($post->reporter_id as $item)
					{
						// Delete Reporters Messages
						ORM::factory('message')
							->where('reporter_id', $item)
							->delete_all();
					
						// Delete Reporter
						$reporter = ORM::factory('reporter')->find($item);
						$reporter->delete( $item );
						
						//Delete Reporter categories
						ORM::factory('reporter_category')
							->where('reporter_id', $item)
							->delete_all();
					}
					
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				elseif( $post->action == 'l' )			// Modify Level Action
				{
					foreach($post->reporter_id as $item)
					{
						// Update Reporter Level
						$reporter = ORM::factory('reporter')->find($item);
						if ($reporter->loaded)
						{
							$reporter->level_id = $post->level_id;
							$reporter->save();
						}
					}
					
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.modified'));
				}
							}
						
			else
			{
				// No! We have validation errors, we need to show the form again, with the errors

				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());
				
				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('reporters'));
				$form_error = TRUE;
			}
		}

		// Handle filtering of list by category
		$l = Kohana::config('locale.language.0');
		$category_id = (isset($_GET['c']) AND intval($_GET['c']) > 0)? intval($_GET['c']) : 0;
		$category = ORM::factory('category', $category_id);
		if ($category->loaded)
		{
			$this->template->content->category_title = Category_Lang_Model::category_title($category_id,$l);
		}
		else
		{
			$this->template->content->category_title = "";
		}

		// Start building query
		$filter = '1=1 ';
		
		// Default search type to service id
		$search_type = ( isset($_GET['s']) ) ? intval($_GET['s']) : intval($service_id);
		if ($search_type > 0)
		{
			$filter .= 'AND service_id = '.intval($search_type).' ';
		}
		$search_level = ( isset($_GET['l']) ) ? intval($_GET['l']) : 0;
		if ($search_level > 0)
		{
			$filter .= 'AND level_id = '.intval($search_level).' ';
		}
		
		// Get Search Keywords (If Any)
		$keyword = '';
		if (isset($_GET['k']) AND !empty($_GET['k']))
		{
			$keyword = $_GET['k'];
			$filter .= 'AND service_account LIKE \'%'.Database::instance()->escape_str($_GET['k']).'%\' ';
		}

		// Pagination
		$pagination = new Pagination(array(
		                    'query_string' => 'page',
		                    'items_per_page' => $this->items_per_page,
		                    'total_items' => ORM::factory('reporter')
								->where($filter)
								->count_all()
		                ));

		$reporters = ORM::factory('reporter')
						->where($filter)
		                ->orderby('service_account', 'asc')
		                ->find_all($this->items_per_page,  $pagination->sql_offset);

		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		$this->template->content->pagination = $pagination;
		$this->template->content->total_items = $pagination->total_items;
		$this->template->content->reporters = $reporters;
		$this->template->content->service_id = $service_id;
		$this->template->content->search_type = $search_type;
		$this->template->content->search_level = $search_level;
		$this->template->content->category_tree_view = category::get_category_tree_view();
		
		$search_service_array = Service_Model::get_array();
		$search_service_array[0] = "All";
		asort($search_service_array);
		$this->template->content->service_array = Service_Model::get_array();
		$this->template->content->search_service_array = $search_service_array;
		
		$levels = ORM::factory('level')->orderby('level_weight')->find_all();
		$search_level_array = Level_Model::get_array();
		$search_level_array[0] = "All";
		$this->template->content->level_array = Level_Model::get_array();
		$this->template->content->levels = $levels;
		$this->template->content->search_level_array = $search_level_array;	
		$this->template->content->keyword = $keyword;

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		$this->themes->js = new View('admin/reporters/reporters_js');
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->themes->js->latitude = Kohana::config('settings.default_lat');
		$this->themes->js->longitude = Kohana::config('settings.default_lon');
		$this->themes->js->form_error = $form_error;
	}

	/**
	 * Download Reporters in CSV format
	 */
	public function download()
	{
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("messages_reporters"))
		{
			url::redirect(url::site().'admin/dashboard');
		}

		$this->template->content = new View('admin/reporters/download');
		$this->template->content->title = Kohana::lang('ui_admin.download_reporters');

		$errors = $form = array(
			'format' =>'',
			'channel_include' => array(),
			'level_include' => array(),
			'data_include' => array(),
			'from_date'	   => '',
			'to_date'	   => '',
			'form_auth_token'=> ''
		);

		// Default to all selected
		$form['data_include'] = range(1,7);

		$services = ORM::factory('service')->find_all();
		$numservices = count($services);
		foreach ($services as $service)
		{
			$form['channel_include'][] = $service->id;
		}
		
		$levels = ORM::factory('level')->find_all();
		$numlevels = count($levels);
		foreach ($levels as $level)
		{
			$form['level_include'][] = $level->id;
		}
		
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
				$reporters = ORM::factory('reporter')->orderby('reporter_date', 'desc')->find_all();

				// If CSV format is selected
				if($post->format == 'csv')
					{
					$report_csv = download::download_reporter_csv($post, $reporters);

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
					$content = download::download_reporter_xml($post, $reporters);
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
		$this->template->content->services = $services;
		$this->template->content->levels = $levels;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;

		// Javascript Header
		$this->themes->js = new View('admin/reporters/download_js');
		$this->themes->js->calendar_img = url::base() . "media/img/icon-calendar.gif";
	}

	public function upload()
	{
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("messages_reporters"))
		{
			url::redirect(url::site().'admin/dashboard');
		}

		$form = array(
			'uploadfile' => '',
		);
		
		$errors = array();
		$notices = array();
		
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

		/**
	 * Edit a reporter
	 * @param bool|int $id The id no. of the report
	 * @param bool|string $saved
	 */
	public function edit($id = FALSE, $saved = FALSE)
	{
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("messages_reporters"))
		{
			url::redirect('admin/dashboard');
		}

		$db = new Database();
				
		$this->template->content = new View('admin/reporters/edit');
		$this->template->content->title = Kohana::lang('ui_admin.create_reporter');
		$this->template->content->locale_array = Kohana::config('locale.all_languages');
				
		// Setup and initialize form field names
		$form = array(
			'user_id' => '',
			'level_id' => 3,
			'service_id' => '',
			'service_account' => '',
			'reporter_first' => '',
			'reporter_last' => '',
			'reporter_email' => '',
			'reporter_phone' => '',
			'reporter_ip' => '',
			'reporter_date' => date("m/d/Y",time()),
			'reporter_category' => array(),
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
				
		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			
			$post = $_POST;

			// Check if the reporter id is valid and add it to the post data
			if (Reporter_Model::is_valid_reporter($id, FALSE))
			{
				$post = array_merge($post, array('reporter_id' => $id));
			}

			// Validate post contents
			if (reporters::validate($post))
			{
				// Yes! everything is valid
				$location_id = $post->location_id;

				// STEP 1: SAVE LOCATION
				$location = new Location_Model($location_id);
				reporters::save_location($post, $location);

				// STEP 2: SAVE REPORTER
				$reporter = new Reporter_Model($id);
				$id = reporters::save_reporter($post, $reporter, $location->id);

				// STEP 3: SAVE CATEGORIES
				reporters::save_category($post, $reporter);

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
			if (Reporter_Model::is_valid_reporter($id, FALSE))
			{
				// Retrieve Current Reporter
				$reporter = ORM::factory('reporter', $id);
				if ($reporter->loaded == TRUE)
				{
					// Retrieve Categories
					$reporter_category = array();
					foreach($reporter->reporter_category as $category)
					{
						$reporter_category[] = $category->category_id;
					}

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
						'reporter_category' => $reporter_category,
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

		// Level and Service Arrays
		$this->template->content->level_array = Level_Model::get_array();
		$this->template->content->service_array = Service_Model::get_array();
		$this->template->content->new_categories_form = $this->_new_categories_form_arr();

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		$this->themes->colorpicker_enabled = TRUE;
		$this->themes->treeview_enabled = TRUE;
		$this->themes->json2_enabled = TRUE;

		$this->themes->js = new View('admin/reporters/reporters_edit_js');
		$this->themes->js->edit_mode = TRUE;
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->themes->js->form_error = $form_error;

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

		// Pack Javascript
		//$myPacker = new javascriptpacker($this->themes->js , 'Normal', FALSE, FALSE);
		//$this->themes->js = $myPacker->pack();
	}

	// Dynamic categories form fields
	private function _new_categories_form_arr()
	{
		return array(
			'category_name' => '',
			'category_description' => '',
			'category_color' => '',
		);
}

}
