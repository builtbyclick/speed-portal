
	<?php if ($site_message != ''): ?>
		<div class="green-box">
			<h3><?php echo $site_message; ?></h3>
		</div>
	<?php endif; ?>

	<!-- content column -->
	<div id="content" class="clearingfix">

		<div class="map-container">
		<?php
		// Map and Timeline Blocks
		echo $div_map;
		?>
			<div class="map-key">
				<p>Map key</p>
				<ul>
				<li class="singlekey"><span>Individual reports</span></li>
				<li class="clusterkey"><span>10 reports</span></li>
				</ul>
			</div>
		</div>

<!-- right column -->
		<div id="map-filters" class="clearingfix">
			<div id="filter-accordion" class="collapse out">
			<?php
				echo $div_timeline;
				?>

			<?php
			// Action::main_sidebar_pre_filters - Add Items to the Entry Page before filters
			Event::run('ushahidi_action.main_sidebar_pre_filters');
			?>

			<!-- category filters -->
			<div class="cat-filters clearingfix">
				<strong>
					<?php echo Kohana::lang('ui_main.category_filter');?>
					<?php /*<span>
						[<a href="javascript:toggleLayer('category_switch_link', 'category_switch')" id="category_switch_link">
							<?php echo Kohana::lang('ui_main.hide'); ?>
						</a>]
					</span>*/?>
				</strong>

			<ul id="category_switch" class="category-filters">
				<?php
				$color_css = 'class="category-icon swatch" style="background-color:#'.$default_map_all.'"';
				$all_cat_image = '';
				if ($default_map_all_icon != NULL)
				{
					$all_cat_image = html::image(array(
						'src'=>$default_map_all_icon
					));
					$color_css = 'class="category-icon"';
				}
				?>
				<li>
					<a class="active" id="cat_0" href="#">
						<span <?php echo $color_css; ?>><?php echo $all_cat_image; ?></span>
						<span class="category-title"><?php echo Kohana::lang('ui_main.all_categories');?></span>
					</a>
				</li>
				<?php
					foreach ($categories as $category => $category_info)
					{
						$category_title = html::escape($category_info[0]);
						$category_color = $category_info[1];
						$category_image = ($category_info[2] != NULL)
						    ? url::convert_uploaded_to_abs($category_info[2])
						    : NULL;
						$category_description = html::escape(Category_Lang_Model::category_description($category));

						$color_css = 'class="category-icon swatch" style="background-color:#'.$category_color.'"';
						if ($category_info[2] != NULL)
						{
							$category_image = html::image(array(
								'src'=>$category_image,
								));
							$color_css = 'class="category-icon"';
						}

						echo '<li>'
						    . '<a href="#" id="cat_'. $category .'" title="'.$category_description.'">'
						    . '<span '.$color_css.'>'.$category_image.'</span>'
						    . '<span class="category-title">'.$category_title.'</span>'
						    . '</a>';

						// Get Children
						echo '<div class="" id="child_'. $category .'">';
						if (sizeof($category_info[3]) != 0)
						{
							echo '<ul>';
							foreach ($category_info[3] as $child => $child_info)
							{
								$child_title = html::escape($child_info[0]);
								$child_color = $child_info[1];
								$child_image = ($child_info[2] != NULL)
								    ? url::convert_uploaded_to_abs($child_info[2])
								    : NULL;
								$child_description = html::escape(Category_Lang_Model::category_description($child));

								$color_css = 'class="category-icon swatch" style="background-color:#'.$child_color.'"';
								if ($child_info[2] != NULL)
								{
									$child_image = html::image(array(
										'src' => $child_image
									));

									$color_css = 'class="category-icon"';
								}

								echo '<li>'
								    . '<a href="#" id="cat_'. $child .'" title="'.$child_description.'">'
								    . '<span '.$color_css.'>'.$child_image.'</span>'
								    . '<span class="category-title">'.$child_title.'</span>'
								    . '</a>'
								    . '</li>';
							}
							echo '</ul>';
						}
						echo '</div></li>';
					}
				?>
			</ul>

			</div>
			<!-- / category filters -->

			<?php if ($layers): ?>
				<!-- Layers (KML/KMZ) -->
				<div class="layers-filters clearingfix">
					<strong><?php echo Kohana::lang('ui_main.layers_filter');?>
						<span>
							[<a href="javascript:toggleLayer('kml_switch_link', 'kml_switch')" id="kml_switch_link">
								<?php echo Kohana::lang('ui_main.hide'); ?>
							</a>]
						</span>
					</strong>
				</div>
				<ul id="kml_switch" class="category-filters">
				<?php
					foreach ($layers as $layer => $layer_info)
					{
						$layer_name = $layer_info[0];
						$layer_color = $layer_info[1];
						$layer_url = $layer_info[2];
						$layer_file = $layer_info[3];

						$layer_link = ( ! $layer_url)
						    ? url::base().Kohana::config('upload.relative_directory').'/'.$layer_file
						    : $layer_url;

						echo '<li>'
						    . '<a href="#" id="layer_'. $layer .'">'
						    . '<span class="swatch" style="background-color:#'.$layer_color.'"></span>'
						    . '<span class="layer-name">'.$layer_name.'</span>'
						    . '</a>'
						    . '</li>';
					}
				?>
				</ul>
				<!-- /Layers -->
			<?php endif; ?>

			<!-- filters -->
			<div class="filters clearingfix">
				<div class="media-filters filter-list-container">
					<strong><?php echo Kohana::lang('ui_main.media_filter'); ?></strong>
					<ul>
						<li><a id="media_0" class="active" href="#"><span><?php echo Kohana::lang('ui_main.all'); ?></span></a></li>
						<li><a id="media_4" href="#"><span><?php echo Kohana::lang('ui_main.news'); ?></span></a></li>
						<li><a id="media_1" href="#"><span><?php echo Kohana::lang('ui_main.pictures'); ?></span></a></li>
						<li><a id="media_2" href="#"><span><?php echo Kohana::lang('ui_main.video'); ?></span></a></li>
					</ul>
				</div>

				<div class="mode-filters filter-list-container">
					<strong><?php echo Kohana::lang('ui_main.source'); ?></strong>
					<ul>
						<li><a id="mode_0" class="active" href="#"><span><?php echo Kohana::lang('ui_main.all'); ?></span></a></li>
						<li><a id="mode_1" href="#"><span><?php echo Kohana::lang('ui_main.web'); ?></span></a></li>
						<li><a id="mode_2" href="#"><span><?php echo Kohana::lang('ui_main.sms'); ?></span></a></li>
						<li><a id="mode_3" href="#"><span><?php echo Kohana::lang('ui_main.email'); ?></span></a></li>
						<li><a id="mode_4" href="#"><span><?php echo Kohana::lang('ui_main.twitter'); ?></span></a></li>
					</ul>
				</div>

				<?php
				// Action::main_filters - Add items to the main_filters
				Event::run('ushahidi_action.map_main_filters');
				?>

			<?php
			// Action::main_sidebar_post_filters - Add Items to the Entry Page after filters
			Event::run('ushahidi_action.main_sidebar_post_filters');
			?>

			</div>
			<!-- / filters -->

<?php /*
			<!-- additional content -->
			<?php if (Kohana::config('settings.allow_reports')): ?>
				<div class="additional-content">
					<h5><?php echo Kohana::lang('ui_main.how_to_report'); ?></h5>

					<div class="how-to-report-methods">

						<!-- Phone -->
						<?php if ( ! empty($phone_array)): ?>
						<div>
							<?php echo Kohana::lang('ui_main.report_option_1'); ?>
							<?php foreach ($phone_array as $phone): ?>
								<?php echo $phone; ?><br />
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<!-- External Apps -->
						<?php if (count($external_apps) > 0): ?>
						<div>
							<strong><?php echo Kohana::lang('ui_main.report_option_external_apps'); ?>:</strong><br/>
							<?php foreach ($external_apps as $app): ?>
								<a href="<?php echo $app->url; ?>"><?php echo $app->name; ?></a><br/>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<!-- Email -->
						<?php if ( ! empty($report_email)): ?>
						<div>
							<strong><?php echo Kohana::lang('ui_main.report_option_2'); ?>:</strong><br/>
							<a href="mailto:<?php echo $report_email?>"><?php echo $report_email?></a>
						</div>
						<?php endif; ?>

						<!-- Twitter -->
						<?php if ( ! empty($twitter_hashtag_array)): ?>
						<div>
							<strong><?php echo Kohana::lang('ui_main.report_option_3'); ?>:</strong><br/>
							<?php foreach ($twitter_hashtag_array as $twitter_hashtag): ?>
								<span>#<?php echo $twitter_hashtag; ?></span>
								<?php if ($twitter_hashtag != end($twitter_hashtag_array)): ?>
									<br />
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<!-- Web Form -->
						<div>
							<a href="<?php echo url::site().'reports/submit/'; ?>">
								<?php echo Kohana::lang('ui_main.report_option_4'); ?>
							</a>
						</div>

					</div>

				</div>
			<?php endif; ?>

			<!-- / additional content -->

			<!-- Checkins -->
			<?php if (Kohana::config('settings.checkins')): ?>
			<br/>
			<div class="additional-content">
				<h5><?php echo Kohana::lang('ui_admin.checkins'); ?></h5>
				<div id="cilist"></div>
			</div>
			<?php endif; ?>
			<!-- /Checkins -->
*/?>
			<?php
			// Action::main_sidebar - Add Items to the Entry Page Sidebar
			Event::run('ushahidi_action.main_sidebar');
			?>

			</div>
			<button type="button" id="show-map-filters" class="btn btn-success btn-block collapsed" data-toggle="collapse" data-target="#filter-accordion">
				<span class="collapsed-text"><i class="icon-chevron-sign-down"></i> Show Filters <i class="icon-chevron-sign-down"></i></span>
				<span class="open-text"><i class="icon-chevron-sign-up"></i> Hide Filters <i class="icon-chevron-sign-up"></i></span>
			</button>
		</div>
		<!-- / right column -->

		<ul class="blocks blocks-main">
			<!-- content blocks -->
				<?php blocks::render(FALSE, 1); ?>
			<!-- /content blocks -->
		</ul>
	</div>
	<!-- content container -->

