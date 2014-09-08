<?php 
/**
 * Reporters download view page.
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
<div class="bg">
	<h2>
		<?php admin::reporters_subtabs("download"); ?>
	</h2>
	<!-- report-form -->
	<div class="report-form">
		<?php
		if ($form_error) {
		?>
			<!-- red-box -->
			<div class="red-box">
				<h3><?php echo Kohana::lang('ui_main.error');?></h3>
				<ul>
				<?php
				foreach ($errors as $error_item => $error_description)
				{
					// print "<li>" . $error_description . "</li>";
					print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
				}
				?>
				</ul>
			</div>
		<?php
		}
		?>
		<!-- column -->
		<div class="download_container">
			<?php print form::open(NULL, array('id' => 'reportForm', 'name' => 'reportForm')); ?>
			<p style="font-weight: bold; color:#00699b; display: block;padding-bottom: 5px;"><?php echo Kohana::lang('ui_admin.select_download_format'); ?></p>
			<div id="form_error_format"></div>
			<p>
				<span><?php print form::radio('format','csv', TRUE); ?><?php echo Kohana::lang('ui_admin.csv')?></span>
				<span><?php print form::radio('format','xml', FALSE); ?><?php echo Kohana::lang('ui_admin.xml') ?></span>
			</p>
			<span style="font-weight: bold; color: #00699b; display: block; padding-bottom: 5px;"><?php echo Kohana::lang('ui_main.choose_data_points');?>:</span>
			<table class="data_points">
			<?php
			//FIXIT: Can't get the select-all to work here... commenting out for now
			/**
				<tr>
					<td colspan="2">
						<input type="checkbox" id="channel_include_all" name="channel_include_all" onclick="CheckAll()" checked="checked"/><strong><?php echo utf8::strtoupper(Kohana::lang('ui_main.select_all'));?></strong>
						<div id="form_error1"></div>
					</td>
				</tr>
			*/
			?>

				<?php
				$flip=1;
				foreach ($services as $service)
				{
					$flip = 1-$flip;
					if ($flip == 0)
					{
						print("<tr>");
					}
				?>
					<td><?php print form::checkbox('channel_include[]',$service->id, in_array(intval($service->id), $form['channel_include'])); ?><?php echo $service->service_name; ?></td>
				<?php
					if ($flip == 1)
					{
						print("</tr>");
					}
				}
				?>
			</table>

			<span style="font-weight: bold; color: #00699b; display: block; padding-bottom: 5px;"><?php echo "Choose trust levels to download";?>:</span>
			<table class="data_points">

				<?php
				$flip=1;
				foreach ($levels as $level)
				{
					$flip = 1-$flip;
					if ($flip == 0)
					{
						print("<tr>");
					}
				?>
					<td><?php print form::checkbox('level_include[]',$level->id, in_array(intval($level->id), $form['level_include'])); ?><?php echo $level->level_title; ?></td>
				<?php
					if ($flip == 1)
					{
						print("</tr>");
					}
				}
				?>
			</table>

			<input id="save_only" type="submit" value="<?php echo utf8::strtoupper(Kohana::lang('ui_main.download'));?>" class="save-rep-btn" />
			<?php print form::close(); ?>
		</div>
	</div>
</div>
