<table style="width: 630px;" class="my_table">
<tr>
		<td>
			<span class="big_blue_span"><?php echo Kohana::lang('ui_main.step');?> 1:</span>
		</td>
		<td>
			<h4 class="fix">Statistic 1 (leave title blank if you don't need this slot)</h4>
			<div class="row">
				<h4>Label</h4>
				<?php print form::input('statisticsbox_title1', $form['statisticsbox_title1'], ' class="text title_2"'); ?>
				<br \><h4>Number</h4>
				<?php print form::input('statisticsbox_num1', $form['statisticsbox_num1'], ' class="text title_2"'); ?>
			</div>
		</td>
</tr>							
<tr>
		<td>
			<span class="big_blue_span"><?php echo Kohana::lang('ui_main.step');?> 1:</span>
		</td>
		<td>
			<h4 class="fix">Statistic 2 (leave title blank if you don't need this slot)</h4>
			<div class="row">
				<h4>Label</h4>
				<?php print form::input('statisticsbox_title2', $form['statisticsbox_title2'], ' class="text title_2"'); ?>
				<br \><h4>Number</h4>
				<?php print form::input('statisticsbox_num2', $form['statisticsbox_num2'], ' class="text title_2"'); ?>
			</div>
		</td>
</tr>							
<tr>
		<td>
			<span class="big_blue_span"><?php echo Kohana::lang('ui_main.step');?> 1:</span>
		</td>
		<td>
			<h4 class="fix">Statistic 3 (leave title blank if you don't need this slot)</h4>
			<div class="row">
				<h4>Label</h4>
				<?php print form::input('statisticsbox_title3', $form['statisticsbox_title3'], ' class="text title_2"'); ?>
				<br \><h4>Number</h4>
				<?php print form::input('statisticsbox_num3', $form['statisticsbox_num3'], ' class="text title_2"'); ?>
			</div>
		</td>
</tr>												
</table>