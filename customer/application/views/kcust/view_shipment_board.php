<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

<style type="text/css">
	.shipment-board-title {
		text-align: center;
		font-size: 20px;
	}
	
	.shipment-board-value {
		text-align: center;
		font-size: 40px;
		font-weight: bold;
	}
	
	.green-text {
		color: #66CC66;
	}
	
	.red-text {
		color: #B22222;
	}
	
	.shipment-board-hourly-title {
		text-align: center;
		font-size: 20px;
	}
	
	.shipment-board-hourly-value {
		text-align: center;
		font-size: 24px;
		font-weight: bold;
	}
	
	.shipment-board-subvalue {
		text-align: center;
		font-size: 24px;
		font-weight: bold;
	}
	
	.info-table td {
		font-size: 14px;
	}
	
	.info-table-label {
		width: 60%;
	}
	
	.info-table-value {
		width: 40%;
		text-align: right;
		font-weight: bold;
	}
	
	.shipment-board-chart-wrapper {
		width: 100%;
		overflow-x: scroll;
	}
	
	.shipment-board-chart {
		min-width: 1000px;
		background-color: #111;
	}
	
	.td-current-time {
		border: 3px solid #00E396 !important;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kcust/app/redstag-logo.png'); ?>" width="200"><br>
		SHIPMENT BOARD
	</h3>
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-shipment-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-date">Date</label>
						<input type="date" class="form-control" id="input-date" name="date" value="<?php echo $date; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div id="shipment-board-visualization-area"><?php echo $shipment_board_visualization_html; ?></div>

<?php endif; ?>