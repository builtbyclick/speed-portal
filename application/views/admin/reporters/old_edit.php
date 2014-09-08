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
echo "testme again";
			<div class="bg">
				<h2>
					<?php admin::reporters_subtabs("edit"); ?>
				</h2>
				<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'reporterForm', 'name' => 'reporterForm')); ?>
					<input type="hidden" name="save" id="save" value="">
					<input type="hidden" name="location_id" id="location_id" value="<?php print $form['location_id']; ?>">
					<input type="hidden" name="country_name" id="country_name" value="<?php echo $form['country_name'];?>" />
					<!-- report-form -->
					<div class="reporter-form">
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
									print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
								}
								?>
								</ul>
							</div>
						<?php
						}

						if ($form_saved) {
						?>
							<!-- green-box -->
							<div class="green-box">
								<h3><?php echo Kohana::lang('ui_main.report_saved');?></h3>
							</div>
						<?php
						}
						?>
						<div class="head">
							<h3><?php echo $id ? Kohana::lang('ui_main.edit_reporter') : Kohana::lang('ui_main.new_reporter'); ?></h3>
							<div class="btns" style="float:right;">
								<ul>
									<li><a href="#" class="btn_save"><?php echo utf8::strtoupper(Kohana::lang('ui_main.save_reporter'));?></a></li>
									<li><a href="#" class="btn_save_close"><?php echo utf8::strtoupper(Kohana::lang('ui_main.save_close'));?></a></li>
									<li><a href="#" class="btn_save_add_new"><?php echo utf8::strtoupper(Kohana::lang('ui_main.save_add_new'));?></a></li>
									<li><a href="<?php echo url::base().'admin/reporters/';?>" class="btns_red"><?php echo utf8::strtoupper(Kohana::lang('ui_main.cancel'));?></a>&nbsp;&nbsp;&nbsp;</li>
									<?php if ($id) {?>
									<li><a href="<?php echo $previous_url;?>" class="btns_gray">&laquo; <?php echo utf8::strtoupper(Kohana::lang('ui_main.previous'));?></a></li>
									<li><a href="<?php echo $next_url;?>" class="btns_gray"><?php echo utf8::strtoupper(Kohana::lang('ui_main.next'));?> &raquo;</a></li>
									<?php } ?>
								</ul>
							</div>
						</div>
						<!-- f-col -->
						<div class="f-col">
							<?php
							// Action::report_pre_form_admin - Runs right before report form is rendered
							Event::run('ushahidi_action.reporter_pre_form_admin', $id);
							?>
							<?php if ($show_messages) { ?>
							<div class="row">
								<h4 style="margin:0;padding:0;"><a href="#" id="messages_toggle" class="show-messages"><?php echo Kohana::lang('ui_main.show_messages');?></a>&nbsp;</h4>
								<!--messages table goes here-->
			                    <div id="show_messages">
									<?php
									foreach ($all_messages as $message) {
										echo "<div class=\"message\">";
										echo "<strong><u>" . $message->message_from . "</u></strong> - ";
										echo $message->message;
										echo "</div>";
									}
									?>
								</div>
							</div>
							<?php } ?>
							<div class="row">
								<h4><?php echo Kohana::lang('ui_main.form');?> <span>(<?php echo Kohana::lang('ui_main.select_form_type');?>)</span></h4>
								<span class="sel-holder">
									<?php print form::dropdown('form_id', $forms, $form['form_id'],
										' onchange="formSwitch(this.options[this.selectedIndex].value, \''.$id.'\')"') ?>
								</span>
								<div id="form_loader" style="float:left;"></div>
							</div>
							<div class="row">
								<h4><?php echo Kohana::lang('ui_main.title');?> <span class="required">*</span></h4>
								<?php print form::input('incident_title', $form['incident_title'], ' class="text title"'); ?>
							</div>
							<div class="row">
								<h4><?php echo Kohana::lang('ui_main.description');?> <span><?php echo Kohana::lang('ui_main.include_detail');?>.</span> <span class="required">*</span></h4>
								<span class="allowed-html"><?php echo html::allowed_html(); ?></span>
								<?php print form::textarea('incident_description', $form['incident_description'], ' rows="12" cols="40"') ?>
							</div>

							<?php
							// Action::reporter_form_admin - Runs just after the report description
							Event::run('ushahidi_action.reporter_form_admin', $id);
							?>

							<?php
							if (!($id))
							{ // Use default date for new report
								?>
								<div class="row" id="datetime_default">
									<h4><a href="#" id="date_toggle" class="new-cat"><?php echo Kohana::lang('ui_main.modify_date');?></a><?php echo Kohana::lang('ui_main.modify_date');?>: 
									<?php echo Kohana::lang('ui_main.today_at').' '.$form['incident_hour']
										.":".$form['incident_minute']." ".$form['incident_ampm']; ?></h4>
								</div>
								<?php
							}
							?>
							<div class="row <?php
								if (!($id))
								{ // Hide date editor for new report
									echo "hide";
								}?> " id="datetime_edit">
								<div class="date-box">
									<h4><?php echo Kohana::lang('ui_main.date');?> <span><?php echo Kohana::lang('ui_main.date_format');?></span></h4>
									<?php print form::input('incident_date', $form['incident_date'], ' class="text"'); ?>								
									<?php print $date_picker_js; ?>				    
								</div>
								<div class="time">
									<h4><?php echo Kohana::lang('ui_main.time');?> <span>(<?php echo Kohana::lang('ui_main.approximate');?>)</span></h4>
									<?php
									print '<span class="sel-holder">' .
								    form::dropdown('incident_hour', $hour_array,
									$form['incident_hour']) . '</span>';
									
									print '<span class="dots">:</span>';
									
									print '<span class="sel-holder">' .
									form::dropdown('incident_minute',
									$minute_array, $form['incident_minute']) .
									'</span>';
									print '<span class="dots">:</span>';
									
									print '<span class="sel-holder">' .
									form::dropdown('incident_ampm', $ampm_array,
									$form['incident_ampm']) . '</span>';
									?>
								</div>
							</div>
							<div class="row">
							<?php Event::run('ushahidi_action.reporter_form_admin_after_time', $id); ?>
							</div>

						</div>
						<!-- f-col-1 -->
						<div class="f-col-1">
							<div class="reporter-location">
								<h4><?php echo Kohana::lang('ui_main.reporter_location');?></h4>
								<div class="location-info">
									<span><?php echo Kohana::lang('ui_main.latitude');?>:</span>
									<?php print form::input('latitude', $form['latitude'], ' class="text"'); ?>
									<span><?php echo Kohana::lang('ui_main.longitude');?>:</span>
									<?php print form::input('longitude', $form['longitude'], ' class="text"'); ?>
								</div>
								<ul class="map-toggles">
									<li><a href="#" class="smaller-map"><?php echo Kohana::lang('ui_main.smaller_map'); ?></a></li>
									<li style="display:block;"><a href="#" class="wider-map"><?php echo Kohana::lang('ui_main.wider_map'); ?></a></li>
									<li><a href="#" class="taller-map"><?php echo Kohana::lang('ui_main.taller_map'); ?></a></li>
									<li><a href="#" class="shorter-map"><?php echo Kohana::lang('ui_main.shorter_map'); ?></a></li>
								</ul>
								<div id="divMap" class="map_holder_reports">
									<div id="geometryLabelerHolder" class="olControlNoSelect">
										<div id="geometryLabeler">
											<div id="geometryLabelComment">
												<span id="geometryLabel"><label><?php echo Kohana::lang('ui_main.geometry_label');?>:</label> <?php print form::input('geometry_label', '', ' class="lbl_text"'); ?></span>
												<span id="geometryComment"><label><?php echo Kohana::lang('ui_main.geometry_comments');?>:</label> <?php print form::input('geometry_comment', '', ' class="lbl_text2"'); ?></span>
											</div>
											<div>
												<span id="geometryColor"><label><?php echo Kohana::lang('ui_main.geometry_color');?>:</label> <?php print form::input('geometry_color', '', ' class="lbl_text"'); ?></span>
												<span id="geometryStrokewidth"><label><?php echo Kohana::lang('ui_main.geometry_strokewidth');?>:</label> <?php print form::dropdown('geometry_strokewidth', $stroke_width_array, ''); ?></span>
												<span id="geometryLat"><label><?php echo Kohana::lang('ui_main.latitude');?>:</label> <?php print form::input('geometry_lat', '', ' class="lbl_text"'); ?></span>
												<span id="geometryLon"><label><?php echo Kohana::lang('ui_main.longitude');?>:</label> <?php print form::input('geometry_lon', '', ' class="lbl_text"'); ?></span>
											</div>
										</div>
										<div id="geometryLabelerClose"></div>
									</div>
								</div>
							</div>
							<div class="reporter-find-location">
								<div id="panel" class="olControlEditingToolbar"></div>
								<div class="btns" style="float:left;">
									<ul style="padding:4px;">
										<li><a href="#" class="btn_del_last"><?php echo utf8::strtoupper(Kohana::lang('ui_main.delete_last'));?></a></li>
										<li><a href="#" class="btn_del_sel"><?php echo utf8::strtoupper(Kohana::lang('ui_main.delete_selected'));?></a></li>
										<li><a href="#" class="btn_clear"><?php echo utf8::strtoupper(Kohana::lang('ui_main.clear_map'));?></a></li>
									</ul>
								</div>
								<div style="clear:both;"></div>
								<?php print form::input('location_find', '', ' title="'.Kohana::lang('ui_main.location_example').'" class="findtext"'); ?>
								<div class="btns" style="float:left;">
									<ul>
										<li><a href="#" class="btn_find"><?php echo utf8::strtoupper(Kohana::lang('ui_main.find_location'));?></a></li>
									</ul>
								</div>
								<div id="find_loading" class="incident-find-loading"></div>
								<div style="clear:both;"><?php echo Kohana::lang('ui_main.pinpoint_location');?>.</div>
							</div>
							<?php Event::run('ushahidi_action.reporter_form_admin_location', $id); ?>
							<div class="row">
								<div class="town">
									<h4><?php echo Kohana::lang('ui_main.reporters_location_name');?>  <span class="required">*</span><br /><span><?php echo Kohana::lang('ui_main.detailed_location_example');?></span></h4>
									<?php print form::input('location_name', $form['location_name'], ' class="text long"'); ?>
								</div>
							</div>
				
						</div>

						<!-- f-col-bottom -->
						<div class="f-col-bottom-container">
							<div class="f-col-bottom">
								<div class="row">
									<h4><?php echo Kohana::lang('ui_main.personal_information');?></span></h4>
									<label>
										<span><?php echo Kohana::lang('ui_main.first_name');?></span>
										<?php print form::input('person_first', $form['person_first'], ' class="text"'); ?>
									</label>
									<label>
										<span><?php echo Kohana::lang('ui_main.last_name');?></span>
										<?php print form::input('person_last', $form['person_last'], ' class="text"'); ?>
									</label>
								</div>
								<div class="row">
									<label>
										<span><?php echo Kohana::lang('ui_main.email_address');?></span>
										<?php print form::input('person_email', $form['person_email'], ' class="text"'); ?>
									</label>
								</div>
							</div>
							
							<!-- f-col-bottom-1 -->
							<div style="clear:both;"></div>
						</div>

						<div class="btns">
							<ul>
								<li><a href="#" class="btn_save"><?php echo utf8::strtoupper(Kohana::lang('ui_main.save_reporter'));?></a></li>
								<li><a href="#" class="btn_save_close"><?php echo utf8::strtoupper(Kohana::lang('ui_main.save_close'));?></a></li>
									<li><a href="#" class="btn_save_add_new"><?php echo utf8::strtoupper(Kohana::lang('ui_main.save_add_new'));?></a></li>
								<?php 
								if($id)
								{
									echo "<li><a href=\"#\" class=\"btn_delete btns_red\">".utf8::strtoupper(Kohana::lang('ui_main.delete_reporter'))."</a></li>";
								}
								?>
								<li><a href="<?php echo url::site().'admin/reporters/';?>" class="btns_red"><?php echo utf8::strtoupper(Kohana::lang('ui_main.cancel'));?></a></li>
							</ul>
						</div>						
					</div>
				<?php print form::close(); ?>
				<?php
				if($id)
				{
					// Hidden Form to Perform the Delete function
					print form::open(url::site().'admin/reporters/', array('id' => 'reporterMain', 'name' => 'reporterMain'));
					$array=array('action'=>'d','reporter_id[]'=>$id);
					print form::hidden($array);
					print form::close();
				}
				?>
			</div>
