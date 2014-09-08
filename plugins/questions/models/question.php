<?php defined('SYSPATH') or die('No direct script access.');

/**
* Model for Question
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @subpackage Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Question_Model extends ORM
{
	protected $has_many = array(
		'answers',
		'category' => 'question_category',
	);
	
	// Database table name
	protected $table_name = 'question';

	protected $sorting = array('date' => 'DESC');
	
	/**
	 * Gets the answers for a question
	 * @param int $id Database ID of the question
	 * @return mixed FALSE if the question id is non-existent, ORM_Iterator if it exists
	 */
	/*public static function get_answers($id)
	{
		if (intval($id) > 0)
		{
			$where = array(
				'answer.question_id' => $id,
				'active' => '1',
				'spam' => '0'
			);

			// Fetch the comments
			return ORM::factory('answer')
					->where($where)
					->orderby('date', 'asc')
					->find_all();
		}
		else
		{
			return FALSE;
		}
	}*/
	
	public function latest_answer()
	{
		return ORM::factory('answer')
			->where('question_id', $this->id)
			->orderby('date', 'desc')
			->find();
	}
	
}
