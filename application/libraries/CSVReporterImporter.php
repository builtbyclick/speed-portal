<?php
/**
 * CSV Reporter Importer Library
 *
 * Imports reporter details within CSV file referenced by filehandle.
 * 
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 *
 */
class CSVReporterImporter {	
	/**
	 * Notices to be passed on successful data import
	 * @var array
	 */
	public $notices = array();
	
	/**
	 * Errors to be passed on failed data import
	 * @var array
	 */
	public $errors = array();
	
	/**
	 * Total number of reports within CSV file
	 * @var int
	 */		
	public $totalreporters = 0;
	
	/**
	 * Total number of reports successfully imported
	 * @var int
	 */
	public $importedreporters = 0;
		
	/**
	 * Reporter locations successfully imported
	 * @var array
	 */
	private $locations_added = array();
	
	
	/**
	 * FIXIT: ADD THIS FUNCTION
	 * Function to import dataset from CSV file referenced by the file handle
	 * @param string $filehandle
	 * @return bool 
	 */
	
	/**
	 * Function to import reporters from CSV file referenced by the file handle
	 * @param string $filehandle
	 * @return bool 
	 */
	function import($file) 
	{
		// Get contents of CSV file
		$data = file_get_contents($file);

		// Replace carriage return character
		$replacedata = preg_replace("/\r\n/","\n",$data);

		// Replace file content
		file_put_contents($file, $replacedata);
		
		if($filehandle = fopen($_FILES['uploadfile']['tmp_name'], 'r'))
		{
			$csvtable = new Csvtable($filehandle);
			// Set the required columns of the CSV file
			$requiredcolumns = array('SERVICE ID','SERVICE ACCOUNT');
			foreach ($requiredcolumns as $requiredcolumn)
			{
				// If the CSV file is missing any required column, return an error
				if (!$csvtable->hasColumn($requiredcolumn))
				{
					$this->errors[] = Kohana::lang('import.csv.required_column').'"'.$requiredcolumn.'"';
				}
			}
		
			if (count($this->errors))
			{
				return false;
			}
		
		
			// So we can check if reporter already exists in database
			$this->reporter_ids = ORM::factory('reporter')->select_list('id','id'); 
			
			// Get rows from CSV file
			$this->time = date("Y-m-d H:i:s",time());
			$rows = $csvtable->getRows();
			$this->totalreporters = count($rows);
			$this->rownumber = 0;
	 	
			// Loop through CSV rows
		 	foreach($rows as $row)
		 	{
				$this->rownumber++;
				if (isset($row['#']) AND isset($this->reporter_ids[$row['#']]))
				{
					$this->notices[] = Kohana::lang('import.reporter_exists').$row['#'];
				}
				else
				{
					if ($this->import_reporter($row))
					{
						$this->importedreporters++;
					}
					else
					{
						$this->rollback();
						return false;
					}
				}
			} 
		}
		else
		{
			$this->errors[] = Kohana::lang('ui_admin.file_open_error');
		}
		
		// If we have errors, return FALSE, else TRUE
		return count($this->errors) === 0;
	}
	
	/**
	 * Function to undo import of reports
	 */
	function rollback()
	{
		if (count($this->reporters_added)) ORM::factory('incident')->delete_all($this->reporters_added);
		if (count($this->locations_added)) ORM::factory('location')->delete_all($this->locations_added);
	}
	
	/**
	 * Function to import a report form a row in the CSV file
	 * @param array $row
	 * @return bool
	 */
	function import_reporter($row)
	{
	
		//STEP 0: CHECK THAT THE INPUT SERVICE NAME IS VALID
		$service = ORM::factory('service')
			->where('service_name', $row['SERVICE ID'])
			->find();

		//Only save reporters with valid service names
		if ($service)
		{
		
			//Check this service_id and service_account combination doesn't already exist in the system
			$reporter = ORM::factory('reporter')
				->where(array('service_id' => $service->id, 'service_account' => $row['SERVICE ACCOUNT']))
				-> find();
			
			//Don't update an existing reporter
			//FIXIT: this code with $reporter breaks the views - poss something to do with error recording?
			if (false) // ($reporter)
			{
				return false;
			}
			else
			{
				// STEP 1: SAVE LOCATION
				if (isset($row['LOCATION NAME']))
				{
					$location = new Location_Model();
					$location->location_name = isset($row['LOCATION NAME']) ? $row['LOCATION NAME'] : '';
			
					// For Geocoding purposes
					$location_geocoded = map::geocode($location->location_name);
			
					// If we have LATITUDE and LONGITUDE use those
					if ( isset($row['LATITUDE']) AND isset($row['LONGITUDE']) ) 
					{
						$location->latitude = isset($row['LATITUDE']) ? $row['LATITUDE'] : 0;
						$location->longitude = isset($row['LONGITUDE']) ? $row['LONGITUDE'] : 0;
					} 
			
					// Otherwise, get geocoded lat/lon values
					else
					{
						$location->latitude = $location_geocoded ? $location_geocoded['latitude'] : 0;
						$location->longitude = $location_geocoded ? $location_geocoded['longitude'] : 0;
					}
					$location->country_id = $location_geocoded ? $location_geocoded['country_id'] : 0;
					$location->location_date = $this->time;
					$location->save();
					$this->locations_added[] = $location->id;
				}
				
				// STEP 2: SAVE REPORTER
				$reporter = new Reporter_Model();
				$reporter->service_id      = $service->id;
				$reporter->service_account = $row['SERVICE ACCOUNT'];
				$reporter->location_id     = isset($row['LOCATION NAME']) ? $location->id : 0;
				$reporter->level_id        = isset($row['LEVEL ID']) ? $row['LEVEL ID'] : 3;
				$reporter->reporter_date   = isset($row['DATE ADDED']) ? $row['DATE ADDED'] : $this->time ;
				$reporter->reporter_first  = isset($row['FIRST NAME']) ? $row['FIRST NAME'] : '';
				$reporter->reporter_last   = isset($row['LAST NAME']) ? $row['LAST NAME'] : '';
				$reporter->reporter_email  = isset($row['EMAIL']) ? $row['EMAIL'] : '';
				$reporter->reporter_phone  = isset($row['PHONE']) ? $row['PHONE'] : '';
				$reporter->reporter_ip     = isset($row['URL']) ? $row['URL'] : '';
				$reporter->user_id         = isset($row['USER ID']) ? $row['USER ID'] : '';
				$reporter->save();
				$this->reporters_added[] = $reporter->id;
				 
				return true;
			}
		}
		else
		{
			return false;
		}
	}
}

?>
