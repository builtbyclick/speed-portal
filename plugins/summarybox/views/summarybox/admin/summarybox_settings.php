<table style="width: 630px;" class="my_table">
<tr>
		<td>
			<span class="big_blue_span"><?php echo Kohana::lang('ui_main.step');?> 1:</span>
		</td>
		<td>
			<h4 class="fix">Edit the summary box title.</h4>
			<div class="row">
				<h4>Title</h4>
				<?php print form::input('summarybox_title', $form['summarybox_title'], ' class="text title_2"'); ?>
			</div>
		</td>
	</tr>							
<tr>
		<td>
			<span class="big_blue_span"><?php echo Kohana::lang('ui_main.step');?> 2:</span>
		</td>
		<td>
			<h4 class="fix">Edit the summary box text.</h4>
			<div class="row">
				<h4>Text</h4>
				<?php print form::textarea('summarybox_text', $form['summarybox_text'], ' class="text title_2"'); ?>
			</div>
		</td>
	</tr>							
</table>