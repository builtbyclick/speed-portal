<div id="content">
	<div class="content-bg">
		<!-- start block -->
		<div class="big-block">

			<div class="row">
				<?php echo form::open('questions/submit', array('method' => 'get')); ?>
				<?php echo form::input('q', '', 'placeholder="Ask a Question" class="span7  ask-a-question-input  pull-left"'); ?>
				<?php echo form::submit('submit', 'Ask', 'class="span1  btn   pull-right"'); ?>
				<?php echo form::close(); ?>
			</div>
			<h1>Unanswered</h1>
			<div class="questions">
				<ul class="question-list">
				<?php
				foreach ($questions_unanswered as $question)
				{
				?>
					<li><a href="<?php echo url::site("questions/view/".$question->id); ?>">
						<?php echo html::escape($question->text); ?>
					</a>
					<span class="date"><?php echo date('M j Y', strtotime($question->date)); ?></span>
					</li>
				<?php } ?>
				</ul>
				<a href="<?php echo url::site("questions/unanswered/"); ?>">View more...</a>
			</div>

			<h1>Recently Updated</h1>
			<ul class="question-list">
				<?php

				// Print out each question
				foreach ($questions_recent as $question)
				{
					?>
					<li><a href="<?php echo url::site("questions/view/".$question->id); ?>">
						<?php echo html::escape(text::limit_chars($question->text, 80, '...', TRUE)); ?>
					</a>
					<span class="date"><?php echo date('M j Y', strtotime($question->date)); ?></span>
					</li>
					<p class="answer">
						<?php
						$a = $question->latest_answer();
						if ($a)
						{
							echo html::escape($a->text);
						}
						?>
					</p>
				<?php } ?>
			</ul>
			<a href="<?php echo url::site("questions/recent/"); ?>">View more...</a>

			<?php
				/*foreach ($questions as $question)
				{
					$question_id = $question->id;
					$question_title = text::limit_chars($question->title, 40, '...', True);
					$question_date = date('M j Y', strtotime($question->date));
					//$question_source = text::limit_chars($question->question->question_name, 15, "...");

					print "<div class=\"report_row1\">";
					print "		<div class=\"report_details report_col2\">";
					print "			<h3><a href=\"".url::site().'questions/view/'.$question_id."\">" . $question_title . "</a></h3>";
					print "		</div>";
					print "		<div class=\"report_date report_col3\">";
					print $question_date;
					print "		</div>";
					print "		<div class=\"report_location report_col4\">";
					//print $question_source;
					print "		</div>";
					print "</div>";
				}*/
			?>
		</div>
		<!-- end block -->
	</div>
</div>
