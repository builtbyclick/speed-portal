<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/data.js"></script>

<div class="snapshot-box" id="content">
	<div class="content-bg">

		<div class="snapshot-header">
			<div class="snapshot-timefilter">
				In the last:
				<?php
				$timespans = array('1','12','24');
				foreach ($timespans as $t)
				{?>
					<a href="<?php echo url::site("snapshot/?timespan=") . $t ;?>" class="snapshot-timefilter-option<?php echo ($t == $timespan ? " active-link" : null); ?>"><?php echo($t);?> hour<?php if($t <> "1"){print("s");}?></a>
				<?php
				} ?>
			</div>

			<div class="snapshot-reportsnumber"><?php print $totalreports?> reports</div>
		</div>

		<div class="snapshot-halfwidth-container snapshot-map-container">
			<h5>Reports by Location (<a href='/reports?sharing=main&s=<?php echo $start_date; ?>&e=<?php echo $end_date; ?>'>see reports list</a>)</h5>
			<div class="map-container snapshot">
					<?php
					// Map and Timeline Blocks
					echo $div_map;
					?>
			</div>
		</div>

		<div class="snapshot-halfwidth-container snapshot-questions-container">
			<h5>Latest questions</h5>
			<div class="snapshot-question">
				<?php foreach ($recent_questions as $question)
				{ ?>

				<p class="question-title">
					<a href="<?php echo url::site("questions/view/".$question->id); ?>"><?php echo html::escape($question->text);?></a><br>
					<span class="question-metadata">Question from <?php echo($question_authors[$question->id]);?> (<?php echo date('M j Y', strtotime($question->date));?>)</span>
				</p>
				<?php
				} ?>

				<p style="text-align:right;">
					<a href="<?php echo url::site("questions"); ?>">more questions...</a>
				</p>

			</div>
		</div>

		<div class="snapshot-chart-container">
			<h5>Reports by Category</h5>

			<table class="snapshot-table" id="snapshot-table" style="position:relative">
				<thead>
					<tr class="snapshot-table-header">
						<th class="column-25-width">Category</th>
						<th class="column-10-width">Number</th>
						<th class="column-10-width">%</th>
						<th class="column-50-width">Reports</th>
					</tr>
				</thead>
				<tbody>
    				<?php foreach ($catcounts as $catcount)
					{
					?>
						<tr>
						<th><?php print($catcount['categoryname']);?></th>
						<td><?php print($catcount['count']);?></td>
						<td><?php print($catcount['percentage']);?>%</td>
                        <td><div id="chart-container-categories" class="snapshot-chart"></div></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>

		</div>

		<div class="snapshot-chart-container">
			<h5>Reports by Incoming Type</h5>

			<table class="snapshot-table" id="snapshot-table" style="position:relative">
				<thead>
					<tr class="snapshot-table-header">
						<th class="column-25-width">Type</th>
						<th class="column-10-width">Number</th>
						<th class="column-10-width">%</th>
						<th class="column-50-width">Reports</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($sourcecounts as $sourcecount)
					{
					?>
						<tr>
						<th><?php print($sourcecount['sourcename']);?></th>
						<td><?php print($sourcecount['count']);?></td>
						<td><?php print($sourcecount['percentage']);?>%</td>
                        <td><div id="chart-container-types" class="snapshot-chart"></div></td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>

		</div>

		<div class="snapshot-halfwidth-container">
			<h5><?php print($summarybox_title);?></h5>
			<p><?php print($summarybox_text);?></p>
		</div>

		<div class="snapshot-halfwidth-container">
			<h5>Statistics</h5>
			<p class="statistic"><span><?php echo $statsbox_num1; ?></span> <?php echo $statsbox_title1; ?></p>
			<p class="statistic"><span><?php echo $statsbox_num2; ?></span> <?php echo $statsbox_title2; ?></p>
			<p class="statistic"><span><?php echo $statsbox_num3; ?></span> <?php echo $statsbox_title3; ?></p>
		</div>

	</div> <!-- .content-bg -->

	<div>
		<small>Note: This summary only includes reports from the "<?php echo $site_name; ?>" portal. It does not include reports from the "Meta" portal or other agency portals.</small>
	</div>
</div> <!-- .snapshot-box -->

<script>

$(function () {

	// Alter height of charts to match the height of the tables they're in

	var $snapshotCharts = $(".snapshot-chart");

	$snapshotCharts.each(function() {
		var thisTable = $(this).parents('table');
		var thisChartHeight = thisTable.outerHeight() - 6;
		$(this).css({ height: thisChartHeight + "px" });
	});

	// Chart settings
  Highcharts.setOptions({
    chart: {
      type: 'bar',
      spacingTop: 0,
      spacingLeft: 1,
      spacingRight: 1,
      spacingBottom: 12,
      marginTop: 0
    },
    title: {
    	text: null
    },
    credits: {
    	enabled: false
  	},
  	tooltip: {
      shadow: false,
      borderColor: '#ccc',
      borderRadius: 2,
      formatter: function() {
        return this.x +
        	': <b>'+ this.y +'</b>';
      },
      style: {
      	fontFamily: 'Gill Sans',
      	fontSize: '14px'
      }
    },
  	colors: [
  		'#e37f43'
  	],
  	plotOptions: {
      series: {
          pointWidth: 10,
      }
    },
    xAxis: {
      labels: {
      	enabled: false
      },
      lineWidth: 0,
      tickWidth: 0
    },
    yAxis: {
    	title: {
    		enabled: false
    	},
    	lineColor: '#cccccc',
      lineWidth: 1,
      gridLineWidth: 0,
      tickWidth: 1,
      labels: {
      	y: 22,
      	style: {
      		fontFamily: 'Gill Sans',
      		fontSize: '14px'
      	}
      }
    },
    legend: {
    	enabled: false
    }
  });

	// Categories chart
	var cattotals = <?php echo json_encode($cattotals); ?>;
	var catnames= <?php echo json_encode($catnames); ?>;
	var chartCategories = new Highcharts.Chart({
    chart: {
       renderTo: 'chart-container-categories',
    },
    series: [{
  		name: 'Reports',
  		data: cattotals
		}],
		xAxis: {
  		categories: catnames
  	}
	});

	// Incoming types chart
	var sourcetotals = <?php echo json_encode($sourcetotals); ?>;
	var sourcenames= <?php echo json_encode($sourcenames); ?>;
	var chartTypes = new Highcharts.Chart({
    chart: {
       renderTo: 'chart-container-types',
    },
    series: [{
  		name: 'Reports',
  		data: sourcetotals
		}],
		xAxis: {
  		categories: sourcenames
  	}
	});

});

</script>