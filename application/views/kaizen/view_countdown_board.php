<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

<style type="text/css">
	.takt-board-title {
		text-align: center;
		font-size: 20px;
	}
	
	.takt-board-value {
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
	
	.takt-board-hourly-title {
		text-align: center;
		font-size: 20px;
	}
	
	.takt-board-hourly-value {
		text-align: center;
		font-size: 24px;
		font-weight: bold;
	}
	
	.takt-board-subvalue {
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
	
	.takt-board-chart-wrapper {
		width: 100%;
		overflow-x: scroll;
	}
	
	.takt-board-chart {
		min-width: 1000px;
		background-color: #111;
	}
	
	.radial-chart-section {
		text-align: center;
	}
	
	.base-timer {
		position: relative;
		width: 500px;
		height: 500px;
		margin: 0 auto;
	}

	.base-timer__svg {
		transform: scaleX(-1);
	}

	.base-timer__circle {
		fill: none;
		stroke: none;
	}

	.base-timer__path-elapsed {
		stroke-width: 7px;
		stroke: grey;
	}

	.base-timer__path-remaining {
		stroke-width: 7px;
		stroke-linecap: round;
		transform: rotate(90deg);
		transform-origin: center;
		transition: 1s linear all;
		fill-rule: nonzero;
		stroke: currentColor;
	}

	.base-timer__path-remaining.green {
		color: #70f869; //rgb(65, 184, 131);
	}

	.base-timer__path-remaining.orange {
		color: orange;
	}

	.base-timer__path-remaining.red {
		color: red;
	}

	.base-timer__label {
		position: absolute;
		width: 500px;
		height: 500px;
		top: 0;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		font-size: 48px;
	}
	
	#time-countdown-label {
		font-size: 76px;
	}
	
	#remaining-shipments-label {
		font-size: 120px;
	}
	
	#completed-shipments-label {
		font-size: 32px;
	}
	
	#estimated-finish-time-label {
		font-size: 32px;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		COUNTDOWN BOARD
	</h3>
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-countdown-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-facility">Facility</label>
						<select class="form-control selectized" id="input-facility" name="facility">
							<option value="">All Facilities</option>
							<?php
								foreach($facility_list as $item) {
									$selected = ($facility == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['facility_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-start-time">Start Time</label>
						<input type="time" class="form-control" id="input-start-time" name="start_time" value="<?php echo $start_time; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-end-time">End Time</label>
						<input type="time" class="form-control" id="input-end-time" name="end_time" value="<?php echo $end_time; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-cut-off-time">Cut Off Time</label>
						<input type="time" class="form-control" id="input-cut-off-time" name="cut_off_time" value="<?php echo $cut_off_time; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
				<!--<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<a href="#" class="form-control btn btn-default" id="btn-set-hide-break-times" break_times_state="hidden">Set Break Times</a>
					</div>
				</div>-->
			</div>
			<div class="row" id="break-times-row" style="display:none;">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-break-time-1-start">Break Time 1 Start</label>
						<input type="time" class="form-control" id="input-break-time-1-start" name="break_time_1_start" value="<?php echo $break_time_1_start; ?>">
					</div>
					<div class="form-group">
						<label for="input-break-time-1-end">Break Time 1 End</label>
						<input type="time" class="form-control" id="input-break-time-1-end" name="break_time_1_end" value="<?php echo $break_time_1_end; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-break-time-2-start">Break Time 2 Start</label>
						<input type="time" class="form-control" id="input-break-time-1-start" name="break_time_2_start" value="<?php echo $break_time_2_start; ?>">
					</div>
					<div class="form-group">
						<label for="input-break-time-2-end">Break Time 2 End</label>
						<input type="time" class="form-control" id="input-break-time-2-end" name="break_time_2_end" value="<?php echo $break_time_2_end; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-break-time-3-start">Break Time 3 Start</label>
						<input type="time" class="form-control" id="input-break-time-3-start" name="break_time_3_start" value="<?php echo $break_time_3_start; ?>">
					</div>
					<div class="form-group">
						<label for="input-break-time-3-end">Break Time 3 End</label>
						<input type="time" class="form-control" id="input-break-time-3-end" name="break_time_3_end" value="<?php echo $break_time_3_end; ?>">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div class="row">
	<div class="col-md-6">
		<div class="radial-chart-section" id="time-countdown-section">
			<div class="base-timer">
				<svg class="base-timer__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
					<g class="base-timer__circle">
						<circle class="base-timer__path-elapsed" cx="50" cy="50" r="45"></circle>
						<path
							id="time-countdown-base-timer-path-remaining"
							stroke-dasharray="283"
							class="base-timer__path-remaining <?php echo $color_code; ?>"
							d="
							M 50, 50
							m -45, 0
							a 45,45 0 1,0 90,0
							a 45,45 0 1,0 -90,0
							"
						></path>
					</g>
				</svg>
				<div id="time-countdown-base-timer-label" class="base-timer__label">
					<span id="time-countdown-label"><?php echo $estimated_remaining_secs_text; ?></span>
					<span style="font-size:16px;">Est. Finish Time</span>
					<span id="estimated-finish-time-label"><?php echo $estimated_finish_time_text; ?></span>
					<span style="font-size:16px; margin-top:10px;">Projected Demand</span>
					<span id="projected-demand-label" style="font-size:32px;"><?php echo $adjusted_projected_demand; ?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="radial-chart-section" id="remaining-shipments-section">
			<div class="base-timer">
				<svg class="base-timer__svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
					<g class="base-timer__circle">
						<circle class="base-timer__path-elapsed" cx="50" cy="50" r="45"></circle>
						<path
							id="remaining-shipments-base-timer-path-remaining"
							stroke-dasharray="283"
							class="base-timer__path-remaining <?php echo $color_code; ?>"
							d="
							M 50, 50
							m -45, 0
							a 45,45 0 1,0 90,0
							a 45,45 0 1,0 -90,0
							"
						></path>
					</g>
				</svg>
				<div id="remaining-shipments-base-timer-label" class="base-timer__label">
					<span style="font-size:16px; margin-bottom:-30px;">Remaining Shipments</span>
					<span id="remaining-shipments-label"><?php echo $remaining_shipments_count; ?></span>
					<span style="font-size:16px;">Current Pace Per Minute</span>
					<span id="current-pace-label" style="font-size:32px;"><?php echo $current_num_shipment_per_minute; ?></span>
					<span style="font-size:16px; margin-top:10px;">Required Pace Per Minute</span>
					<span id="required-pace-label" style="font-size:32px;"><?php echo $required_num_shipment_per_minute_text; ?></span>
				</div>
			</div>
		</div>
	</div>
</div>

<br><br>

<div id="countdown-board-shipments-graph"><?php echo $countdown_board_shipments_graph_html; ?></div>

<?php endif; ?>