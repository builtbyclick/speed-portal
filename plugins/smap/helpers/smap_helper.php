<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Dummy class used to upload media attachments from SMAP to a report
 */
class DummyPost {
	public $incident_photo = array();
	public $incident_video = array();
}


/**
 * SMAP helper class.
 * Common functions for handling SMAP feeds
 *
 * @package    Ushahidi
 * @category   Helpers
 * @author     Ushahidi Team
 * @copyright  (c) 2013 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class smap_helper_Core {
	
	/**
	 * Return array mapping smap data type
	 * to ushahidi media type
	 */
	public static function smap_data_type_mapping()
	{
		return array (
			'map' => 10,
			'graph' => 11,
			'table' => 11
		);
	}
	
	/**
	 * parse SMAP api and get list of all SMAP reports from it
	 */
	public static function call_smap_api($smap_settings, $smap_call, $format='json')
	{
	
		/** FIXIT: not using this array yet... start using it for api calls
	  $CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 60,
	  );
		*/
	
		//Set up curl link to password-protected SMAP api
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, "$smap_settings->smap_username:$smap_settings->smap_password");
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //get round ssl certificate problem
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		//download media file and add it to a local directory
		if ($format == "media")
		{
			// Use default upload directory - if it doesn't exist, create it
			$directory = Kohana::config('upload.directory', TRUE);
			$directory = rtrim($directory, '/').'/';
			if ( ! is_dir($directory) AND Kohana::config('upload.create_directories') === TRUE )
			{
				mkdir($directory, 0777, TRUE);
			}
			if ( ! is_writable($directory) )
				throw new Kohana_Exception('upload.not_writable', $directory);

			$filename = $directory.time().'SMAPfile'.substr($smap_call,-4);
			$fp = fopen($filename, 'wb');
			//Media files aren't always on the SMAP server so put the whole url into smap_call
			curl_setopt($ch, CURLOPT_URL, $smap_call);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			fclose($fp);
			
			// Set permissions on new file
			chmod($filename, 0644);

			$result = $filename;
		}
		elseif ($format == "embed")
		{
			$url = $smap_settings->smap_url . $smap_call;
			curl_setopt($ch, CURLOPT_URL, $url);
			$result = curl_exec($ch);
		}
		else
		{
			$url = $smap_settings->smap_url . $smap_call;
			curl_setopt($ch, CURLOPT_URL, $url);
			$callresult = curl_exec($ch);
			$result = json_decode($callresult);
		}
		
		$info = curl_getinfo($ch);
		//echo curl_error($ch);
		curl_close($ch);

		return $result;
		}
	

	/**
	 * save SMAP report as an Ushahidi report
	 SMAP reports come in as a message with type SMAP, with a report automatically created 
	 from that message, ?and the sender automatically tagged as a trusted reporter?
	 */
	public static function save_smap_report($smap_settings, $smap_report)
	{
	
		// Get SMAP Report time
		//FIXIT: check time format; use today if report_time is blank or badly formatted
		$save_date = date("Y-m-d H:i:s",time());
		$smap_date = date("Y-m-d H:i:s",strtotime($smap_report->smap->pub_date));

		//Check table of smap messages to see if this is a new ident.
		//If we've seen this report before, check if it's been updated.
		//Create new message and report if it's new, modify them if it's an update.
		$smap_id = $smap_report->smap->ident;
		$smapdbmessage = ORM::factory('message')->where(array('service_messageid' => $smap_id))->find();
		if (($smapdbmessage->loaded == TRUE) and ($smapdbmessage->message_date >= $smap_date))
		{
			//no report changes needed
		}
		else
		{
		
			//STEP 0: get existing database entities, or create new ones
			if ($smapdbmessage->loaded == TRUE)
			{
				//Replace details in existing message and report
				echo " replacing details ".$smapdbmessage->id;
				$message  = $smapdbmessage;
				$incident = ORM::factory('incident')->where('id', $message->incident_id)->find();
				$location = ORM::factory('location')->where('id', $incident->location_id )->find();
				$person   = ORM::factory('incident_person')->where(array('incident_id' => $message->incident_id))->find();
				$incident_category = ORM::factory('incident_category')->where(array('incident_id' => $message->incident_id))->find();
			}
			else
			{
				//Add new message and report
				$message  = new Message_Model();
				$incident = new Incident_Model();
				$location = new Location_Model();
				$person   = new Incident_Person_Model();
				$incident_category = new Incident_Category_Model();
			}
		
			// STEP 1: SAVE LOCATION	
			//Set LATITUDE and LONGITUDE from the SMAP report bounding box
			//FIXIT: load bounding box into system too
			//FIXIT: bounding box comes in as long-lat-long-lat, which isn't the usual way around
			$bbox = $smap_report->smap->bbox;
			$location->latitude = ($bbox[1]+$bbox[3])/2.0;
			$location->longitude = ($bbox[0]+$bbox[2])/2.0;		
			
			$locname= implode(', ', array_filter(array($smap_report->smap->community, $smap_report->smap->district, $smap_report->smap->region, $smap_report->smap->country)));
			$location->location_name = $locname;
			$location_geocoded = map::geocode($location->location_name);
			$location->country_id = $location_geocoded ? $location_geocoded['country_id'] : 0;
			
			$location->location_date = $smap_date;
			$location->save();
			
			// STEP 2: SAVE SMAP REPORT
			$incident->location_id = $location->id;
			$incident->user_id = 0;
			$incident->form_id = 1;
			$incident->incident_title = $smap_report->title;
			$incident->incident_description = $smap_report->smap->description;
			$incident->incident_date = $smap_date;
			$incident->incident_dateadd = $save_date;
			$incident->incident_active = 1;
			$incident->incident_verified = 1;
			$incident->save();
		
			// STEP 3a: SAVE PERSON, IF THEY DONT ALREADY EXIST
			// FIXIT: set person_ip to the SMAP ip address, and use that to check if the person is new

			//get SMAP person's name
			$pnames = explode(" ", $smap_report->author_name, 2);
			$fname = $pnames[0];
			if (count($pnames) > 1) {
				$lname = $pnames[1];
			}
			else {
				$lname = "";
			}			
			$person->incident_id  = $incident->id;
			$person->person_first = $fname;
			$person->person_last  = $lname;
			$person->person_email = null;
			$person->person_date = $save_date;
			$person->person_ip = $smap_settings->smap_url;
			$person->save();
			
			// STEP 3b: SAVE PERSON AS REPORTER IF THEY'RE NOT ON THE LIST ALREADY
			$service = ORM::factory('service')
				->where('service_name', 'SMAP')
				->find();

			$reporter = ORM::factory('reporter')
				->where('service_id', $service->id)
				->where('service_account', $smap_report->author_name)
				->find();
			
			if (!$reporter->loaded == true)
			{				
				$reporter->service_id		= $service->id;
				$reporter->level_id			= 5; //FIXIT: Automatically trusts SMAP reporters
				$reporter->service_account	= $smap_report->author_name; 
				$reporter->reporter_first	= $fname;
				$reporter->reporter_last	= $lname;
				$reporter->reporter_email	= null;
				$reporter->reporter_phone	= null;
				$reporter->reporter_ip		= null;
				$reporter->reporter_date	= $save_date;
				$reporter->save();
			}
			

			// STEP 4: SAVE SMAP MESSAGE
			$message->parent_id = 0;
			$message->incident_id = $incident->id;
			$message->user_id = 0;
			$message->reporter_id = $reporter->id;
			$message->service_messageid = $smap_id;
			$message->message_from = $smap_report->author_name;
			$message->message_to = $smap_settings->smap_url;
			$message->message = $smap_report->title;
			$message->message_detail = "";
			$message->message_type = 1; //THIS MUST BE 1 OR MESSAGE WONT BE VISIBLE TO ADMIN
			$message->message_date = $smap_date;
			$message->message_level = 5;
			$message->latitude = $location->latitude;
			$message->longitude = $location->longitude;
			$message->save();
						
			// STEP 5: ADD CATEGORY TO INCIDENT
			$incident_category->incident_id = $incident->id; 
			$incident_category->category_id = Settings_Model::get_setting('smap_category_id');
			$incident_category->save();
			
			//STEP 6: SAVE ATTACHMENT - IMAGE OR VIDEO
			//FIXIT: do bad things to the $smap_report so save_media thinks it's getting a $post
			//with $incident_photourl set in it.
			
			$dummypost = new DummyPost;
			if ($smap_report->type == "video") 
			{
				if (isset($smap_report->url))
				{
					$dummypost->incident_video = array($smap_report->url);
					reports::save_media($dummypost, $incident, "SMAP");
				}
			}
			elseif ($smap_report->type != "rich" OR $smap_report->smap->data_type != 'map') // skip saving image for 'map' reports
			{	
				//FIXIT: need to handle FALSE returns from get_image_from_smap
				$smap_settings = ORM::factory('smap_settings')->find(1);
				$photos = array();
				if (isset($smap_report->url))
				{
					$photos[] = smap_helper::call_smap_api($smap_settings, $smap_report->url, "media");
				}
				elseif (isset($smap_report->thumbnail_url))
				{
					$photos[] = smap_helper::call_smap_api($smap_settings, $smap_report->thumbnail_url, "media");
				}
				$dummypost->incident_photo = $photos;
				reports::save_media($dummypost, $incident, "SMAP");
			}
			
			// Save smap embed
			// We have to do this AFTER calling reports::save_media() since this deletes existing media entries
			if ($smap_report->type == "rich" AND in_array($smap_report->smap->data_type, array_keys(self::smap_data_type_mapping()) ))
			{
				$media = ORM::factory("media");
				$media->media_link = $smap_settings->smap_url . 'surveyKPI/reports/view/' . $smap_id . '?format=embed';
				$typemap = self::smap_data_type_mapping();
				$media->media_type = $typemap[$smap_report->smap->data_type]; // Get media type from data type
				$media->media_title = $smap_report->title;
				$media->media_description = $smap_report->smap->description;
				$media->incident_id = $incident->id;
				$media->save();
			}
		}
	}
	
}