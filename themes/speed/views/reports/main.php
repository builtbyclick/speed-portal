

	<div id="filters-box">
		<h5><?php echo Kohana::lang('ui_main.filter_reports'); ?></h5>
		<div id="accordion">

			<h5>
				<a href="#" class="small-link-button f-clear reset" onclick="removeParameterKey('c', 'fl-categories');"><?php echo Kohana::lang('ui_main.clear')?></a>
				<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.category')?></a>
			</h5>
			<div class="f-category-box">
				<ul class="filter-list fl-categories" id="category-filter-list">
					<li>
						<a href="#"><?php
						$all_cat_image = '&nbsp';
						$all_cat_image = '';
						if($default_map_all_icon != NULL) {
							$all_cat_image = html::image(array('src'=>$default_map_all_icon));
						}
						?>
						<span class="item-swatch" style="background-color: #<?php echo Kohana::config('settings.default_map_all'); ?>"><?php echo $all_cat_image ?></span>
						<span class="item-title"><?php echo Kohana::lang('ui_main.all_categories'); ?></span>
						<span class="item-count" id="all_report_count"><?php echo $report_stats->total_reports; ?></span>
						</a>
					</li>
					<?php echo $category_tree_view; ?>
				</ul>
			</div>

			<h5>
				<a href="#" class="small-link-button f-clear reset" onclick="removeParameterKey('radius', 'f-location-box');removeParameterKey('start_loc', 'f-location-box');">
					<?php echo Kohana::lang('ui_main.clear')?>
				</a>
				<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.location'); ?></a></h5>
			<div class="f-location-box">
				<?php echo $alert_radius_view; ?>
				<p></p>
			</div>

			<h5>
				<a href="#" class="small-link-button f-clear reset" onclick="removeParameterKey('mode', 'fl-incident-mode');">
					<?php echo Kohana::lang('ui_main.clear')?>
				</a>
				<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.type')?></a>
			</h5>
			<div class="f-type-box">
				<ul class="filter-list fl-incident-mode">
					<li>
						<a href="#" id="filter_link_mode_1">
							<span class="item-icon icon-globe"></span>
							<span class="item-title"><?php echo Kohana::lang('ui_main.web_form'); ?></span>
						</a>
					</li>

				<?php foreach ($services as $id => $name): ?>
					<?php
						$item_class = "";
						if ($id == 1) $item_class = "icon-mobile-phone";
						if ($id == 2) $item_class = "icon-envelope";
						if ($id == 3) $item_class = "icon-twitter";
					?>
					<li>
						<a href="#" id="filter_link_mode_<?php echo ($id + 1); ?>">
							<span class="item-icon <?php echo $item_class; ?>"></span>
							<span class="item-title"><?php echo $name; ?></span>
						</a>
					</li>
				<?php endforeach; ?>

				</ul>
			</div>

			<h5>
				<a href="#" class="small-link-button f-clear reset" onclick="removeParameterKey('m', 'fl-media');"><?php echo Kohana::lang('ui_main.clear')?></a>
				<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.media');?></a>
			</h5>
			<div class="f-media-box">
				<p><?php echo Kohana::lang('ui_main.filter_reports_contain'); ?>&hellip;</p>
				<ul class="filter-list fl-media">
					<li>
						<a href="#" id="filter_link_media_1">
							<span class="item-icon icon-camera"></span>
							<span class="item-title"><?php echo Kohana::lang('ui_main.photos'); ?></span>
						</a>
					</li>
					<li>
						<a href="#" id="filter_link_media_2">
							<span class="item-icon icon-facetime-video"></span>
							<span class="item-title"><?php echo Kohana::lang('ui_main.video'); ?></span>
						</a>
					</li>
					<li>
						<a href="#" id="filter_link_media_4">
							<span class="item-icon icon-rss"></span>
							<span class="item-title"><?php echo Kohana::lang('ui_main.reports_news')?></span>
						</a>
					</li>
				</ul>
			</div>

			<h5>
				<a href="#" class="small-link-button f-clear reset" onclick="removeParameterKey('v', 'fl-verification');">
					<?php echo Kohana::lang('ui_main.clear'); ?>
				</a>
				<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.verification'); ?></a>
			</h5>
			<div class="f-verification-box">
				<ul class="filter-list fl-verification">
					<li>
						<a href="#" id="filter_link_verification_1">
							<span class="item-icon ic-verified">&nbsp;</span>
							<span class="item-title"><?php echo Kohana::lang('ui_main.verified'); ?></span>
						</a>
					</li>
					<li>
						<a href="#" id="filter_link_verification_0">
							<span class="item-icon ic-unverified">&nbsp;</span>
							<span class="item-title"><?php echo Kohana::lang('ui_main.unverified'); ?></span>
						</a>
					</li>

				</ul>
			</div>
			<h5>
				<a href="#" class="small-link-button f-clear reset" onclick="removeParameterKey('cff', 'fl-customFields');">
					<?php echo Kohana::lang('ui_main.clear'); ?>
				</a>
				<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.custom_fields'); ?></a>
			</h5>
			<div class="f-customFields-box">
				<?php echo $custom_forms_filter; ?>

			</div>
			<?php
				// Action, allows plugins to add custom filters
				Event::run('ushahidi_action.report_filters_ui');
			?>
		</div>
		<!-- end #accordion -->

		<div id="filter-controls">
			<p>
				<a href="#" id="applyFilters" class="btn  filter-button"><?php echo Kohana::lang('ui_main.filter_reports'); ?></a>
				<a href="#" class="btn  btn-link  small-link-button" id="reset_all_filters"><?php echo Kohana::lang('ui_main.reset_all_filters'); ?></a>
			</p>
			<?php
			// Action, allows plugins to add custom filter controls
			Event::run('ushahidi_action.report_filters_controls_ui');
			?>
		</div>
	</div>
	<!-- end #filters-box -->

<div id="content">
	<div class="content-bg">
		<!-- start reports block -->
		<div class="big-block">
			<h5 class="heading">
				<?php echo Kohana::lang('ui_main.showing_reports_from', array(date('M d, Y', $oldest_timestamp), date('M d, Y', $latest_timestamp))); ?>
				<br /><small><a href="#" class="btn-change-time ic-time"><?php echo Kohana::lang('ui_main.change_date_range'); ?></small></a>
			</h5>

			<div id="tooltip-box">
				<div class="tt-arrow"></div>
				<ul class="inline-links">
					<li>
						<a title="<?php echo Kohana::lang('ui_main.all_time'); ?>" class="btn-date-range active" id="dateRangeAll" href="#">
							<?php echo Kohana::lang('ui_main.all_time')?>
						</a>
					</li>
					<li>
						<a title="<?php echo Kohana::lang('ui_main.today'); ?>" class="btn-date-range" id="dateRangeToday" href="#">
							<?php echo Kohana::lang('ui_main.today'); ?>
						</a>
					</li>
					<li>
						<a title="<?php echo Kohana::lang('ui_main.this_week'); ?>" class="btn-date-range" id="dateRangeWeek" href="#">
							<?php echo Kohana::lang('ui_main.this_week'); ?>
						</a>
					</li>
					<li>
						<a title="<?php echo Kohana::lang('ui_main.this_month'); ?>" class="btn-date-range" id="dateRangeMonth" href="#">
							<?php echo Kohana::lang('ui_main.this_month'); ?>
						</a>
					</li>
				</ul>

				<p class="labeled-divider"><span><?php echo Kohana::lang('ui_main.choose_date_range'); ?>:</span></p>
				<?php echo form::open(NULL, array('method' => 'get')); ?>
					<table class="report-date-filter">
						<tr>
							<td><strong>
								<?php echo Kohana::lang('ui_admin.from')?>:</strong><input id="report_date_from" type="text" />
							</td>
							<td>
								<strong><?php echo ucfirst(strtolower(Kohana::lang('ui_admin.to'))); ?>:</strong>
								<input id="report_date_to" type="text" />
							</td>
							<td valign="">
								<a href="#" id="applyDateFilter" class="btn  btn-success  filter-button"><?php echo Kohana::lang('ui_main.go')?></a>
							</td>
						</tr>
					</table>
				<?php echo form::close(); ?>
			</div>

		</div>
	</div>
</div>
				<!-- reports-box -->
				<div id="reports-box">
					<?php echo $report_listing_view; ?>
				</div>
				<!-- end #reports-box -->

