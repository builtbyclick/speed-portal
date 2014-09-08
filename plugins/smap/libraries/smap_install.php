 <?php defined('SYSPATH') or die('No direct script access.');
/**
 * Smap Installer
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

class Smap_Install {
	
	/**
	* Constructor to load the shared database library
	*/
	public function __construct()
	{
		$this->db =  Database::instance();
	}
	
	/**
	* Creates the required database tables for Smap
	*/
	public function run_install()
	{
		//Piggybacking: add reporter_category table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."reporter_category`
			(
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				reporter_id int(11) unsigned DEFAULT 0,
				category_id int(11) unsigned DEFAULT 0,
				PRIMARY KEY (id)
			);");
		
		// Create the SMAP database tables
		// Include the table_prefix
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."smap_settings`
			(
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				smap_title varchar(40) DEFAULT NULL,
				smap_url varchar(255) DEFAULT NULL,
				smap_username varchar(40) DEFAULT NULL,
				smap_password varchar(40) DEFAULT NULL,
				last_update int(11) DEFAULT -1,
				PRIMARY KEY (id)
			);");
		
		//Add SMAP to the list of services, if it isn't already in there
		$service = ORM::factory('service')->where(array('service_name' => 'SMAP'))->find();
		if ($service->loaded == FALSE)
		{
			$service = new Service_Model();
			$service->service_name = "SMAP";
			$service->service_description = "SMAP suite interface";
			$service->save();
		}
		
		//Add SMAP to the scheduler table
		$scheduler = ORM::factory('scheduler')->where(array('scheduler_name' => 'SMAP'))->find();
		if ($scheduler->loaded == FALSE)
		{
			$scheduler = new Scheduler_Model();
			$scheduler->scheduler_name = 'SMAP';
			$scheduler->scheduler_last = 0;
			$scheduler->scheduler_weekday = -1;
			$scheduler->scheduler_day = -1;
			$scheduler->scheduler_hour = -1;
			$scheduler->scheduler_minute = 0;
			$scheduler->scheduler_controller = 's_smap';
			$scheduler->scheduler_active = 1;
			$scheduler->save();	
		}
		
		//Add SMAP category to the categories list (if it doesn't exist already)
		// Convert the first string character of the category name to Uppercase
		$category = ORM::factory('Category', Settings_Model::get_setting('smap_category_id'));
		$categoryname = "Assessments";
		$categorydesc =  "Rapid Assessment reports from SMAP"; //FIXIT: need to l10n this
		
		if (! $category->loaded)
		{
			$this->notices[] = Kohana::lang('import.new_category').$categoryname;
			$category = new Category_Model;
			$category->category_title = $categoryname;
			
			// We'll use yellow for now. Maybe something random?
			$category->category_color = 'FFFF00';
			
			// because all current categories are of type '5'
			$category->category_visible = 1;
			$category->category_description = $categorydesc;
			$category->category_position = ORM::factory('category')->count_all();
			$category->save();
			Settings_Model::save_setting('smap_category_id', $category->id);
		}
		
	}
	
	/**
	* Deletes the database tables for Smap
	*/
	public function uninstall()
	{
		//Delete SMAP tables from the database
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'smap_settings`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'smap_incident`');
		
		//Remove SMAP from the scheduler table
		$this->db->query("DELETE FROM `".Kohana::config('database.default.table_prefix')."scheduler` where scheduler_name = 'SMAP' ");
	}
}