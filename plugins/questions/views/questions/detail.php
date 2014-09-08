<div id="main" class="report_detail">

	<div class="left-col">

 	<h2 class="report-title"><?php
			echo html::clean(nl2br($question_text));

			// If Admin is Logged In, include Edit and Delete links
			if ($logged_in AND $loggedin_role != 'members')
			{
				echo " [&nbsp;<a href=\"".url::site()."questions/submit/".$question_id."\">"
				    .Kohana::lang('ui_main.edit')."</a>&nbsp;]";

				echo " [&nbsp;<a href=\"".url::site()."questions/delete/".$question_id."\">"
				    .Kohana::lang('ui_main.delete')."</a>&nbsp;]";
			}
		?></h2>

		<p class="question-author-date">
			<span class="r_date"><?php echo Kohana::lang('ui_questions.question_from', array(html::strip_tags($question_author->name))); ?>&nbsp;(<?php echo $question_date; ?>)</span>
		</p>

		<div class="report-category-list">
		<p>
			<?php
				foreach ($question_category as $category)
				{
					// don't show hidden categoies
					if ($category->category->category_visible == 0)
					{
						continue;
					}
					if ($category->category->category_image_thumb)
					{
						$style = "background:transparent url(".url::convert_uploaded_to_abs($category->category->category_image_thumb).") 0 0 no-repeat";
					}
					else
					{
						$style = "background-color:#".$category->category->category_color;
					}
					
					?>
					<a href="<?php echo url::site()."questions/?c=".$category->category->id; ?>" title="<?php echo Category_Lang_Model::category_description($category->category_id);; ?>">
						<span class="r_cat-box" style="<?php echo $style ?>">&nbsp;</span>
						<?php echo Category_Lang_Model::category_title($category->category_id); ?>
					</a>
					<?php 
				}
			?>
			</p>
		</div>
	</div>
</div>


		<?php
			echo $answers;
		?>
	
<div class="main report_detail">
		<?php
			echo $answers_form;
		?>

	<div style="clear:both;"></div>

</div>
