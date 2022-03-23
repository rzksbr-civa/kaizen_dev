<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.board-title {
		text-align: center;
		font-size: 20px;
	}
	
	.board-value {
		text-align: center;
		font-size: 40px;
		font-weight: bold;
	}
	
	.quadrant-percentage {
		font-size: 24px;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		CLIENT COMPLEXITY & PROFITABILITY REPORT
	</h3>
</div>

<div class="row">
	<div class="col-md-3 col-xs-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title board-title">Low Complexity,<br>High Profitability</div>
			</div>
			<div class="panel-body board-value">
				<?php echo $quadrant_total['low_complexity_high_profitability']; ?>
				<div class="quadrant-percentage">(<?php echo number_format($quadrant_percentages['low_complexity_high_profitability'], 2) . '%'; ?>)</div>
			</div>
		</div>
	</div>
	<div class="col-md-3 col-xs-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title board-title">High Complexity,<br>High Profitability</div>
			</div>
			<div class="panel-body board-value">
				<?php echo $quadrant_total['high_complexity_high_profitability']; ?>
				<div class="quadrant-percentage">(<?php echo number_format($quadrant_percentages['high_complexity_high_profitability'], 2) . '%'; ?>)</div>
			</div>
		</div>
	</div>
	<div class="col-md-3 col-xs-6">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<div class="panel-title board-title">Low Complexity,<br>Low Profitability</div>
			</div>
			<div class="panel-body board-value">
				<?php echo $quadrant_total['low_complexity_low_profitability']; ?>
				<div class="quadrant-percentage">(<?php echo number_format($quadrant_percentages['low_complexity_low_profitability'], 2) . '%'; ?>)</div>
			</div>
		</div>
	</div>
	<div class="col-md-3 col-xs-6">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<div class="panel-title board-title">High Complexity,<br>Low Profitability</div>
			</div>
			<div class="panel-body board-value">
				<?php echo $quadrant_total['high_complexity_low_profitability']; ?>
				<div class="quadrant-percentage">(<?php echo number_format($quadrant_percentages['high_complexity_low_profitability'], 2) . '%'; ?>)</div>
			</div>
		</div>
	</div>
</div>

<div id="client-complexity-and-profitability-chart"></div>