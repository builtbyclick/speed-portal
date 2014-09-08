<?php if(count($question_answers) > 0): ?>

	<?php foreach($question_answers as $answer): ?>
		<div class="main report-comment-box">

			<h3 class="answer-text"><?php echo html::escape($answer->text); ?></h3>

			<div class="answer-author">
				<?php echo Kohana::lang('ui_questions.answered_by', array(html::strip_tags($answer->author))); ?>&nbsp;(<?php echo date('M j Y', strtotime($answer->date)); ?>)
			</div>

		</div>
	<?php endforeach; ?>

<?php endif; ?>
