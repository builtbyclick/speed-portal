<div id="content">
	<div class="content-bg">

		<!-- start question form block -->
		<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'questionForm', 'name' => 'questionForm', 'class' => 'gen_forms')); ?>
		<div class="big-block">
			<h1><?php echo $question_heading; ?></h1>
			<?php if ($form_error): ?>
			<!-- red-box -->
			<div class="red-box alert">
				<strong>Error!</strong>
				<ul>
					<?php
						foreach ($errors as $error_item => $error_description)
						{
							print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
						}
					?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="row">
				<input type="hidden" name="form_id" id="form_id" value="<?php echo $id?>">
			</div>
			<div class="report_left">
				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_questions.question'); ?> <span class="required">*</span></h4>
					<?php print form::textarea('text', $form['text'], ' rows="2" class="textarea long" ') ?>
				</div>
			</div>
				<div class="report_row">
				</div>
				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_categories'); ?></h4>
					<div class="report_category" id="categories">
					<?php
						$selected_categories = (!empty($form['question_category']) AND is_array($form['question_category']))
							? $selected_categories = $form['question_category']
							: array();
						echo category::form_tree('question_category', $selected_categories, 1);
						?>
					</div>
				</div>
				<div class="report_optional">
					<?php if (! $logged_in): ?>
					<div class="report_row">
						<h4><?php echo Kohana::lang('ui_main.name'); ?></h4>
						<?php print form::input('name', $form['name'], ' class="text long"'); ?>
					</div>
					<div class="report_row">
						<h4><?php echo Kohana::lang('ui_main.reports_email'); ?></h4>
						<?php print form::input('email', $form['email'], ' class="text long"'); ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="report_right">
				<div class="report_row">
					<input name="submit" type="submit" value="<?php echo Kohana::lang('ui_main.reports_btn_submit'); ?>" class="btn btn-success" />
				</div>
			</div>
		</div>
		<?php print form::close(); ?>
		<!-- end report form block -->
	</div>
</div>
