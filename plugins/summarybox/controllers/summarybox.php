<?php defined('SYSPATH') or die('No direct script access.');
/**
 * HelloWorld Controller
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

class Summarybox_Controller extends Controller {
    public function _show_summary()
    {
        $view = View::factory('summarybox/summary_html');
				$settings = ORM::factory('summarybox_settings')->find(1);
				$view->summarybox_title = $settings->summarybox_title;
				$view->summarybox_text = $settings->summarybox_text;
        $view->render(TRUE);
    }
}