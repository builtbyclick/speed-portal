<?php blocks::open("questions"); ?>
<?php blocks::title('Question Forum'); ?>
<ul class="icons-ul question-list-block">
<?php 
if ($questions->count() != 0)
{
	foreach ($questions as $question)
	{
	?>
		<li><i class="icon-li icon-comments"></i><a href="<?php echo url::site().'questions/view/'.$question->id; ?>"> <?php echo html::escape(text::limit_chars($question->text, 50, '...', TRUE)); ?></a></li>
<?php
	}
}
?>
</ul>

<p><a class="more btn btn-small pull-right" href="<?php echo url::site() . 'questions' ?>"><?php echo Kohana::lang('ui_main.view_more'); ?></a>
<?php blocks::close();?></p>
