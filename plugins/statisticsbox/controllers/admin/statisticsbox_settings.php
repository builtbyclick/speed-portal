<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Statisticsbox Settings Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Statisticsbox Settings Controller	
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/

class Statisticsbox_Settings_Controller extends Admin_Controller
{
	public function index()
	{
		$this->template->this_page = 'addons';
		
		// Standard Settings View
		$this->template->content = new View("admin/addons/plugin_settings");
		$this->template->content->title = "Statisticsbox Settings";
		
		// Settings Form View
		$this->template->content->settings_form = new View("statisticsbox/admin/statisticsbox_settings");
		
		// setup and initialize form field names
		$form = array
	    (
			'statisticsbox_title1' => '',
			'statisticsbox_title2' => '',
			'statisticsbox_title3' => '',
			'statisticsbox_num1' => '',
			'statisticsbox_num2' => '',
			'statisticsbox_num3' => ''
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
			$post->add_rules('statisticsbox_title1', 'length[0,50]');
			$post->add_rules('statisticsbox_title2', 'length[0,50]');
			$post->add_rules('statisticsbox_title3', 'length[0,50]');
			$post->add_rules('statisticsbox_num1',   'length[0,20]');
			$post->add_rules('statisticsbox_num2',   'length[0,20]');
			$post->add_rules('statisticsbox_num3',   'length[0,20]');

			// Test to see if things passed the rule checks
	        if ($post->validate())
	        {
	            // Yes! everything is valid
				$settings = ORM::factory('statisticsbox_settings', 1);
				$settings->statisticsbox_title1 = $post->statisticsbox_title1;
				$settings->statisticsbox_title2 = $post->statisticsbox_title2;
				$settings->statisticsbox_title3 = $post->statisticsbox_title3;
				$settings->statisticsbox_num1   = $post->statisticsbox_num1;
				$settings->statisticsbox_num2   = $post->statisticsbox_num2;
				$settings->statisticsbox_num3   = $post->statisticsbox_num3;
				$settings->save();

				// Everything is A-Okay!
				$form_saved = TRUE;

				// repopulate the form fields
	            $form = arr::overwrite($form, $post->as_array());
	        }

            // No! We have validation errors, we need to show the form again,
            // with the errors
            else
	        {
	            // repopulate the form fields
	            $form = arr::overwrite($form, $post->as_array());

	            // populate the error fields, if any
	            $errors = arr::overwrite($errors, $post->errors('statisticsbox'));
				$form_error = TRUE;
	        }
	    }
		else
		{
			// Retrieve Current Settings
			$settings = ORM::factory('statisticsbox_settings', 1);

			$form = array
		    (
		        'statisticsbox_title1' => $settings->statisticsbox_title1,
		        'statisticsbox_title2' => $settings->statisticsbox_title2,
		        'statisticsbox_title3' => $settings->statisticsbox_title3,
		        'statisticsbox_num1' => $settings->statisticsbox_num1,
		        'statisticsbox_num2' => $settings->statisticsbox_num2,
		        'statisticsbox_num3' => $settings->statisticsbox_num3
		    );
		}
		
		// Pass the $form on to the settings_form variable in the view
		$this->template->content->settings_form->form = $form;
		
		$this->template->content->settings_form->statisticsbox_url = url::site()."statisticsbox";
		
		// Other variables
	    $this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
	}
}
