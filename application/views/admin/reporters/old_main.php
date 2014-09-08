<?php
/**
 * Reporters view page.
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
					<?php admin::reporters_subtabs("view"); ?>
				</h2>
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<ul class="tabset">
						<li>
						  <?php admin::reporters_subsubtabs(0); //FIXIT: set this to $channel ?>
						</li>
					</ul>
					<!-- tab -->
					<div class="tab action-tab active">
						<ul>
							<?php if (Auth::instance()->has_permission('reporters_edit')): ?>
							<li><a href="#" onclick="reporterAction('d','<?php echo utf8::strtoupper(Kohana::lang('ui_main.delete')); ?>', '');">
								<?php echo Kohana::lang('ui_main.delete');?></a>
							</li>
							<?php endif; ?>
						</ul>
						
						<div class="sort_by">
							<?php print form::open(NULL, array('method' => 'get', 'class' => 'sort-form')); ?>
							<?php echo Kohana::lang('ui_main.sort_by'); ?>
							<?php echo form::dropdown('order', array(
								'lastname' => Kohana::lang('ui_admin.reporter_surname'),
								'date' => Kohana::lang('ui_admin.reporter_date'),
							), $order_field); 
							echo form::input(array(
									'type'  => 'hidden',
									'name'  => 'sort',
									'value' => $sort,
									'class' => 'sort-field'
								));
							echo form::hidden('status', $status);
							echo form::close(); ?>
						</div>
					</div>
					
				</div>
				<?php if ($form_error): ?>
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
				<?php endif; ?>
				
				<?php if ($form_saved): ?>
					<!-- green-box -->
					<div class="green-box" id="submitStatus">
						<h3><?php echo Kohana::lang('ui_main.reporters');?> 
							<?php echo $form_action; ?> 
							<a href="#" id="hideMessage" class="hide"><?php echo Kohana::lang('ui_main.hide_this_message'); ?></a>
						</h3>
					</div>
				<?php endif; ?>
				
				<!-- report-table -->
				<?php print form::open(NULL, array('id' => 'reporterMain', 'name' => 'reporterMain')); ?>
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="reporter_id[]" id="reporter_single" value="">
					<div class="table-holder">
						<table class="table">
							<thead>
								<tr>
									<th class="col-1">
										<input id="checkallreporters" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'reporter_id[]' )" />
									</th>
									<th class="col-2"><?php echo Kohana::lang('ui_main.reporter_details');?></th>
									<th class="col-3"><?php echo Kohana::lang('ui_main.date');?></th>
									<th class="col-4">
										<a class="sort sort-<?php echo $sort; ?>" title="<?php echo ($sort == 'ASC') ? Kohana::lang('ui_main.ascending') : Kohana::lang('ui_main.descending'); ?>" href="#"></a>
										<?php echo Kohana::lang('ui_main.actions');?>
									</th>
								</tr>
							</thead>
							<tfoot>
								<tr class="foot">
									<td colspan="4">
										<?php echo $pagination; ?>
									</td>
								</tr>
							</tfoot>
							<tbody>
							<?php if ($total_items == 0): ?>
								<tr>
									<td colspan="4" class="col">
										<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
									</td>
								</tr>
							<?php endif; ?>
							<?php
								foreach ($reporters as $reporter)
								{
									$reporter_id = $reporter->id;
									$reporter_date = $reporter->reporter_date;
									$reporter_date = date('Y-m-d', strtotime($reporter->reporter_date));
									$reporter_location = $reporter->location_id ? $reporter->location_name : Kohana::lang('ui_main.none');
									
									// Get the reporter ORM
									$reporter_orm = ORM::factory('reporter', $reporter_id);
																											
									?>
									<tr>
										<td class="col-1">
											<input name="reporter_id[]" id="reporter" value="<?php echo $reporter_id; ?>" type="checkbox" class="check-box"/>
										</td>
										<td class="col-2">
											<div class="post">
												<div class="reporter-id"><a href="<?php echo url::site() . 'admin/reporters/edit/' . $reporter_id; ?>" class="more">#<?php echo $reporter_id; ?></a></div>
											</div>
											<ul class="info">
												<li class="none-separator"><?php echo Kohana::lang('ui_main.location');?>: 
													<strong><?php echo html::specialchars($reporter_location); ?></strong>
												</li>
												<li><?php echo Kohana::lang('ui_main.submitted_by', array($submit_by, $submit_mode));?>
												</li>
											</ul>
											<?php
											
											// Action::report_extra_admin - Add items to the report list in admin
											Event::run('ushahidi_action.report_extra_admin', $reporter);
											?>
										</td>
										<td class="col-3"><?php echo $reporter_date; ?></td>
										<td class="col-4">
											<ul>
											<?php if (Auth::instance()->has_permission('reporters_edit')): ?>
												<li>
													<a href="#" class="del" onclick="reportAction('d','DELETE', '<?php echo $reporter_id; ?>');">
														<?php echo Kohana::lang('ui_main.delete');?>
													</a>
												</li>
												<?php endif; ?>
											</ul>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</div>
				<?php print form::close(); ?>
				<div class="tabs">
					<div class="tab">
						<ul>
						<li><a href="#" onclick="reportAction('d','<?php echo utf8::strtoupper(Kohana::lang('ui_main.delete')); ?>', '');">
							<?php echo Kohana::lang('ui_main.delete');?></a>
						</li>
						</ul>
					</div>
				</div>
			</div>
