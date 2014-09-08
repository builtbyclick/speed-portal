 <?php defined('SYSPATH') or die('No direct script access.');
/**
 * Smap runtime library
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

 class Smap {
 
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
