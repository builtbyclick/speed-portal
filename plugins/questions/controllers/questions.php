<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Questions Controller
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

class Questions_Controller extends Main_Controller {

	/**
	 * Whether an admin console user is logged in
	 * @var bool
	 */
	var $logged_in;

	public function __construct()
	{
		parent::__construct();
		$this->logged_in = Auth::instance()->logged_in();
	}


	/**
	 * Displays all questions.
	 */
	public function index()
	{
		$this->template->header->this_page ='questions';
		$this->template->header->page_title .= Kohana::lang('ui_questions.questions') .Kohana::config('settings.title_delimiter');
		$this->template->content = new View('questions/questions');
		//$view->site_url = $site_url;

		$this->template->content->questions_unanswered = ORM::factory('question')
			 ->where("id NOT IN (SELECT question_id FROM answer)")
			 ->orderby('date', 'desc')
			 ->find_all( (int) Kohana::config('settings.items_per_page'), $pagination->sql_offset);

		// Recent answered questions
		$this->template->content->questions_recent = ORM::factory('question')
			 ->select("DISTINCT question.*")
			 ->join("answer", "answer.question_id", "question.id", "INNER")
			 ->orderby('answer.date', 'desc')
			 ->find_all( (int) Kohana::config('settings.items_per_page'), $pagination->sql_offset);

	}


	/**
	 * Displays all questions.
	 */
	public function unanswered()
	{
		$this->template->header->this_page ='questions';
		$this->template->header->page_title .= Kohana::lang('ui_questions.questions') .Kohana::config('settings.title_delimiter');
		$this->template->content = new View('questions/list');
		$this->template->content->page_title = 'Unanswered';
		//$view->site_url = $site_url;

		// Pagination
		$this->template->content->pagination = $pagination = new Pagination(array(
				'style' => 'front-end-reports',
			  'query_string' => 'page',
			  'items_per_page' => (int) Kohana::config('settings.items_per_page'),
			  'total_items' => ORM::factory('question')
					 ->where("id NOT IN (SELECT question_id FROM answer)")
					 ->orderby('date', 'desc')
					 ->count_all()
			));

		if (ceil($pagination->total_items / $pagination->items_per_page) <= 1)
		{
			$this->template->content->pagination = '';
		}

		$this->template->content->questions = ORM::factory('question')
			 ->where("id NOT IN (SELECT question_id FROM answer)")
			 ->orderby('date', 'desc')
			 ->find_all( $pagination->items_per_page, $pagination->sql_offset);

	}


	/**
	 * Displays all questions.
	 */
	public function recent()
	{
		$this->template->header->this_page ='questions';
		$this->template->header->page_title .= Kohana::lang('ui_questions.questions') .Kohana::config('settings.title_delimiter');
		$this->template->content = new View('questions/list');
		$this->template->content->page_title = 'Recently Updated';
		//$view->site_url = $site_url;

		// Pagination
		$count = ORM::factory('question')
			 ->select("COUNT(DISTINCT question.id) AS records_found")
			 ->join("answer", "answer.question_id", "question.id", "INNER")
			 ->orderby('answer.date', 'desc')
			 ->find();

		$this->template->content->pagination = $pagination = new Pagination(array(
				'style' => 'front-end-reports',
			  'query_string' => 'page',
			  'items_per_page' => (int) Kohana::config('settings.items_per_page'),
			  'total_items' => $count->records_found
			));

		if (ceil($pagination->total_items / $pagination->items_per_page) <= 1)
		{
			$this->template->content->pagination = '';
		}

		// Recent answered questions
		$this->template->content->questions = ORM::factory('question')
			 ->select("DISTINCT question.*")
			 ->join("answer", "answer.question_id", "question.id", "INNER")
			 ->orderby('answer.date', 'desc')
			 ->find_all( $pagination->items_per_page, $pagination->sql_offset);
	}


	 /**
	 * Displays a question.
	 * @param boolean $id If id is supplied, a question with that id will be
	 * retrieved.
	 */
	public function view($id = FALSE)
	{
		$this->template->header->this_page = 'questions';
		$this->template->content = new View('questions/detail');


		// Load Akismet API Key (Spam Blocker)
		$api_akismet = Kohana::config('settings.api_akismet');

		// Sanitize the question id before proceeding
		$id = intval($id);

		if (! $id < 0)
		{
			url::redirect('main');
		}

		$question = ORM::factory('question')
			->where('id',$id)
			->find();

		// Not Found
		if ( ! $question->loaded)
		{
			url::redirect('questions/questions/');
		}

		$this->template->header->page_title .= Kohana::lang('ui_questions.question_title', text::limit_chars($question->text, 20, '..')) .Kohana::config('settings.title_delimiter');

		// Answer Post?
		// Setup and initialize form field names

		$form = array(
			'author' => '',
			'text' => '',
			'email' => '',
			'ip' => '',
			'captcha' => ''
		);

		$captcha = Captcha::factory();
		$errors = $form;
		$form_error = FALSE;

		// Check, has the form been submitted, if so, setup validation

		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory($_POST);

			// Add some filters
			$post->pre_filter('trim', TRUE);

			// Add some rules, the input field, followed by a list of checks, carried out in order
			if ( ! $this->user)
			{
				$post->add_rules('author', 'required', 'length[3,100]');
				$post->add_rules('email', 'required','email', 'length[4,100]');
			}
			$post->add_rules('text', 'required');
			//$post->add_rules('captcha', 'required', 'Captcha::valid');

			// Test to see if things passed the rule checks
			if ($post->validate())
			{
				// Yes! everything is valid
				if ($api_akismet != "")
				{
					// Run Akismet Spam Checker
					$akismet = new Akismet();

					// Answer data
					$answer = array(
						'website' => "",
						'body' => $post->text,
						'ip' => $_SERVER['REMOTE_ADDR']
					);

					if ($this->user)
					{
						$answer['author'] = $this->user->name;
						$answer['email'] = $this->user->email;
					}
					else
					{
						$answer['author'] = $post->author;
						$answer['email'] = $post->email;
					}

					$config = array(
						'blog_url' => url::site(),
						'api_key' => $api_akismet,
						'answer' => $answer
					);

					$akismet->init($config);

					if ($akismet->errors_exist())
					{
						if ($akismet->is_error('AKISMET_INVALID_KEY'))
						{
							// throw new Kohana_Exception('akismet.api_key');
						}
						elseif ($akismet->is_error('AKISMET_RESPONSE_FAILED'))
						{
							// throw new Kohana_Exception('akismet.server_failed');
						}
						elseif ($akismet->is_error('AKISMET_SERVER_NOT_FOUND'))
						{
							// throw new Kohana_Exception('akismet.server_not_found');
						}

						$spam = 0;
					}
					else
					{
						$spam = ($akismet->is_spam()) ? 1 : 0;
					}
				}
				else
				{
					// No API Key!!
					$spam = 0;
				}

				$answer = new Answer_Model();
				$answer->question_id = $id;
				if ($this->user)
				{
					$answer->user_id = $this->user->id;
					$answer->author = $this->user->name;
					$answer->email = $this->user->email;
				}
				else
				{
					$answer->author = html::strip_tags($post->author, FALSE);
					$answer->email = html::strip_tags($post->email, FALSE);
				}
				$answer->text = html::strip_tags($post->text, FALSE);
				$answer->ip = $_SERVER['REMOTE_ADDR'];
				$answer->date = date("Y-m-d H:i:s",time());

				// Activate answer for now
				if ($spam == 1)
				{
					$answer->spam = 1;
					$answer->active = 0;
				}
				else
				{
					$answer->spam = 0;
					$answer->active = (Kohana::config('settings.allow_comments') == 1)? 1 : 0;
				}
				$answer->save();

				// Redirect
				url::redirect('questions/view/'.$id);

			}
			else
			{
				// No! We have validation errors, we need to show the form again, with the errors
				// Repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// Populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('answers'));
				$form_error = TRUE;
			}
		}

		//User status
		$this->template->content->logged_in = $this->logged_in;
		$this->template->content->loggedin_role = Auth::instance()->get_user()->dashboard();

		// Filters
		$this->template->header->page_title .= $title.Kohana::config('settings.title_delimiter');

		// Add Features
		$this->template->content->question_id = $question->id;
		$this->template->content->question_text = $question->text;
		$this->template->content->question_date = date('M j Y', strtotime($question->date));
		$this->template->content->question_time = date('H:i', strtotime($question->date));
		$this->template->content->question_author = $question->user_id ? ORM::factory('User', $question->user_id) : $question->author;
		$this->template->content->question_category = $question->question_category;

		// Retrieve Answers (Additional Information)
		$this->template->content->answers = new View('questions/answers');
		$this->template->content->answers->question_answers =  $question->answers;

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		$this->themes->photoslider_enabled = TRUE;
		$this->themes->validator_enabled = TRUE;
		$this->themes->js = new View('reports/view_js');
		$this->themes->js->incident_id = $incident->id;
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->themes->js->latitude = $incident->location->latitude;
		$this->themes->js->longitude = $incident->location->longitude;
		$this->themes->js->incident_zoom = $incident->incident_zoom;
		$this->themes->js->incident_photos = $incident_photo;

		// Are we allowed to submit answers?
		$this->template->content->answers_form = "";
		if (Kohana::config('settings.allow_comments'))
		{
			$this->template->content->answers_form = new View('questions/answers_form');
			$this->template->content->answers_form->user = $this->user;
			$this->template->content->answers_form->form = $form;
			$this->template->content->answers_form->form_field_names = $form_field_names;
			$this->template->content->answers_form->captcha = $captcha;
			$this->template->content->answers_form->errors = $errors;
			$this->template->content->answers_form->form_error = $form_error;
		}

		// If the Admin is Logged in - Allow for an edit link
		$this->template->content->logged_in = $this->logged_in;
	}

	/**
	 * Deletes a question.
	 */
	public function delete($id = FALSE)
	{
		$db = new Database();

		//Only delete if user has admin access and the question id is valid
		$logged_in = Auth::instance()->logged_in();
		$loggedin_role = Auth::instance()->get_user()->dashboard();

		if (($logged_in) AND ($loggedin_role != 'members') AND ($id > 0))
		{
			$question = new Question_Model($id);
			if ($question->loaded)
			{
				$question->delete();
			}
		}

		//Redirect user to questions index
		url::redirect('questions');
	}


	/**
	 * Submits a new question.
	 */
	public function submit($id = FALSE, $saved = FALSE)
	{
		$db = new Database();
		$this->template->header->this_page = 'questions';
		$this->template->content = new View('questions/submit');
		$this->template->api_url = Kohana::config('settings.api_url');

		// If id is given, we're editing this question, otherwise it's a new question
		if ($id == FALSE)
		{
			//Adding a new question
			$question = new Question_Model();
			$this->template->header->page_title .= Kohana::lang('ui_questions.ask_a_question')
				.Kohana::config('settings.title_delimiter');
			$this->template->content->question_heading = Kohana::lang('ui_questions.ask_a_question');

			// Setup and initialize form field names
			$form = array(
				'text' => isset($_GET['q']) ? $_GET['q'] : '',
				'name' => '',
				'email' => '',
				'question_category' => array(),
			);
		}
		else
		{
			//Editing an existing question
			$id = intval($id);
			$question = ORM::factory('question')
				->where('id',$id)
				->find();

			if ( ! $question->loaded)
			{
				url::redirect('questions/questions/');
			}

			//Set view headers
			$this->template->header->page_title .= Kohana::lang('ui_questions.question_title',
				text::limit_chars($question->text, 20, '..'))
				.Kohana::config('settings.title_delimiter');
			$this->template->content->question_heading = Kohana::lang('ui_questions.edit_question');

			//Populate categories array
			$question_category = array();
			foreach($question->question_category as $category)
			{
				$question_category[] = $category->category_id;
			}

			// Setup and initialize form field names
			$form = array(
				'text' => $question->text,
				'name' => '',
				'email' => '',
				'question_category' => $question_category,
			);
		}

		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = ($saved == 'saved');

		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			//$post = array_merge($_POST, $_FILES);
			$post = $_POST;

			// Test to see if things passed the rule checks
			if (questions::validate($post))
			{
				questions::save_question($post, $question);
				questions::save_category($post, $question);
				url::redirect('questions/index');
			}

			// No! We have validation errors, we need to show the form again, with the errors
			else
			{
				// Repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// Populate the error fields, if any
				$errors = arr::merge($errors, $post->errors('question'));
				$form_error = TRUE;
			}
		}


		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->logged_in = $this->auth->logged_in();

		// Pass timezone
		$this->template->content->site_timezone = Kohana::config('settings.site_timezone');

		// Pass the submit report message
		$this->template->content->site_submit_report_message = Kohana::config('settings.site_submit_report_message');

		// Javascript Header
		$this->themes->map_enabled = FALSE;
		$this->themes->treeview_enabled = TRUE;
		// Quick and dirty hack - move this into a file later
		$this->themes->js = '
		$(document).ready(function () {
			// Category treeview
			$(".category-column").treeview({
			  persist: "location",
			  collapsed: true,
			  unique: false
			}); });';
		$this->themes->colorpicker_enabled = FALSE;

	}

}
