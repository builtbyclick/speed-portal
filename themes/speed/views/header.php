<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title><?php echo html::specialchars($page_title.$site_name); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php
	echo $live_updates;
	echo $header_block;
	?>
	<?php
	// Action::header_scripts - Additional Inline Scripts from Plugins
	Event::run('ushahidi_action.header_scripts');
	?>
  <script type="text/javascript">
    var baseURL = "<?php echo Kohana::config('config.site_domain'); ?>";
  </script>
	<!-- Font Awesome Icon Font CDN -->
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
</head>

<?php
  // Add a class to the body tag according to the page URI

  // we're on the home page
  if (count($uri_segments) == 0)
  {
  	$body_class = "page-main";
  }
  // 1st tier pages
  elseif (count($uri_segments) == 1)
  {
    $body_class = "page-".$uri_segments[0];
  }
  // 2nd tier pages... ie "/reports/submit"
  elseif (count($uri_segments) >= 2)
  {
    $body_class = "page-".$uri_segments[0]."-".$uri_segments[1];
  }
?>

<body id="page" class="<?php echo $body_class; ?>">

	<?php
	echo $header_nav;
	?>

	<h2 class="feedback-button">
		<a target="_feedback_form" class="btn"
		  href="https://docs.google.com/forms/d/1kWo-HMne4TqqQ8nYoBlBqEIBM8qjxXmXr7hA_pWmh4I/viewform?entry.1203565567&entry.1848984326&entry.327339236&entry.738045227=<?php echo urlencode(url::site() . url::current(TRUE)); ?>">
		  Send Feedback
		  </a>
	</h2>

	<!-- wrapper -->
	<div class="container">

		<!-- header -->
		<div id="header">

			<!-- logo -->
			<?php if ($banner == NULL): ?>
			<div id="logo">
				<h1><span><?php echo $site_name; ?></span></h1>
				<!-- <img src="<?php echo url::base(); ?>/themes/speed/images/wv-logo-symbol.png" alt="" /> -->
			</div>
			<?php else: ?>
			<div id="logo">
				<h1><span><?php echo $site_name; ?></span></h1>
				<a href="<?php echo url::site(); ?>"><img src="<?php echo $banner; ?>" alt="<?php echo $site_name; ?>" /></a>
			</div>
			<?php endif; ?>
			<!-- / logo -->
		</div>
		<!-- / header -->

		<!-- / header item for plugins -->
		<?php
		    // Action::header_item - Additional items to be added by plugins
		  Event::run('ushahidi_action.header_item');
		?>

		<!-- mainmenu -->
		<div id="mainmenu" class="clearingfix">
			<div class="navbar">
				<div class="navbar-inner">
					<ul class="nav">
						<?php //nav::main_tabs($this_page, array()); ?>
						<li <?php if ($this_page == 'home') echo 'class="active"'; ?>><a href="<?php echo url::site(); ?>">Home</a></li>
						<li <?php if ($this_page == 'reports') echo 'class="active"'; ?>><a href="<?php echo url::site('reports'); ?>">Reports</a></li>
						<?php if (! Kohana::config('config.central')): ?>
						<li <?php if ($this_page == 'smap') echo 'class="active"'; ?>><a href="<?php echo url::site('smap'); ?>">Rapid Assessments</a></li>
						<li <?php if ($this_page == 'questions') echo 'class="active"'; ?>><a href="<?php echo url::site('questions'); ?>">Question Forum</a></li>
						<li <?php if ($this_page == 'alerts') echo 'class="active"'; ?>><a href="<?php echo url::site('alerts'); ?>">Get Alerts</a></li>
						<?php endif;?>
						<?php nav::main_tabs($this_page, array('home','reports','reports_submit','questions','alerts')); ?>


						<?php if (! Kohana::config('config.central')): ?>
						<li class="submit-a-report  pull-right <?php if ($this_page == 'reports_submit') echo 'active'; ?>"><a href="<?php echo url::site('reports/submit'); ?>">Submit a Report</a></li>
						<?php endif; ?>
					</ul>

					<?php /* if ($allow_feed == 1) { ?>
					<div class="feedicon"><a href="<?php echo url::site(); ?>feed/"><img alt="<?php echo html::escape(Kohana::lang('ui_main.rss')); ?>" src="<?php echo url::file_loc('img'); ?>media/img/icon-feed.png" style="vertical-align: middle;" border="0" /></a></div>
					<?php } */ ?>

					<!-- searchbox -->
					<div id="searchbox">

						<!-- languages -->
						<?php //echo $languages; ?>
						<!-- / languages -->

						<!-- searchform -->
						<?php //echo $search; ?>
						<!-- / searchform -->

					</div>
					<!-- / searchbox -->
				</div>
			</div>
		</div>
		<!-- / mainmenu -->

		<!-- middle -->
		<div id="middle">

			<div class="speed-left-column">
				<?php
					if ($this_page != 'reports' OR Router::$method != 'index')
					{
						?>
				<div id="latest-updates" class="">
				</div>
				<?php
					}
				?>

				<?php
				// Render first block in left col.
					if ($this_page == 'home')
					{
						echo '<ul class="blocks blocks-left">';
						blocks::render(1, 0);

						echo '<li><div class="content-block dummy">&nbsp;</div></li>';
						echo '</ul>';
					}
				?>
			</div>

