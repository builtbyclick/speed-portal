<!-- start submit answers block -->
<div class="comment-block">
	
	<h5><?php echo Kohana::lang('ui_questions.add_an_answer');?></h5>
	<?php
	if ($form_error)
	{
		?>
		<!-- red-box -->
		<div class="red-box">
			<h3><?php echo Kohana::lang('ui_main.error');?></h3>
			<ul>
				<?php
					foreach ($errors as $error_item => $error_description)
					{
						print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
					}
				?>
			</ul>
		</div>
		<?php
	}
	?>
	<?php print form::open(NULL, array('id' => 'answerForm', 'name' => 'answerForm')); ?>
	<div class="report_row">
		<?php print form::textarea('text', $form['text'], ' rows="4" cols="40" class="textarea long" ') ?>
	</div>
	<?php
	if ( ! $user)
	{
		?>
		<div class="report_row">
			<strong><?php echo Kohana::lang('ui_main.name');?>:</strong><br />
			<?php print form::input('author', $form['author'], ' class="text"'); ?>
			</div>

			<div class="report_row">
			<strong><?php echo Kohana::lang('ui_main.email'); ?>:</strong><br />
			<?php print form::input('email', $form['email'], ' class="text"'); ?>
		</div>
		<?php
	}/*
	else
	{
		?>
		<div class="report_row">
			<strong><?php echo $user->name; ?></strong>
		</div>
		<?php
	}*/
	?>
	<div class="report_row">
		<input name="submit" type="submit" value="<?php echo Kohana::lang('ui_questions.save_answer'); ?>" class="btn  btn-success  btn_blue" />
	</div>
	<?php print form::close(); ?>
	
</div>
<!-- end submit answers block -->
