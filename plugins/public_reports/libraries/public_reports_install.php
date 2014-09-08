 <?php defined('SYSPATH') or die('No direct script access.');
/**
 * Public Reports Installer
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

class Public_Reports_Install {

	/**
	* Constructor to load the shared database library
	*/
	public function __construct()
	{
		$this->db =  Database::instance();
	}

	/**
	* Creates the required database tables for Public Reports
	*/
	public function run_install()
	{
		// Add permission for access to the public report (if it doesn't exist)
		$permission = ORM::factory('Permission')
			->where('name', 'public_api')
			->find();

		if (! $permission->loaded)
		{
			$permission->name = 'public_api';
			$permission->save();
		}

		// Add public api role
		$role = ORM::factory('Role')
			->where('name', 'publicapi')
			->find();

		if (! $role->loaded)
		{
			$role->name = 'publicapi';
			$role->description = "Allow user acces to public reports via API and nothing else";
			$role->access_level = 0;

			$role->add($permission);
			$role->save();
		}

		// Add Public API category to the categories list (if it doesn't exist already)
		$category = ORM::factory('Category', Settings_Model::get_setting('public_reports_category_id'));
		$categoryname = "Public";
		$categorydesc =  "Make a report public"; //FIXIT: need to l10n this

		$this->existing_categories = ORM::factory('category')->select_list('category_title','id');

		if (! $category->loaded)
		{
			$this->notices[] = Kohana::lang('import.new_category') . $categoryname;
			$category = new Category_Model;
			$category->category_title = $categoryname;

			// We'll use green for now. Maybe something random?
			$category->category_color = '66FF66';
			$category->category_visible = 0;
			$category->category_trusted = 1; // Trusted - can't delete
			$category->category_description = $categorydesc;
			$category->category_position = count($this->existing_categories);
			$category->save();

			Settings_Model::save_setting('public_reports_category_id', $category->id);
		}

	}

	/**
	* Deletes the database tables for Public Reports
	*/
	public function uninstall()
	{
	}
}