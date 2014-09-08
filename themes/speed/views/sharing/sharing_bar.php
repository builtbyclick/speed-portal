<div class="site-filters filter-list-container">
	<strong><?php echo Kohana::lang('sharing_two.site_filter');?>
	</strong>
	<ul id="sharing_switch" class="category-filters">
		<li><a href="#" id="share_all" <?php if (Kohana::config('sharing_two.default_sharing_filter') == 'all') echo' class="active"'; ?>>
			<span class="swatch" style="background-color:#<?php echo Kohana::config('settings.default_map_all'); ?>"></span>
			<span><?php echo Kohana::lang('sharing_two.all_sites') ?></span>
		</a></li>
		<?php if (! Kohana::config('config.central')): ?>
		<li><a href="#" id="share_main"<?php if (Kohana::config('sharing_two.default_sharing_filter') == 'main') echo' class="active"'; ?>>
			<span class="swatch" style="background-color:#<?php echo Kohana::config('settings.default_map_all'); ?>"></span>
			<span><?php echo Kohana::config('settings.site_name') ?></span>
		</a></li>
		<?php endif; ?>
		<?php

		foreach ($sites as $site)
		{
			$class = (Kohana::config('sharing_two.default_sharing_filter') == $site->id) ? "active" : '';
			echo '<li><a href="#" id="share_'. $site->id .'" class="'.$class.'"><div class="swatch" style="background-color:#'.$site->site_color.'"></div>
			<div>'.$site->site_name.'</div></a></li>';
		}
		?>
	</ul>
</div>
