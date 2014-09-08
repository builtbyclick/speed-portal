<?php defined('SYSPATH') or die('No direct script access.');

/**
* Model for Reporters
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

class Reporter_Model extends ORM
{
	protected $belongs_to = array('service','level','location');

	/**
	 * One-to-may relationship definition
	 * @var array
	 */
	protected $has_many = array(
		'incident',
		'message',
		'category' => 'reporter_category',
	);
	
	// Database table name
	protected $table_name = 'reporter';
	
	// Create a Reporter if they do not already exist
	function add($reporter_attrs)
	{
		if (count($this->where('service_id', $reporter_attrs['service_id'])->
		                 where('service_account', $reporter_attrs['service_account'])->
		                 find_all()) == 0)
		{
			$this->db->insert('reporter', $reporter_attrs);
		}
	}

	/**
	 * Get the total number of reporters
	 *
	 * @return int
	 */
	public static function get_total_reporters()
	{
		return ORM::factory('reporter')->count_all();
	}

	/**
	 * Get the earliest reporter date
	 *
	 * @return string
	 */
	public static function get_oldest_reporter_timestamp()
	{
		$result = ORM::factory('reporter')->orderby(array('reporter_date'=>'ASC'))->find_all(1,0);

		foreach($result as $reporter)
		{
			return strtotime($reporter->reporter_date);
		}
	}

	/**
	 * Get the latest reporter date
	 * @return string
	 */
	public static function get_latest_reporter_timestamp()
	{
		$result = ORM::factory('reporter')->orderby(array('reporter_date'=>'DESC'))->find_all(1,0);

		foreach($result as $reporter)
		{
			return strtotime($reporter->reporter_date);
		}
	}
	

	/**
	 * Checks if a specified incident id is numeric and exists in the database
	 *
	 * @param int $incident_id ID of the incident to be looked up
	 * @param bool $approved Whether to include un-approved reports
	 * @return bool
	 */
	public static function is_valid_reporter($reporter_id)
	{
		return (intval($reporter_id) > 0)
			? ORM::factory('reporter')->find(intval($reporter_id))->loaded
			: FALSE;
	}
	
	/**
	 * Gets the reporters that match the conditions specified in the $where parameter
	 * The conditions must relate to columns in the reporter and location tables
	 *
	 * @param array $where List of conditions to apply to the query
	 * @param mixed $limit No. of records to fetch or an instance of Pagination
	 * @param string $order_field Column by which to order the records
	 * @param string $sort How to order the records - only ASC or DESC are allowed
	 * @return Database_Result
	 */
	public static function get_reporters($where = array(), $limit = NULL, $order_field = NULL, $sort = NULL, $count = FALSE)
	{
		// Get the table prefix
		$table_prefix = Kohana::config('database.default.table_prefix');

		// To store radius parameters
		$radius = array();
		$having_clause = "";
		if (array_key_exists('radius', $where))
		{
			// Grab the radius parameter
			$radius = $where['radius'];

			// Delete radius parameter from the list of predicates
			unset ($where['radius']);
		}

		// Query
		// Normal query
		if (! $count)
		{
			$sql = 'SELECT DISTINCT i.id, i.user_id, i.service_id, i.level_id, i.service_account, i.reporter_first, i.reporter_last, '
				. 'i.reporter_email, i.reporter_phone, i.reporter_ip, i.reporter_date, l.country_id, l.location_name, l.latitude, l.longitude ';
		}
		// Count query
		else
		{
			$sql = 'SELECT COUNT(DISTINCT i.id) as reporter_count ';
		}
		
		// Check if all the parameters exist
		if (count($radius) > 0 AND array_key_exists('latitude', $radius) AND array_key_exists('longitude', $radius)
			AND array_key_exists('distance', $radius))
		{
			// Calculate the distance of each point from the starting point
			$sql .= ", ((ACOS(SIN(%s * PI() / 180) * SIN(l.`latitude` * PI() / 180) + COS(%s * PI() / 180) * "
				. "	COS(l.`latitude` * PI() / 180) * COS((%s - l.`longitude`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance ";

			$sql = sprintf($sql, $radius['latitude'], $radius['latitude'], $radius['longitude']);

			// Set the "HAVING" clause
			$having_clause = "HAVING distance <= ".intval($radius['distance'])." ";
		}

		$sql .=  'FROM '.$table_prefix.'reporter i '
			. 'LEFT JOIN '.$table_prefix.'location l ON (i.location_id = l.id) ';
		
		// Check if the all reports flag has been specified
		if (array_key_exists('all_reporters', $where) AND $where['all_reporters'] == TRUE)
		{
			unset ($where['all_reporters']);
			$sql .= 'WHERE 1=1 ';
		}
		else
		{
			$sql .= '';
		}

		// Check for the additional conditions for the query
		if ( ! empty($where) AND count($where) > 0)
		{
			foreach ($where as $predicate)
			{
				$sql .= 'AND '.$predicate.' ';
			}
		}

		// Might need "GROUP BY i.id" do avoid dupes
		
		// Add the having clause
		$sql .= $having_clause;

		// Check for the order field and sort parameters
		if ( ! empty($order_field) AND ! empty($sort) AND (strtoupper($sort) == 'ASC' OR strtoupper($sort) == 'DESC'))
		{
			$sql .= 'ORDER BY '.$order_field.' '.$sort.' ';
		}
		else
		{
			$sql .= 'ORDER BY i.reporter_date DESC ';
		}

		// Check if the record limit has been specified
		if ( ! empty($limit) AND is_int($limit) AND intval($limit) > 0)
		{
			$sql .= 'LIMIT 0, '.$limit;
		}
		elseif ( ! empty($limit) AND $limit instanceof Pagination_Core)
		{
			$sql .= 'LIMIT '.$limit->sql_offset.', '.$limit->items_per_page;
		}
		
		// Event to alter SQL
		Event::run('ushahidi_filter.get_reporters_sql', $sql);

		// Kohana::log('debug', $sql);
		return Database::instance()->query($sql);
	}
	
}
