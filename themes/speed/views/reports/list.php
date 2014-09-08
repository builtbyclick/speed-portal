<?php
/**
 * View file for updating the reports display
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team - http://www.ushahidi.com
 * @package    Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */
?>
		<!-- Top reportbox section-->
		<div class="pager-container">
			<?php echo $stats_breadcrumb; ?>
			<div class="pagination"><?php echo $pagination; ?></div>
		</div>
		<!-- /Top reportbox section-->

		<!-- Report listing -->
		<div class="r_cat_tooltip"><a href="#" class="r-3"></a></div>
		<div class="rb_list-and-map-box">
			<div id="rb_list-view">
			<?php
				foreach ($incidents as $incident)
				{
					$incident_id = $incident->incident_id;
					$incident_title = $incident->incident_title;
					$incident_description = $incident->incident_description;
					$incident_url = Incident_Model::get_url($incident_id);
					if (isset($incident->source) AND $incident->source != 'main')
					{
						$incident_url = url::site("reports/sharing/view/$incident_id");
					}
					//$incident_category = $incident->incident_category;
					// Trim to 150 characters without cutting words
					// XXX: Perhaps delcare 150 as constant

					$incident_description = text::limit_chars(html::strip_tags($incident_description), 140, "...", true);
					$incident_date = date('H:i M d, Y', strtotime($incident->incident_date));
					//$incident_time = date('H:i', strtotime($incident->incident_date));
					$location_id = $incident->location_id;
					$location_name = $incident->location_name;
					$incident_verified = $incident->incident_verified;

					if ($incident_verified)
					{
						$incident_verified = '<span class="r_verified">'.Kohana::lang('ui_main.verified').'</span>';
						$incident_verified_class = "verified";
					}
					else
					{
						$incident_verified = '<span class="r_unverified">'.Kohana::lang('ui_main.unverified').'</span>';
						$incident_verified_class = "unverified";
					}

					$comment_count = ORM::Factory('comment')->where('incident_id', $incident_id)->count_all();

					$incident_thumb = FALSE;//url::file_loc('img')."media/img/report-thumb-default.jpg";
					if (isset($incident->source) AND $incident->source != 'main')
						$media = ORM::Factory('media')->join('sharing_incident_media', 'media_id', 'media.id')->where('sharing_incident_id', $incident_id)->find_all();
					else
						$media = ORM::Factory('media')->where('incident_id', $incident_id)->find_all();

					if ($media->count())
					{
						foreach ($media as $photo)
						{

							if ($photo->media_thumb)
							{ // Get the first thumb

								// if already is an url, don't try to url-ize it.
								if (filter_var($photo->media_thumb, FILTER_VALIDATE_URL))
								{
									$incident_thumb = $photo->media_thumb;
									$preview_width = 79;
									$preview_class = " class='small'";
								}
								else
								{
									$incident_thumb = url::convert_uploaded_to_abs($photo->media_medium);

									// When URL builder can't find resource, it returns the URL for base uploads dir
									if (! strstr($photo->media_medium, $incident_thumb))
									{
										$incident_thumb = FALSE;
									}
									else
									{
										$preview_width = 180;
										$preview_class = "";
									}

								}

								if (! empty($incident_thumb))
								{
									break;
								}

							}
						}
					}
				?>
				<div id="incident_<?php echo $incident_id ?>" class="rb_report <?php echo $incident_verified_class; ?>">
					<div class="r_media">
						<?php if (!empty($incident_thumb)): ?><p class="r_photo"> <a href="<?php echo $incident_url; ?>">
							<img<?php echo $preview_class; ?> width="<?php echo $preview_width; ?>" alt="<?php echo html::escape($incident_title); ?>" src="<?php echo $incident_thumb; ?>" /> </a>
						</p><?php endif; ?>

						<!-- Only show this if the report has a video -->
						<p class="r_video" style="display:none;"><a href="#"><?php echo Kohana::lang('ui_main.video'); ?></a></p>

						<?php
						// Action::report_extra_media - Add items to the report list in the media section
						Event::run('ushahidi_action.report_extra_media', $incident_id);
						?>
					</div>

					<div class="r-title">
					<h3><a class="r_title" href="<?php echo $incident_url; ?>">
							<?php echo html::escape($incident_title); ?>
						</a>
						<!--<a href="<?php echo "$incident_url#discussion"; ?>" class="r_comments">
							<?php echo $comment_count; ?></a>
							<?php echo $incident_verified; ?>-->
						</h3>
					</div>

					<div class="r_details">
						<p class="r_date r-3 bottom-cap"><i class="icon-time"></i> <?php echo $incident_date; ?></p>
						<?php if ($location_name) { ?>
						<p class="r_location"><i class="icon-location-arrow"></i> <a href="<?php echo url::site("reports/?l=$location_id"); ?>"><?php echo html::specialchars($location_name); ?></a></p>
						<?php } ?>
						<!-- Category Selector -->
						<div class="r_categories">
							<?php
							if (isset($incident->source) AND $incident->source != 'main')
								$categories = ORM::Factory('category')->join('sharing_incident_category', 'category_id', 'category.id')->where('sharing_incident_id', $incident_id)->find_all();
							else
								$categories = ORM::Factory('category')->join('incident_category', 'category_id', 'category.id')->where('incident_id', $incident_id)->find_all();

							foreach ($categories as $category): ?>

								<?php // Don't show hidden categories ?>
								<?php if($category->category_visible == 0) continue; ?>

								<?php if ($category->category_image_thumb): ?>
									<?php  $category_image = url::convert_uploaded_to_abs($category->category_image_thumb); ?>
									<a class="r_category" href="<?php echo url::site("reports/?c=$category->id") ?>">
										<span class="r_cat-box"><img src="<?php echo $category_image; ?>" height="16" width="16" /></span>
										<span class="r_cat-desc"><?php echo $category->parent_id ? Category_Lang_Model::category_title($category->parent_id). ": " : ''; ?><?php echo Category_Lang_Model::category_title($category->id); ?></span>
									</a>
								<?php else:	?>
									<a class="r_category" href="<?php echo url::site("reports/?c=$category->id") ?>">
										<span class="r_cat-box" style="background-color:#<?php echo $category->category_color;?>;"></span>
										<span class="r_cat-desc"><?php echo $category->parent_id ? Category_Lang_Model::category_title($category->parent_id). ": " : ''; ?><?php echo Category_Lang_Model::category_title($category->id); ?></span>
									</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
						<div class="r_description"><?php echo $incident_description; ?>
						  <!--<a class="btn-show btn-more" href="#incident_<?php echo $incident_id ?>"><?php echo Kohana::lang('ui_main.more_information'); ?> &raquo;</a>
						  <a class="btn-show btn-less" href="#incident_<?php echo $incident_id ?>">&laquo; <?php echo Kohana::lang('ui_main.less_information'); ?></a> -->
						</div>
						<?php
						// Action::report_extra_details - Add items to the report list details section
						Event::run('ushahidi_action.report_extra_details', $incident_id);
						?>
					</div>

				</div>
			<?php } ?>
			</div>
			<div id="rb_map-view">
			</div>
		</div>
		<!-- /Report listing -->

		<!-- Bottom paginator -->
		<div class="pager-container">
			<?php echo $stats_breadcrumb; ?>
			<div class="pagination"><?php echo $pagination; ?></div>
		</div>
		<!-- /Bottom paginator -->

