<?php
/**
 * Site view page.
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
			<div class="bg content">
				<h1><?php echo Kohana::lang('ui_admin.my_profile');?></h1>
				<?php print form::open(); ?>
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
							<h3><?php echo Kohana::lang('ui_main.profile_saved');?></h3>
						</div>
					<?php
					}
					?>
					<!-- column -->
					<div class="sms_holder">

						<?php Event::run('ui_admin.profile_shown'); ?>

						<div class="">
							<h4><a href="#" data-toggle="tooltip" data-placement="right" title="<?php echo Kohana::lang("tooltips.profile_password"); ?>"><?php echo Kohana::lang('ui_main.current_password'); ?></a> <span class="required">*</span></h4>
							<?php print form::password('current_password', '', ' class="text"'); ?>
						</div>

						<div class="">
							<h4><a href="#" data-toggle="tooltip" data-placement="right" title="<?php echo Kohana::lang("tooltips.profile_name"); ?>"><?php echo Kohana::lang('ui_main.full_name');?></a> <span class="required">*</span></h4>
							<?php print form::input('name', $form['name'], ' class="text long2"'); ?>
						</div>

						<div class="">
							<h4><?php echo Kohana::lang('ui_main.username');?></h4>
							<?php print form::input('username', $form['username'], ' class="text short3"'); ?>
						</div>

						<div class="">
							<h4><a href="#" data-toggle="tooltip" data-placement="right" title="<?php echo Kohana::lang("tooltips.profile_email"); ?>"><?php echo Kohana::lang('ui_main.email');?></a> <span class="required">*</span></h4>
							<?php print form::input('email', $form['email'], ' class="text long2"'); ?>
						</div>

						<div class="">
							<h4><a href="#" data-toggle="tooltip" data-placement="right" title="<?php echo Kohana::lang("tooltips.profile_new_password"); ?>"><?php echo Kohana::lang('ui_main.new_password');?></a></h4>
							<?php print form::password('new_password', $form['new_password'], ' class="text"'); ?>
						</div>

						<div class="">
							<h4><?php echo Kohana::lang('ui_main.password_again');?></h4>
							<?php print form::password('password_again', $form['password_again'], ' class="text"'); ?>
						</div>

						<?php if ($loggedin_role == 'admin'): ?>
						<div class="">
							<h4><?php echo Kohana::lang('ui_main.receive_notifications');?>?</h4>
							<?php print form::dropdown('notify', $yesno_array, $form['notify']); ?>
						</div>
						<?php endif; ?>

						<div class="gravatar">
							<h4>Profile Picture</h4>
							<a href="http://www.gravatar.com/" target="_blank"><img src="<?php echo members::gravatar($form['email']); ?>" width="160" border="0" /></a>
							<h4><a href="http://www.gravatar.com/" target="_blank" data-toggle="tooltip" data-placement="right" title="<?php echo Kohana::lang("tooltips.change_picture"); ?>"><?php echo Kohana::lang('ui_main.change_picture');?></a></h4>
						</div>

					</div>

					<input type="submit" class="btn  btn-success  save-rep-btn" value="<?php echo Kohana::lang('ui_admin.save_settings');?>" />
				</div>
				<?php print form::close(); ?>
			</div>
