 <?php defined('SYSPATH') or die('No direct script access.');
/**
 * StatisticsBox Installer
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

 class Statisticsbox_Install {
 
	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		// Also include table_prefix in name
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."statisticsbox_settings` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				statisticsbox_title1 varchar(20) DEFAULT NULL,
				statisticsbox_title2 varchar(20) DEFAULT NULL,
				statisticsbox_title3 varchar(20) DEFAULT NULL,
				statisticsbox_num1   varchar(20) DEFAULT NULL,
				statisticsbox_num2   varchar(20) DEFAULT NULL,
				statisticsbox_num3   varchar(20) DEFAULT NULL,
				PRIMARY KEY (`id`)
			);
		");
	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'statisticsbox_settings`');
	}
}