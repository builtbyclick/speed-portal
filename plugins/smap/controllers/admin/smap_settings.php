<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Smap Settings Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Smap Settings Controller	
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/

class Smap_Settings_Controller extends Admin_Controller
{
	public function index()
	{
	
		//FIXIT: might need a "reset" button that clears the smap data and returns the last_update to -1
		
		$this->template->this_page = 'addons';
		
		// Standard Settings View
		$this->template->content = new View("admin/addons/plugin_settings");
		$this->template->content->title = "Smap Settings";
		
		// Settings Form View
		$this->template->content->settings_form = new View("smap/admin/smap_settings");
		
		// setup and initialize form field names
		$form = array
		(
			'smap_title' => '',
			'smap_url' => '',
			'smap_username' => '',
			'smap_password' => '',
			'delete_reports' => '',
			'delete_reporters' => ''
		);
		//  Copy the form as errors, so the errors will be stored with keys
		//  corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;

		// check, has the form been submitted, if so, setup validation
		if ($_POST)
		{			
			// Instantiate Validation, use $post, so we don't overwrite $_POST
			// fields with our own things
			$post = new Validation($_POST);

			// Add some filters
			$post->pre_filter('trim', TRUE);

			// Add some rules, the input field, followed by a list of checks, carried out in order
			$post->add_rules('smap_title', 'length[0,40]');
			$post->add_rules('smap_url', 'length[0,255]');
			$post->add_rules('smap_username', 'length[0,40]');
			$post->add_rules('smap_password', 'length[0,40]');

			// Test to see if things passed the rule checks
			if ($post->validate())
			{
				// Yes! everything is valid
				// But check the URL for an end "/"
				$smap_url = (substr($post->smap_url,-1) == "/" ? $post->smap_url : $post->smap_url.'/');
				$settings = ORM::factory('smap_settings', 1);
				$settings->smap_title = $post->smap_title;
				$settings->smap_url = $smap_url;
				$settings->smap_username = $post->smap_username;
				$settings->smap_password = $post->smap_password;
				$settings->save();

				// Everything is A-Okay!
				$form_saved = TRUE;

				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());
			}

			// No! We have validation errors, we need to show the form again, with the errors
			else
			{
				// repopulate the form fields and error fields
				$form = arr::overwrite($form, $post->as_array());
				$errors = arr::overwrite($errors, $post->errors('smap'));
				$form_error = TRUE;
			}
			
			//Do data cleanup on SMAP data, if requested
			//Note that logic is: 
			//  if delete_reporters, then delete reporters and incidents/messages;
			//  if not delete_reporters and delete_reports then delete reports only
			//  if not delete_reporters and not delete_reports then do nothing
			// This is because the SMAP reports are identified through their reporters, 
			// so deleting the reporters will delete all record of which reports are SMAP
			if (($post->delete_reporters == "1") or ($post->delete_reports == "1"))
			{
				$service = ORM::factory('service')
					->where('service_name', 'SMAP')
					->find();
				$smap_reporters = ORM::factory('reporter')->where('service_id', $service->id)->find_all();
				foreach ($smap_reporters as $reporter)
				{
					print_r($reporter->id); print(",");
					$messages = ORM::factory('message')->where('reporter_id', $reporter->id)->find_all();
					foreach ($messages as $message)
					{
						print_r($message->id); print(",");
						//Don't delete_all here because incident has a complicated delete function we want to call
						$reports = ORM::factory('incident')->where('id', $message->incident_id)->find_all();
						foreach ($reports as $report)
						{
							print_r($report->id); print(",");
							$report->delete();
						}
						//Delete message
						$message->delete();
					}
					
					if (($post->delete_reporters == "1"))
					{
						$reporter->delete();
					}
				}
			}
			
		}
		else
		{
			// Retrieve Current Settings
			$settings = ORM::factory('smap_settings', 1);

			$form = array
			(
				'smap_title' => $settings->smap_title,
				'smap_url' => $settings->smap_url,
				'smap_username' => $settings->smap_username,
				'smap_password' => $settings->smap_password,
				'delete_reports' => '',
				'delete_reporters' => ''				
			);
		}
		
		// Pass the $form on to the settings_form variable in the view
		$this->template->content->settings_form->form = $form;
		$this->template->content->settings_form->smap_url = url::site()."smap";
		
		// Other variables
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
	}
}
