<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

<style type="text/css">
	
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		KAIZEN MANAGER
	</h3>
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-heading" role="tab" id="panel-settings">
		<h4 class="panel-title">
			Settings
			<div class="pull-right">
				<a role="button" data-toggle="collapse" data-parent="#accordion" href="#panel-settings-collapse" aria-expanded="true" aria-controls="panel-settings-collapse">
					<span class="glyphicon glyphicon-chevron-up" aria-hidden="true" id="panel-settings-collapse-btn" data-state="up"></span>
				</a>
			</div>
		</h4>
	</div>
	<div id="panel-settings-collapse" class="panel-collapse in" role="tabpanel" aria-labelledby="panel-settings">
		<div class="panel-body">
			<form id="form-kaizen-manager-filter">
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
							<label for="input-pace-period">Pace Period (Mins)</label>
							<input type="number" class="form-control" id="input-pace-period" name="pace_period" value="<?php echo $pace_period; ?>">
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
							<input type="time" class="form-control" id="input-break-time-1-start" name="break_time_1_start" value="<?php echo $break_times[1]['start']; ?>">
						</div>
						
						<div class="form-group">
							<label for="input-break-time-1-end">Break Time 1 End</label>
							<input type="time" class="form-control" id="input-break-time-1-end" name="break_time_1_end" value="<?php echo $break_times[1]['end']; ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label for="input-break-time-2-start">Break Time 2 Start</label>
							<input type="time" class="form-control" id="input-break-time-2-start" name="break_time_2_start" value="<?php echo $break_times[2]['start']; ?>">
						</div>

						<div class="form-group">
							<label for="input-break-time-2-end">Break Time 2 End</label>
							<input type="time" class="form-control" id="input-break-time-2-end" name="break_time_2_end" value="<?php echo $break_times[2]['end']; ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label for="input-break-time-3-start">Break Time 3 Start</label>
							<input type="time" class="form-control" id="input-break-time-3-start" name="break_time_3_start" value="<?php echo $break_times[3]['start']; ?>">
						</div>

						<div class="form-group">
							<label for="input-break-time-3-end">Break Time 3 End</label>
							<input type="time" class="form-control" id="input-break-time-3-end" name="break_time_3_end" value="<?php echo $break_times[3]['end']; ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label for="input-break-time-4-start">Break Time 4 Start</label>
							<input type="time" class="form-control" id="input-break-time-4-start" name="break_time_4_start" value="<?php echo $break_times[4]['start']; ?>">
						</div>

						<div class="form-group">
							<label for="input-break-time-4-end">Break Time 4 End</label>
							<input type="time" class="form-control" id="input-break-time-4-end" name="break_time_4_end" value="<?php echo $break_times[4]['end']; ?>">
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<?php if($generate) : ?>
	<div id="kaizen-manager-visualization">
		<?php echo $kaizen_manager_visualization_html; ?>
	</div>
<?php endif; ?>