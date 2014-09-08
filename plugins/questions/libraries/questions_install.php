 <?php defined('SYSPATH') or die('No direct script access.');
/**
 * Questions Installer
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

 class Questions_Install {
 
	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the summarybox plugin
	 */
	public function run_install()
	{
	
		// Create the database tables.
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."question` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				user_id int(11) DEFAULT 0,
				author varchar(100) default NULL,
				email varchar(120) default NULL,
				text varchar(800) DEFAULT NULL,
				date datetime DEFAULT NULL,
				PRIMARY KEY (`id`)
			);
		");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."question_category` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				question_id int(11) DEFAULT 0,
				category_id int(11) DEFAULT 0,
				PRIMARY KEY (`id`)
			);
		");

		//Answer table is modelled on comment table (as in the comment on a report)
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."answer` (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				question_id bigint(20) DEFAULT NULL,
				checkin_id bigint(20) DEFAULT NULL,
				user_id int(11) DEFAULT 0,
				author varchar(100) default NULL,
				email varchar(120) default NULL,
				text text DEFAULT NULL,
				ip varchar(100) default NULL,
				spam tinyint(4) default 0,
				active tinyint(4) default 0,
				date datetime DEFAULT NULL,
				PRIMARY KEY (`id`)
			);
		");

	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'question`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'question_category`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'answer`');
	}
}