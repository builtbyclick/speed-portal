<?php 
/**
 * Reporters edit view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
	<h2>
		<?php admin::reporters_subtabs("edit"); ?>
	</h2>
		
	<div class="head">
		<h3>Edit Reporter</h3>
	</div>
		
	<?php print form::open(NULL,array('id' => 'reporterEdit',	'name' => 'reporterEdit')); ?>
	<?php print form::hidden("action", "a"); ?>
	<?php print form::hidden("reporter_id", $form['reporter_id']); ?>
	<?php print form::hidden("location_id",   $form['location_id']); ?>

	<div class="f-col">
		<div class="row">
			<label>
				<h4><?php echo Kohana::lang('ui_main.first_name');?></h4>
				<?php print form::input('reporter_first', $form['reporter_first'], ' class="text"'); ?>
			</label>
			<label>
				<h4><?php echo Kohana::lang('ui_main.last_name');?></h4>
				<?php print form::input('reporter_last', $form['reporter_last'], ' class="text"'); ?>
			</label>
		</div>
		<div class="row">
			<label>
				<h4><?php echo Kohana::lang('ui_main.reporter_level');?></h4>
				<?php print form::dropdown('level_id', $level_array, $form['level_id']); ?>
			</label>
		</div>

		<div class="row">
			<h4><?php echo Kohana::lang('ui_main.service_information');?></span></h4>
			<label>
				<span><?php echo Kohana::lang('ui_main.service');?></span>
				<?php print form::dropdown('service_id', $service_array, $form['service_name']); ?>
			</label>
			<label>
				<span><?php echo Kohana::lang('ui_main.service_username');?></span>
				<?php print form::input('service_account', $form['service_account'], ' class="text"'); ?>
			</label>
		</div>

		<div class="row">
			<h4><?php echo Kohana::lang('ui_main.categories');?></h4>

			<div class="reporter_category">
			<?php
				$selected_categories = array();
				if (!empty($form['reporter_category']) && is_array($form['reporter_category'])) {
					$selected_categories = $form['reporter_category'];
				}
				$columns = 1;
				echo category::form_tree('reporter_category', $selected_categories, $columns, FALSE, TRUE);
				?>
			</div>
		</div>
	</div>

	<div class="f-col-1">
		<h4>Give this Reporter A Location</h4>
		<span>(Giving the reporter a location will allow their reports to be mapped immediately if they are trusted)</span>
		<div class="row">
			<strong><?php echo Kohana::lang('ui_main.reports_location_name');?>:</strong><br />
			<?php print form::input('location_name', $form['location_name'], ' class="text"'); ?>
		</div>
		<div class="tab-form-item">
			<strong><?php echo Kohana::lang('ui_main.latitude');?>:</strong><br />
			<?php print form::input('latitude', $form['latitude'], ' class="text"'); ?>
		</div>
		<div class="row">
			<strong><?php echo Kohana::lang('ui_main.longitude');?>:</strong><br />
			<?php print form::input('longitude', $form['longitude'], ' class="text"'); ?>
		</div>
		<div style="clear:both;"></div>
		<div class="row">
			<strong><?php echo Kohana::lang('ui_main.location');?>:</strong><br />
			<div id="divMap" class="map_holder_reports olMap"></div>
		</div>
		<div style="clear:both;"></div>
	</div>
	<div class="btns">
		<ul>
			<li><a href="#" class="btn_save"><?php echo utf8::strtoupper(Kohana::lang('ui_main.save'));?></a></li>
			<?php 
			if($id)
			{
				echo "<li><a href=\"#\" class=\"btn_delete btns_red\">".utf8::strtoupper(Kohana::lang('ui_main.delete'))."</a></li>";
			}
			?>
			<li><a href="<?php echo url::site().'admin/reporters/';?>" class="btns_red"><?php echo utf8::strtoupper(Kohana::lang('ui_main.cancel'));?></a></li>
		</ul>
	</div>
		
	<?php print form::close(); ?>