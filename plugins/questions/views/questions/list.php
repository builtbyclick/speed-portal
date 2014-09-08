<div id="content">
	<div class="content-bg">
		<!-- start block -->
		<div class="big-block">
			<h1><?php echo $page_title ?></h1>
			<ul class="question-list">
				<?php

				// Print out each question
				foreach ($questions as $question)
				{
					?>
					<li><a href="<?php echo url::site("questions/view/".$question->id); ?>">
						<?php echo html::escape(text::limit_chars($question->text, 80, '...', TRUE)); ?>
					</a>
					<span class="date"><?php echo date('M j Y', strtotime($question->date)); ?></span>
					</li>
						<?php
						$a = $question->latest_answer();
						if ($a AND $a->text)
						{
							?>
							<p class="answer">
							<?php echo html::escape($a->text); ?>
							</p>
						<?php
						}
						?>
				<?php } ?>
			</ul>
		</div>
		<!-- end block -->
	</div>
</div>

		<!-- Bottom paginator -->
		<div class="pager-container">
			<?php //echo $stats_breadcrumb; ?>
			<div class="pagination"><?php echo $pagination; ?></div>
		</div>
		<!-- /Bottom paginator -->
