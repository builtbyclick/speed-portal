<table style="width: 630px;" class="my_table">
<tr>
	<td>
		<span class="big_blue_span"><?php echo Kohana::lang('smap.create_smap_link');?>:</span>
	</td>
	<td>
		<div class="row">
			<h4>SMAP site title:</h4>
			<?php print form::input('smap_title', $form['smap_title'], ' class="text title_2"'); ?>
			<br \><h4>SMAP url:</h4>
			<?php print form::input('smap_url', $form['smap_url'], ' class="text title_2"'); ?>
			<br \><h4>SMAP username:</h4>
			<?php print form::input('smap_username', $form['smap_username'], ' class="text title_2"'); ?>
			<br \><h4>SMAP password:</h4>
			<?php print form::input('smap_password', $form['smap_password'], ' class="text title_2"'); ?>
		</div>
	</td>
</tr>
<tr>
<td> 
</td>
</tr>
<tr>
	<td>
		<span class="big_blue_span"><?php echo Kohana::lang('smap.delete_smap_data');?>:</span>
	</td>
	<td>
		<div class="row">
			<div class="f-col-bottom-1-col"><?php echo Kohana::lang('smap.delete_all_smap_reports_and_messages');?>?</div>
			<input type="radio" name="delete_reports" value="1"
			<?php if ($form['delete_reports'] == 1)
			{
				echo " checked=\"checked\" ";
			}?>> <?php echo Kohana::lang('ui_main.yes');?>
			<input type="radio" name="delete_reports" value="0"
			<?php if ($form['delete_reports'] == 0)
			{
				echo " checked=\"checked\" ";
			}?>> <?php echo Kohana::lang('ui_main.no');?>
		</div>
		<div class="row">
			<div class="f-col-bottom-1-col"><?php echo Kohana::lang('smap.delete_all_smap_reporters');?>?</div>
			<input type="radio" name="delete_reporters" value="1"
			<?php if ($form['delete_reporters'] == 1)
			{
				echo " checked=\"checked\" ";
			}?>> <?php echo Kohana::lang('ui_main.yes');?>
			<input type="radio" name="delete_reporters" value="0"
			<?php if ($form['delete_reporters'] == 0)
			{
				echo " checked=\"checked\" ";
			}?>> <?php echo Kohana::lang('ui_main.no');?>
		</div>
	</td>
</tr>							
</table>