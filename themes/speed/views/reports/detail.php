


<div class="main">
		<h1 class="report-title">
			<?php
				echo html::escape($incident_title);
			?>
		</h1>

		<p class="edit-link">
			<?php
			// If Admin is Logged In - Allow For Edit Link
			if ($logged_in AND $loggedin_role != 'members')
			{
				echo " [&nbsp;<a href=\"".url::site()."admin/reports/edit/".$incident_id."\">"
				    .Kohana::lang('ui_main.edit')."</a>&nbsp;]";
			}
			?>
		</p>


		<p class="report-when-where">
			<span class="r_date"><i class="icon-time"></i>  <?php echo $incident_time.' '.$incident_date; ?> </span><br />
			<?php if ($incident_location) { ?>
				<span class="r_location"><i class="icon-location-arrow"></i>  <?php echo html::specialchars($incident_location); ?></span><br />
			<?php } ?>
			<?php Event::run('ushahidi_action.report_meta_after_time', $incident_id); ?>

			<span class="report-category-list">
				<?php
					foreach ($incident_category as $category)
					{
						// don't show hidden categoies
						if ($category->category->category_visible == 0)
						{
							continue;
						}
						if ($category->category->category_image_thumb)
						{
							$style = "background:transparent url(".url::convert_uploaded_to_abs($category->category->category_image_thumb).") 0 0 no-repeat";
						}
						else
						{
							$style = "background-color:#".$category->category->category_color;
						}

						?>
						<a href="<?php echo url::site()."reports/?c=".$category->category->id; ?>" title="<?php echo Category_Lang_Model::category_description($category->category_id);; ?>">
							<span class="r_cat-box" style="<?php echo $style ?>">&nbsp;</span>
							<?php echo $category->category->parent_id ? Category_Lang_Model::category_title($category->category->parent_id). ": " : ''; ?><?php echo Category_Lang_Model::category_title($category->category_id); ?>
						</a>
						<?php
					}
				?>
				<?php
				// Action::report_meta - Add Items to the Report Meta (Location/Date/Time etc.)
				Event::run('ushahidi_action.report_meta', $incident_id);
				?>
			</span>
			<br />
			<?php
			if ($incident_mode == 4)
			{
				$icon = 'icon-twitter'; $source = Kohana::lang("ui_main.twitter");
			}
			else if ($incident_mode == 3)
			{
				$icon = 'icon-envelope'; $source = Kohana::lang("ui_main.email");
			}
			else if ($incident_mode == 2)
			{
				$icon = 'icon-mobile-phone'; $source = Kohana::lang("ui_main.sms");
			}
			else if ($incident_mode == 1)
			{
				$icon = 'icon-globe';
				$source = Kohana::lang("ui_main.web");
				if (count($incident_videos) > 0)
				{
					$icon = 'icon-facetime-video'; $source = Kohana::lang("ui_main.video");
				}
				elseif (count($incident_photos) > 0)
				{
					$icon = 'icon-camera'; $source = Kohana::lang("ui_main.photos");
				}
				elseif (count($incident_news) > 0)
				{
					$icon = 'icon-rss'; $source = Kohana::lang("ui_main.news");
				}
			}
			?>
			<span><i class="<?php echo $icon; ?>"></i> <?php echo $source; ?></span>

			<h2><?php echo Kohana::lang('ui_main.reports_description');?></h2>
			<p><?php echo html::clean(nl2br($incident_description)); ?></p>
		</p>

<?php if( count($incident_photos) > 0) : ?>
		<?php
		echo '<a class="photothumb" rel="lightbox-group1" href="'.$incident_photos[0]['large'].'"><img alt="'.html::escape($incident_title).'" src="'.$incident_photos[0]['large'].'"/></a> ';
		?>
<?php endif; ?>
</div>

<div id="report-map" class="report-map main">
		<div class="map-holder" id="map"></div>
	  <ul class="map-toggles" style="display:none;">
	    <li><a href="#" class="smaller-map"><?php echo Kohana::lang('ui_main.smaller_map'); ?></a></li>
	    <li style="display:block;"><a href="#" class="wider-map"><?php echo Kohana::lang('ui_main.wider_map'); ?></a></li>
	    <li><a href="#" class="taller-map"><?php echo Kohana::lang('ui_main.taller_map'); ?></a></li>
	    <li><a href="#" class="shorter-map"><?php echo Kohana::lang('ui_main.shorter_map'); ?></a></li>
	  </ul>
	  <div style="clear:both"></div>
	</div>

<div id="main" class="report_detail">

	<div class="left-col">

  	  <?php
    	 /* if ($incident_verified)
    		{
    			echo '<p class="r_verified">'.Kohana::lang('ui_main.verified').'</p>';
    		}
    		else
    		{
    			echo '<p class="r_unverified">'.Kohana::lang('ui_main.unverified').'</p>';
    		}*/
  	  ?>


		<?php
		// Action::report_display_media - Add content just above media section
	    Event::run('ushahidi_action.report_display_media', $incident_id);
		?>

		<!-- start report media -->
		<div class="<?php if( count($incident_photos) > 0 || count($incident_videos) > 0){ echo "report-media";}?>">
			<?php
			// if there are images, show them
			if( count($incident_photos) > 0 )
			{
				echo '<div id="report-images">';
				foreach ($incident_photos as $photo)
				{
					echo '<a class="photothumb" rel="lightbox-group1" href="'.$photo['large'].'"><img alt="'.html::escape($incident_title).'" src="'.$photo['thumb'].'"/></a> ';
				};
				echo '</div>';
			}

			// if there are videos, show those too
			if( count($incident_videos) > 0 )
			{
				echo "<div id=\"report-video\"><ul>\n";
				// embed the video codes
				foreach( $incident_videos as $incident_video)
				{
					echo "<li>\n\t";
					echo $videos_embed->embed($incident_video, FALSE, FALSE);
					echo "\n</li>\n";
				};
  			echo "</ul></div>";

			}
			?>
		</div>

		<!-- start report description -->
		<div class="report-description-text">


			<!-- start news source link -->
			<?php if( count($incident_news) > 0 ) { ?>
			<div class="incident_news">
			<h2><?php echo Kohana::lang('ui_main.reports_news');?></h2>
					<?php
						foreach( $incident_news as $incident_new)
						{
							?>
							<a href="<?php echo $incident_new; ?> " target="_blank"><?php
							echo $incident_new;?></a>
							<br/>
							<?php
						}
			?>
			</div>
			<br>
			<?php } ?>
			<!-- end news source link -->

			<!-- start additional fields -->
			<?php if(strlen($custom_forms) > 0) { ?>
			<div class="custom_forms">
			<h5><?php echo Kohana::lang('ui_main.additional_data');?></h5>
			<?php

				echo $custom_forms;

			?>
			<br/>
			</div>
			<?php } ?>
			<!-- end additional fields -->

			<?php if ($features_count)
			{
				?>
				<br /><br /><h5><?php echo Kohana::lang('ui_main.reports_features');?></h5>
				<?php
				foreach ($features as $feature)
				{
					echo ($feature->geometry_label) ?
					 	"<div class=\"feature_label\"><a href=\"javascript:getFeature($feature->id)\">$feature->geometry_label</a></div>" : "";
					echo ($feature->geometry_comment) ?
						"<div class=\"feature_comment\">$feature->geometry_comment</div>" : "";
				}
			}?>

			<!--<div class="credibility">
				<table class="rating-table" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td><?php echo Kohana::lang('ui_main.credibility');?>:</td>
            <td><a href="javascript:rating('<?php echo $incident_id; ?>','add','original','oloader_<?php echo $incident_id; ?>')"><img id="oup_<?php echo $incident_id; ?>" src="<?php echo url::file_loc('img'); ?>media/img/up.png" alt="UP" title="UP" border="0" /></a></td>
            <td><a href="javascript:rating('<?php echo $incident_id; ?>','subtract','original')"><img id="odown_<?php echo $incident_id; ?>" src="<?php echo url::file_loc('img'); ?>media/img/down.png" alt="DOWN" title="DOWN" border="0" /></a></td>
            <td><a href="" class="rating_value" id="orating_<?php echo $incident_id; ?>"><?php echo $incident_rating; ?></a></td>
            <td><a href="" id="oloader_<?php echo $incident_id; ?>" class="rating_loading" ></a></td>
          </tr>
        </table>
			</div>-->
		</div>

		<?php
            // Action::report_extra - Allows you to target an individual report right after the description
            Event::run('ushahidi_action.report_extra', $incident_id);

			// Filter::comments_block - The block that contains posted comments
			Event::run('ushahidi_filter.comment_block', $comments);
			echo $comments;
		?>

		<?php
			// Filter::comments_form_block - The block that contains the comments form
			Event::run('ushahidi_filter.comment_form_block', $comments_form);
			echo $comments_form;
		?>

	</div>

	<div class="right-col">

		<div class="report-media-box-content">


		</div>

		<?php
			// Action::report_view_sidebar - This gives plugins the ability to insert into the sidebar (below the map and above additional reports)
			Event::run('ushahidi_action.report_view_sidebar', $incident_id);
		?>
<?php /*
		<div class="report-additional-reports">
			<h4><?php echo Kohana::lang('ui_main.additional_reports');?></h4>
			<?php foreach($incident_neighbors as $neighbor) {
				$neighbor_url = url::site("reports/view/$neighbor->id");
				if (isset($neighbor->source) AND $neighbor->source != 'main')
				{
					$neighbor_url = url::site("reports/sharing/view/$neighbor->id");
				}
				?>
			  <div class="rb_report">
  			  <h5><a href="<?php echo $neighbor_url; ?>"><?php echo $neighbor->incident_title; ?></a></h5>
  			  <p class="r_date r-3 bottom-cap"><?php echo date('H:i M d, Y', strtotime($neighbor->incident_date)); ?></p>
  			  <p class="r_location"><?php echo $neighbor->location_name.", ".round($neighbor->distance, 2); ?> Kms</p>
  			</div>
      <?php } ?>
		</div>
*/ ?>
	</div>

	<div style="clear:both;"></div>




</div>
