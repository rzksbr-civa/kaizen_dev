<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	#staff-time-log-table {
		width: 700px;
	}
	
	.orange-bg {
		background-color: orange;
		color: black;
	}
	
	.green-bg {
		background-color: green;
	}
	
	.yellow-bg {
		background-color: yellow;
		color: black;
	}
	
	.red-bg {
		background-color: red;
	}
	
	#staff-time-log-table {
		font-weight: bold;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		OUTBOUND TEAM HELPER BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-team-helper-board-filter">
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
						<label for="input-department">Department</label>
						<select class="form-control selectized" id="input-department" name="department">
							<option value="">All Departments</option>
							<?php
								foreach($department_list as $item) {
									$selected = ($department == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['department_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
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
				<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="button" class="form-control btn btn-primary" id="btn-export">Export</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div id="staff-time-log-table-area">
	<?php echo $team_helper_staff_time_log_html; ?>
</div>

<?php endif; ?>