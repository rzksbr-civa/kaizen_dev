<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.metrics-board-value {
		font-size: 14px;
		text-align: center;
	}
	
	.metrics-board-panel {
		float:left;
		margin-right:10px;
	}
	
	.metrics-board-title {
		text-align: center;
	}
	
	.metrics-board-table {
		border-radius: 25px;
		width: 2300px;
	}
	
	.total-cell {
		background-color: #888;
	}
	
	.metrics-board-table th {
		font-size: 12px;
	}
	
	.metrics-board-table td {
		vertical-align: middle !important;
		font-size: 14px;
		padding-top: 4px !important;
		padding-bottom: 4px !important;
	}
	
	.metrics-board-table td img {
		max-width: 50px;
	}
	
	.metrics-board-username {
		font-size: 14px !important;
	}
	
	.metrics-board-employee-assignment {
		font-size: 14px !important;
		cursor: pointer;
	}
	
	.evolution-points-area .coin {
		padding: 1px 12px;
		border-radius: 100%;
	}
	
	.evolution-bronze-area {
		color: #5a3825;
	}
	
	.bronze-frame {
		border: 3px solid #5a3825;
	}
	
	.bronze-bg {
		background-color: #5a3825;
		color: white;
	}
	
	.evolution-silver-area {
		color: #c0c0c0;
	}
	
	.silver-frame {
		border: 3px solid #c0c0c0;
	}
	
	.silver-bg {
		background-color: #c0c0c0;
		color: black;
	}
	
	.evolution-gold-area {
		color: #ffd700;
	}
	
	.gold-frame {
		border: 3px solid #ffd700;
	}
	
	.gold-bg {
		background-color: #ffd700;
		color: black;
	}

	.evolution-purple-area {
		color: #9933ff;
	}
	
	.purple-frame {
		border: 3px solid #9933ff;
	}
	
	.purple-bg {
		background-color: #9933ff;
		color: white;
	}
	
	.evolution-normal-area {
		color: #3bb143;
	}
	
	.normal-frame {
		border: 3px solid #3bb143;
	}
	
	.normal-bg {
		background-color: #3bb143;
		color: white;
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
	}
	
	.legend-table td, .legend-table th {
		padding: 2px 10px !important;
		font-size: 11px;
	}
	
	.metrics-board-note {
		color: red;
		background-color: yellow;
		font-size: 18px;
		font-weight: bold;
		padding: 20px;
		position: fixed;
		bottom: 10px;
		right: 10px;
		width: 500px;
		text-align: center;
		cursor: pointer;
	}
</style>

<div class="page-header">
	<?php if($generate && !empty($evolution_goals)) : ?>
	
		<?php if($evolution_goals <> 10000000) : ?>
		<div class="pull-left">
			<table class="table legend-table">
				<thead>
					<tr>
						<th>Evolution Status</th>
						<th>Evolution Points</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="normal-bg">Green</td>
						<td class="normal-bg"><?php echo '< ' . $evolution_goals; ?></td>
					</tr>
					<tr>
						<td class="bronze-bg">Bronze</td>
						<td class="bronze-bg"><?php echo $evolution_goals . ' - ' . ($evolution_goals * 2); ?></td>
					</tr>
					<tr>
						<td class="silver-bg">Silver</td>
						<td class="silver-bg"><?php echo ($evolution_goals * 2) . ' - ' . ($evolution_goals * 3); ?></td>
					</tr>
					<tr>
						<td class="gold-bg">Gold</td>
						<td class="gold-bg"><?php echo ($evolution_goals * 3) . ' - ' . ($evolution_goals * 4); ?></td>
					</tr>
					<tr>
						<td class="purple-bg">Purple</td>
						<td class="purple-bg"><?php echo '>= ' . ($evolution_goals * 4); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="pull-right">
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
	
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		OUTBOUND METRICS BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-metrics-board-filter">
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
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div id="metrics-board-table-area">
	<?php echo $evolution_points_leaderboard_html; ?>
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

<div class="modal fade" id="modal-set-metrics-board-note" tabindex="-1" role="dialog" aria-labelledby="modal-set-metrics-board-note" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" id="modal-set-metrics-board-note-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo ucwords(lang('word__close')); ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal-set-metrics-board-note-title">Set Metrics Board Note</h4>
			</div>
			<form id="form-set-metrics-board-note">
			
			<div class="modal-body" id="modal-set-metrics-board-note-body">
				<div class="form-group">
					<label for="input-metrics-board-note-content">Note Content</label>
					<textarea class="form-control" id="input-metrics-board-note-content" name="note_content"></textarea>
				</div>	
			</div>
			<div class="modal-footer" id="modal-set-metrics-board-note-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="btn-do-set-metrics-board-note">Save</button>
			</div>
			
			</form>
		</div>
	</div>
</div>

<?php endif; ?>