<?php 
/**
 * Reporters Helper class.
 *
 * This class holds functions used for new report submission from both the backend and frontend.
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
class reporters_Core {
	
	/**
	 * Maintains the list of parameters used for fetching reporters
	 * in the fetch_reporters method
	 * @var array
	 */
	public static $params = array();
	
	/**
	 * Pagination object user in fetch_reporters method
	 * @var Pagination
	 */
	public static $pagination = array();
	
			
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
				->add_rules('service_id','required')
				->add_rules('service_account','required');
		
		// Return
		return $post->validate();
	}
	
	/**
	 * Function to save report location
	 * 
	 * @param Validation $post
	 * @param Location_Model $location Instance of the location model
	 */
	public static function save_location($post, $location)
	{
		// Fetch the country id (set to 0 if country_name isn't set)
		$country = isset($post->country_name)
			? Country_Model::get_country_by_name($post->country_name)
			: new Country_Model(NULL);
		$country_id = ( ! empty($country) AND $country->loaded)? $country->id : 0;
		
		// Assign country_id retrieved
		$post->country_id = $country_id;
		$location->location_name = $post->location_name;
		$location->latitude = $post->latitude;
		$location->longitude = $post->longitude;
		$location->country_id = $country_id;
		$location->location_date = date("Y-m-d H:i:s",time());
		$location->save();
		
		// Garbage collection
		unset ($country, $country_id);
	}
	
	/**
	 * Saves a reporter
	 *
	 * @param Validation $post Validation object with the data to be saved
	 * @param Reporter_Model $reporter Reporter_Model instance to be modified
	 * @param Location_Model $location_model Location to be attached to the reporter
	 * @param int $id ID no. of the report
	 *
	 */
	public static function save_reporter($post, $reporter, $location_id)
	{
		// Exception handling
		if ( ! $reporter instanceof Reporter_Model)
		{
			throw new Kohana_Exception('Invalid parameter types');
		}
		
		// Verify that the location id exists
		if ( ! Location_Model::is_valid_location($location_id))
		{
			throw new Kohana_Exception(sprintf('Invalid location id specified: ', $location_id));
		}
		
		// Is this new?
		if ( ! $reporter->loaded)	
		{
			$reporter->reporter_date = date("Y-m-d H:i:s",time());
		}
		
		//Copy post values into reporter object
		if (isset($post->reporter_id))
		{
			$reporter->id = $post->reporter_id;
		}
		
		$reporter->location_id = $location_id;
		$reporter->reporter_first = $post->reporter_first;
		$reporter->reporter_last = $post->reporter_last;
		$reporter->level_id = $post->level_id;
		$reporter->service_id = $post->service_id;
		$reporter->service_account = $post->service_account;
		
		// Save the reporter
		$reporter->save();
		
		return($reporter->id);
	}
	
	/**
	 * Function to save reporter categories
	 * 
	 * @param Validation $post
	 * @param Reporter_Model $reporter Instance of the reporter model
	 */
	public static function save_category($post, $reporter)
	{
		// Delete Previous Entries
		ORM::factory('reporter_category')->where('reporter_id', $reporter->id)->delete_all();
		
		if (empty($post->reporter_category)) return;
		
		foreach ($post->reporter_category as $item)
		{
			$reporter_category = new Reporter_Category_Model();
			$reporter_category->reporter_id = $reporter->id;
			$reporter_category->category_id = $item;
			$reporter_category->save();
		}
	}



	
	/**
	 * Function to record the verification/approval actions
	 *
	 * @param mixed $reporter
	 */
	public static function verify_approve($reporter)
	{
		// @todo Exception handling
		
		$verify = new Verify_Model();
		$verify->reporter_id = $reporter->id;
		
		// Record 'Verified By' Action
		$verify->user_id = 0;
		if (Auth::instance()->get_user() instanceof User_Model)
		{
			$verify->user_id = Auth::instance()->get_user()->id;
		}
		$verify->verified_date = date("Y-m-d H:i:s",time());
		
		if ($reporter->reporter_active == 1)
		{
			$verify->verified_status = '1';
		}
		elseif ($reporter->reporter_verified == 1)
		{
			$verify->verified_status = '2';
		}
		elseif ($reporter->reporter_active == 1 AND $reporter->reporter_verified == 1)
		{
			$verify->verified_status = '3';
		}
		else
		{
			$verify->verified_status = '0';
		}
		
		// Save
		$verify->save();
	} 
	
	/**
	 * Function that saves reporter geometries
	 *
	 * @param Reporter_Model $reporter
	 * @param mixed $reporter
	 *
	 *
	public static function save_reporter_geometry($post, $reporter)
	{
		// Delete all current geometry
		ORM::factory('geometry')->where('reporter_id',$reporter->id)->delete_all();
		
		if (isset($post->geometry)) 
		{
			// Database object
			$db = new Database();
			
			// SQL for creating the reporter geometry
			$sql = "INSERT INTO ".Kohana::config('database.default.table_prefix')."geometry "
				. "(reporter_id, geometry, geometry_label, geometry_comment, geometry_color, geometry_strokewidth) "
				. "VALUES(%d, GeomFromText('%s'), '%s', '%s', '%s', %s)";
				
			foreach($post->geometry as $item)
			{
				if ( ! empty($item))
				{
					//Decode JSON
					$item = json_decode($item);
					//++ TODO - validate geometry
					$geometry = (isset($item->geometry)) ? $db->escape_str($item->geometry) : "";
					$label = (isset($item->label)) ? $db->escape_str(substr($item->label, 0, 150)) : "";
					$comment = (isset($item->comment)) ? $db->escape_str(substr($item->comment, 0, 255)) : "";
					$color = (isset($item->color)) ? $db->escape_str(substr($item->color, 0, 6)) : "";
					$strokewidth = (isset($item->strokewidth) AND (float) $item->strokewidth) ? (float) $item->strokewidth : "2.5";
					if ($geometry)
					{
						// 	Format the SQL string
						$sql = "INSERT INTO ".Kohana::config('database.default.table_prefix')."geometry "
							. "(reporter_id, geometry, geometry_label, geometry_comment, geometry_color, geometry_strokewidth)"
							. "VALUES(".$reporter->id.", GeomFromText('".$geometry."'), '".$label."', '".$comment."', '".$color."', ".$strokewidth.")";
						Kohana::log('debug', $sql);
						// Execute the query
						$db->query($sql);
					}
				}
			}
		}
	}
	*/
	
	public static function fetch_reporters($paginate = FALSE, $items_per_page = 0)
	{
		// Reset the paramters
		self::$params = array();
				
		$table_prefix = Kohana::config('database.default.table_prefix');
		
		// Fetch the URL data into a local variable
		$url_data = $_GET;
		
		// Split selected parameters on ","
		// For simplicity, always turn them into arrays even theres just one value
		$exclude_params = array('c', 'v', 'm', 'mode', 'sw', 'ne', 'start_loc');
		foreach ($url_data as $key => $value)
		{
			if (in_array($key, $exclude_params) AND ! is_array($value))
			{
				$url_data[$key] = explode(",", $value);
			}
		}
			
		// 
		// Location bounds parameters
		// 
		if (isset($url_data['sw']) AND isset($url_data['ne']))
		{
			$southwest = $url_data['sw'];
			$northeast = $url_data['ne'];
			
			if ( count($southwest) == 2 AND count($northeast) == 2 )
			{
				$lon_min = (float) $southwest[0];
				$lon_max = (float) $northeast[0];
				$lat_min = (float) $southwest[1];
				$lat_max = (float) $northeast[1];
			
				// Add the location conditions to the parameter list
				array_push(self::$params, 
					'l.latitude >= '.$lat_min,
					'l.latitude <= '.$lat_max,
					'l.longitude >= '.$lon_min,
					'l.longitude <= '.$lon_max
				);
			}
		}
		
		// 
		// Location bounds - based on start location and radius
		// 
		if (isset($url_data['radius']) AND isset($url_data['start_loc']))
		{
			//if $url_data['start_loc'] is just comma delimited strings, then make it into an array
			if (intval($url_data['radius']) > 0 AND is_array($url_data['start_loc']))
			{
				$bounds = $url_data['start_loc'];
				if (count($bounds) == 2 AND is_numeric($bounds[0]) AND is_numeric($bounds[1]))
				{
					self::$params['radius'] = array(
						'distance' => intval($url_data['radius']),
						'latitude' => $bounds[0],
						'longitude' => $bounds[1]
					);
				}
			}
		}
		
		// 
		// Check for incident date range parameters
		// 
		if (!empty($url_data['from']))
		{
			// Add hours/mins/seconds so we still get reports if from and to are the same day
			$date_from = date('Y-m-d 00:00:00', strtotime($url_data['from']));
			
			array_push(self::$params, 
				'i.reporter_date >= "'.$date_from.'"'
			);
		}
		if (!empty($url_data['to']))
		{
			// Add hours/mins/seconds so we still get reports if from and to are the same day
			$date_to = date('Y-m-d 23:59:59', strtotime($url_data['to']));
			
			array_push(self::$params, 
				'i.reporter_date <= "'.$date_to.'"'
			);
		}
		
		// Additional checks for date parameters specified in timestamp format
		// This only affects those submitted from the main page
		
		// Start Date
		if (isset($_GET['s']) AND intval($_GET['s']) > 0)
		{
			$start_date = intval($_GET['s']);
			array_push(self::$params, 
				'i.reporter_date >= "'.date("Y-m-d H:i:s", $start_date).'"'
			);
		}

		// End Date
		if (isset($_GET['e']) AND intval($_GET['e']))
		{
			$end_date = intval($_GET['e']);
			array_push(self::$params, 
				'i.reporter_date <= "'.date("Y-m-d H:i:s", $end_date).'"'
			);
		}
		
		// In case a plugin or something wants to get in on the parameter fetching fun
		Event::run('ushahidi_filter.fetch_reporters_set_params', self::$params);
		
		//> END PARAMETER FETCH

		// Check for order and sort params
		$order_field = NULL; $sort = NULL;
		$order_options = array(
			'id' => 'i.id'
		);
		if (isset($url_data['order']) AND isset($order_options[$url_data['order']]))
		{
			$order_field = $order_options[$url_data['order']];
		}
		if (isset($url_data['sort']))
		{
			$sort = (strtoupper($url_data['sort']) == 'ASC') ? 'ASC' : 'DESC';
		}
		
		if ($paginate)
		{
			// Fetch incident count
			$reporter_count = Reporter_Model::get_reporters(self::$params, false, $order_field, $sort, TRUE);
			
			// Set up pagination
			$page_limit = (intval($items_per_page) > 0)
			    ? $items_per_page 
			    : intval(Kohana::config('settings.items_per_page'));
					
			$total_items = $reporter_count->current()
					? $reporter_count->current()->reporter_count
					: 0;
			
			$pagination = new Pagination(array(
					'style' => 'front-end-reports',
					'query_string' => 'page',
					'items_per_page' => $page_limit,
					'total_items' => $total_items
				));
			
			Event::run('ushahidi_filter.pagination',$pagination);
			
			self::$pagination = $pagination;
			
			// Return paginated results
			return Reporter_Model::get_reporters(self::$params, self::$pagination, $order_field, $sort);
		}
		else
		{
			// Return
			return Reporter_Model::get_reporters(self::$params, false, $order_field, $sort);;
		}
	}
	
	
}
