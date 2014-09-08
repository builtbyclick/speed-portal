<?php 
/**
 * Questions Helper class.
 *
 * This class holds functions used for questions submissions.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @category   Helpers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */
class questions_Core {

	/**
	 * Function to save question categories
	 * 
	 * @param Validation $post
	 * @param Question_Model $question Instance of the question model
	 */
	public static function save_category($post, $question)
	{
		// Delete Previous Entries
		ORM::factory('question_category')->where('question_id', $question->id)->delete_all();
		if (isset($post->question_category))
		{
			foreach ($post->question_category as $item)
			{
				$question_category = new Question_Category_Model();
				$question_category->question_id = $question->id;
				$question_category->category_id = $item;
				$question_category->save();
			}
		}
	}

	/**
	 * Function to save question
	 * 
	 * @param Validation $post
	 * @param Question_Model $question Instance of the question model
	 */
	public static function save_question($post, $question)
	{
		// Get user id
		if (Auth::instance()->get_user() instanceof User_Model)
		{
			$question->user_id = Auth::instance()->get_user()->id;
		}

		$question->date = date("Y-m-d H:i:s",time());
		$question->text = $post->text;
		$question->author = $post->name;
		$question->email = $post->email;
		
		$qid = $question->save();
		
		return $qid;
	}

			
	/**
	 * Validation of form fields
	 *
	 * @param array $post Values to be validated
	 */
	public static function validate(array & $post)
	{

		// Exception handling
		if ( ! isset($post) OR ! is_array($post))
			return FALSE;
		
		// Create validation object
		$post = Validation::factory($post)
				->pre_filter('trim', TRUE)
				->add_rules('text','required', 'length[3,800]');
			
		//XXX: Hack to validate for no checkboxes checked
		//FIXIT: this is crashing out the validation
/*		if ( ! isset($post->question_category))
		{
			$post->question_category = "";
			$post->add_error('question_category','required');
		}
		else
		{
			$post->add_rules('question_category.*','required','numeric');
		}
*/
		

		if ( ! empty($post->name))
		{
			$post->add_rules('name', 'length[2,100]');
		}
		else
		{
			$post->name = '';
		}

		if ( ! empty($post->email))
		{
			$post->add_rules('email', 'email', 'length[3,100]');
		}
		else
		{
			$post->email = '';
		}
		
		// Return
		return $post->validate();
	}

}