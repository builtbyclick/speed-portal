

<script type="text/template" id="reports-list-template">
				<?php echo form::open(); ?>
				<div class="row">
					<div class="list">
						<h5><?php echo Kohana::lang('speed.latest_reports'); ?></h5>
						<a class="paused hidden" style="display: none;" href="#"><span class="pause"><?php echo Kohana::lang('speed.pause'); ?></span><span class="resume"><?php echo Kohana::lang('speed.resume'); ?></span></a>
						<a class="filter-button hidden" style="display: none;" href="#"><span class="filters-open"><?php echo Kohana::lang('ui_main.filters'); ?></span><span class="filters-close"><?php echo Kohana::lang('ui_main.hide'); ?></span></a>
						<div class="clearfix">
							<?php echo form::input('q', '', 'placeholder="Enter your search term" class="pull-left"'); ?>
							<?php echo form::submit('submit', 'Go', 'class="btn  pull-right"'); ?>
						</div>


						<div class="loading"><?php echo Kohana::lang('ui_main.loading_reports'); ?></div>
						<ul class="reports-list clearfix">
						</ul>
						<div id="submit-a-report" class="clearfix"><a href="<?php echo url::site("/reports/submit"); ?>" class="btn btn-success pull-right"><?php echo Kohana::lang('ui_main.submit'); ?></a></div>
					</div>
					<div class="filters">
						<h4><?php echo Kohana::lang('ui_main.categories'); ?></h4>
						<?php echo category::form_tree('c'); ?>

						<h4><?php echo Kohana::lang('ui_main.media'); ?></h4>
						<ul>
							<li><label><input type="checkbox" name="m[]" value="1" class="check-box"> Photo</label></li>
							<li><label><input type="checkbox" name="m[]" value="2" class="check-box"> Video</label></li>
							<li><label><input type="checkbox" name="m[]" value="4" class="check-box"> News</label></li>
						</ul>

						<h4><?php echo Kohana::lang('ui_main.source'); ?></h4>
						<ul>
							<li><label><input type="checkbox" name="mode[]" value="1" class="check-box"> Web</label></li>
							<li><label><input type="checkbox" name="mode[]" value="2" class="check-box"> SMS</label></li>
							<li><label><input type="checkbox" name="mode[]" value="3" class="check-box"> Email</label></li>
							<li><label><input type="checkbox" name="mode[]" value="4" class="check-box"> Twitter</label></li>
						</ul>

						<?php
						if (class_exists('Sharing'))
						{

							// Get all active Shares
							$sites = ORM::factory('sharing_site')
								->where('site_active', 1)
								->where('share_reports', 1)
								->find_all();

							if (count($sites) > 0)
							{ ?>
							<?php if (! Kohana::config('config.central')): ?>
							<h4><?php echo Kohana::lang('sharing_two.site_filter'); ?></h4>
							<?php endif; ?>
							<ul>
								<li><label><input type="checkbox" name="sharing[]" value="main" class="check-box"> <?php echo Kohana::config('settings.site_name') ?></label></li>
								<?php

									foreach ($sites as $site)
									{ ?>
								<li><label><input type="checkbox" name="sharing[]" value="<?php echo $site->id ?>" class="check-box"> <?php echo$site->site_name ?></label></li>
									<?php } ?>
							</ul>
						<?php
							}
						}
						?>

						<?php echo form::submit('filter', Kohana::lang('speed.filter'), 'class="btn pull-right"'); ?>
						<?php echo form::input(array('name' => 'Reset', 'type' => 'reset'),  Kohana::lang('ui_main.reset'), ' type="" class="btn pull-right" id="reset"'); ?>
					</div>
				</div>
				<?php echo form::close(); ?>
</script>

<script type="text/template" id="report-item-template">
		<i class="<%- icon  %>"></i> <p><%- name.substr(0,60) %></p>
		<small class="report-item-time  pull-right"><%- date.fromNow() %></small>
	</script>

<script type="text/template" id="report-popover-template">
	<% if (thumbnail) { %><a href="<%- url %>"><img src="<%- thumbnail %>" /></a><% } %>
	<ul>
		<li><?php echo Kohana::lang('ui_main.source'); ?>: <%- source %></li>
		<li><?php echo Kohana::lang('ui_main.category'); ?>: <%- category %></li>
		<li><?php echo Kohana::lang('ui_main.time'); ?>: <span><%- date.format('YYYY-MM-DD HH:mm:ss') %></span></li>
	</ul>
	<p><%- description.substr(0,150) %></p>
	<a href="<%- url %>"><?php echo Kohana::lang('ui_main.view_more'); ?></a>
</script>

<script type="text/template" id="empty-item-template">
	<li class="no-reports"><?php echo Kohana::lang('ui_main.no_reports'); ?></li>
</script>
