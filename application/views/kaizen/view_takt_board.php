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
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		OUTBOUND TAKT BOARD
	</h3>
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-takt-board-filter">
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
						<label for="input-start-date">Start Date</label>
						<input type="date" class="form-control" id="input-date" name="start_date" value="<?php echo $start_date; ?>">
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
						<label for="input-custom-end-time">End Time</label>
						<input type="time" class="form-control" id="input-end-time" name="end_time" value="<?php echo $end_time; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
			</div>
			
			<div class="row">
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
						<input type="time" class="form-control" id="input-break-time-2-start" name="break_time_2_start" value="<?php echo $break_time_2_start; ?>">
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
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-break-time-4-start">Break Time 4 Start</label>
						<input type="time" class="form-control" id="input-break-time-4-start" name="break_time_4_start" value="<?php echo $break_time_4_start; ?>">
					</div>

					<div class="form-group">
						<label for="input-break-time-4-end">Break Time 4 End</label>
						<input type="time" class="form-control" id="input-break-time-4-end" name="break_time_4_end" value="<?php echo $break_time_4_end; ?>">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div id="cost-calculation-section"><?php echo $cost_calculation_section_html; ?></div>

<div id="block-times-section"><?php echo $block_times_section_html; ?></div>

<div id="completed-shipment-section"><?php echo $completed_shipment_section_html; ?></div>

<div id="graph-section"><?php echo $graph_section_html; ?></div>

<div class="modal fade" id="modal-edit-takt-data" tabindex="-1" role="dialog" aria-labelledby="modal-edit-takt-data" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" id="modal-edit-takt-data-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo ucwords(lang('word__close')); ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal-edit-takt-data-title">Edit Takt Data</h4>
			</div>
			<form id="form-edit-takt-data">
			
			<div class="modal-body" id="modal-edit-takt-data-body">
				<div class="form-group">
					<label class="control-label" for="input-edit-takt-data-projected_demand">Projected Demand</label>
					<input type="number" class="form-control input_number big-input input-edit-takt-data" id="input-edit-takt-data-projected_demand" value="" name="projected_demand" autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label" for="input-edit-takt-data-hours_shift">Hours Shift</label>
					<input type="number" class="form-control input_number big-input input-edit-takt-data" id="input-edit-takt-data-hours_shift" value="" name="hours_shift" autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label" for="input-edit-takt-data-break_time_per_shift_in_min">Break Time Per Shift (Mins)</label>
					<input type="number" class="form-control input_number big-input input-edit-takt-data" id="input-edit-takt-data-break_time_per_shift_in_min" value="" name="break_time_per_shift_in_min" autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label" for="input-edit-takt-data-lunch_time_per_shift_in_min">Lunch Time Per Shift (Mins)</label>
					<input type="number" class="form-control input_number big-input input-edit-takt-data" id="input-edit-takt-data-lunch_time_per_shift_in_min" value="" name="lunch_time_per_shift_in_min" autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label" for="input-edit-takt-data-number_of_employees_scheduled">Number of Employees Scheduled</label>
					<input type="number" class="form-control input_number big-input input-edit-takt-data" id="input-edit-takt-data-number_of_employees_scheduled" value="" name="number_of_employees_scheduled" autocomplete="off">
				</div>
			</div>
			<div class="modal-footer" id="modal-edit-takt-data-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="btn-do-edit-takt-data">Save</button>
			</div>
			
			</form>
		</div>
	</div>
</div>

<?php endif; ?>