<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.scoreboard-value {
		font-size: 18px;
		text-align: center;
	}
	
	.scoreboard-panel {
		float:left;
		margin-right:10px;
	}
	
	.scoreboard-title {
		text-align: center;
	}
	
	.scoreboard-table {
		width: 620px;
	}
	
	.scoreboard-table td {
		vertical-align: middle !important;
		font-size: 18px;
		padding-top: 4px !important;
		padding-bottom: 4px !important;
	}
	
	.scoreboard-table td img {
		max-width: 50px;
	}
	
	.scoreboard-username {
		font-size: 24px !important;
	}
	
	.scoreboard-employee-assignment {
		font-size: 18px !important;
	}
	
	.row-below {
		background-color: red;
	}
	
	.row-minimum {
		background-color: white;
		color: black;
	}
	
	.row-production-goal {
		background-color: green;
	}
	
	.row-great {
		background-color: blue;
	}
	
	.row-outstanding {
		background-color: purple;
	}
	
	.legend-table {
		width: 300px;
		float: right;
	}
	
	.legend-table td {
		padding-left: 10px;
	}
	
	#auto-refresh-button-area {
		position: fixed;
		top: 50px;
		right: 20px;
		width: 200px;
		padding: 10px 30px 30px 30px;
	}
	
	#edit-assignment-employee-name-area {
		font-size: 30px;
		font-weight: bold;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		OUTBOUND SCOREBOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-scoreboard-filter">
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
						<label for="input-block-time">Block Time</label>
						<select class="form-control multiple-selectized" id="input-block-time" name="block_time[]">
							<option value="">All Block Times</option>
							<?php
								foreach($block_time_list as $item) {
									$selected = in_array($item['id'], $block_time) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['block_time_name'].'</option>';
								}
							?>
						</select>
					</div>
					
					<div class="form-group">
						<label for="input-employee-shift-type">Shift</label>
						<select class="form-control multiple-selectized" id="input-employee-shift-type" name="employee_shift_type[]">
							<option value="">All Shifts</option>
							<?php
								foreach($employee_shift_type_list as $item) {
									$selected = in_array($item['id'], $employee_shift_type) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['employee_shift_type_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-action">Action</label>
						<select multiple class="form-control multiple-selectized" id="input-action" name="action[]">
							<option value="">All Actions</option>
							<?php
								foreach($action_list as $item) {
									$selected = in_array($item, $action) ? ' selected' : '';
									echo '<option value="'.$item.'"'.$selected.'>'.$item.'</option>';
								}
							?>
						</select>
					</div>
					
					<div class="form-group">
						<label for="input-assignment-type">Assignment Type</label>
						<select class="form-control multiple-selectized" id="input-assignment-type" name="assignment_type[]">
							<option value="">All Assignment Types</option>
							<?php
								foreach($assignment_type_list as $item) {
									$selected = in_array($item['id'], $assignment_type) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['assignment_type_name'].'</option>';
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
					
					<div class="form-group">
						<label for="input-type">Type</label>
						<select multiple class="form-control multiple-selectized" id="input-type" name="type[]">
							<option value="">All Types</option>
							<?php
								foreach($type_list as $item) {
									$selected = in_array($item, $type) ? ' selected' : '';
									echo '<option value="'.$item.'"'.$selected.'>'.$item.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-time-from">Time From</label>
						<input type="time" class="form-control" id="input-time-from" name="time_from" value="<?php echo $time_from; ?>">
					</div>
					<div class="form-group">
						<label for="input-time-to">Time To</label>
						<input type="time" class="form-control" id="input-time-to" name="time_to" value="<?php echo $time_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-sort-by">Sort By</label>
						<select class="form-control selectized" id="input-sort-by" name="sort_by">
							<?php
								foreach($sort_by_list as $item) {
									$selected = ($sort_by == $item) ? ' selected' : '';
									echo '<option value="'.$item.'"'.$selected.'>'.$item.'</option>';
								}
							?>
						</select>
					</div>
					
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="form-group" id="auto-refresh-button-area">
	<label>&nbsp;</label>
	<a class="form-control btn btn-success" id="btn-auto-refresh" data-state="on">Auto Refresh On</a>
</div>

<?php if($generate) : ?>
<div style="width:<?php echo (count($scoreboard) + 1) * 600; ?>px";>
	<div id="scoreboard-tables-area">
		<?php echo $scoreboard_tables_html; ?>
	</div>
	
	<div class="panel scoreboard-panel">
		<div class="panel-title">
			<div class="panel-heading">
				<h3 class="scoreboard-title">Legend</h3>
			</div>
		</div>
		<table class="table legend-table">
			<thead>
				<th style="width:120px;"></th>
				<th style="width:60px;">Picking</th>
				<th style="width:60px;">Packing</th>
			</thead>
			<tbody>
				<tr class="row-outstanding">
					<td>Outstanding</td>
					<td>>= 80</td>
					<td>>= 50</td>
				</tr>
				<tr class="row-great">
					<td>Great</td>
					<td>70-80</td>
					<td>40-50</td>
				</tr>
				<tr class="row-production-goal">
					<td>Production Goal</td>
					<td>60-70</td>
					<td>30-40</td>
				</tr>
				<tr class="row-minimum">
					<td>Minimum</td>
					<td>50-60</td>
					<td>25-30</td>
				</tr>
				<tr class="row-below">
					<td>Below</td>
					<td>< 50</td>
					<td>< 25</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="modal fade" id="modal-edit-assignment" tabindex="-1" role="dialog" aria-labelledby="modal-edit-assignment" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" id="modal-edit-assignment-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo ucwords(lang('word__close')); ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal-edit-assignment-title">Edit Assignment</h4>
			</div>
			<form id="form-edit-assignment">
			
			<div class="modal-body" id="modal-edit-assignment-body">
				<input type="hidden" id="input-edit-assignment-employee-name" name="employee_name" />
				
				<?php foreach($block_time_list as $block_time) : ?>
				<div class="form-group">
					<label for="input-edit-assignment-<?php echo $block_time['id']; ?>"><?php echo $block_time['block_time_name']; ?></label>
					<select class="form-control selectized input-edit-assignment" id="input-edit-assignment-<?php echo $block_time['id']; ?>" name="assignment_type_<?php echo $block_time['id']; ?>">
						<option value=""></option>
						<?php
							foreach($assignment_type_list as $item) {
								echo '<option value="'.$item['id'].'">'.$item['assignment_type_name'].'</option>';
							}
						?>
					</select>
				</div>
				<?php endforeach; ?>
				
			</div>
			<div class="modal-footer" id="modal-edit-assignment-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="btn-do-edit-assignment">Save</button>
			</div>
			
			</form>
		</div>
	</div>
</div>

<?php endif; ?>