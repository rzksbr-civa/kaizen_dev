<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	#assignment-table th  {
		text-align: center;
	}
	
	.employee-name {
		font-size: 20px;
	}
	
	.asg {
		text-align: center;
		cursor: pointer;
	}
	
	.tooltip-green > .tooltip-inner {background-color: green;}
	.tooltip-green.top .tooltip-arrow{
		border-top:5px solid green;
	}
	
	.tooltip-red > .tooltip-inner {background-color: red;}
	.tooltip-red.top .tooltip-arrow{
		border-top:5px solid red;
	}
	
	.green-cell {
		background-color: green;
	}
	
	.red-cell {
		background-color: red;
	}
	
	#bulk-assign-setting-box {
		position: fixed;
		top: 50px;
		right: 20px;
		width: 300px;
		padding: 20px 50px;
		background-color: #003366;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		TEAM ASSIGNMENTS
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form>
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
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
						<label for="input-employee-shift">Shift</label>
						<select multiple class="form-control multiple-selectized" id="input-employee-shift" name="employee_shift[]">
							<option value="">All Shifts</option>
							<?php
								foreach($employee_shift_type_list as $item) {
									$selected = in_array($item['id'], $employee_shift) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['employee_shift_type_name'].'</option>';
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
			</div>
		</form>
		
		<div class="row">
			<div class="col-md-2">
				<div class="form-group" id="bulk-assign-setting-box">
					<label for="input-bulk-assign-assignment-type">Assignment Type</label>
					<select class="form-control selectized" id="input-bulk-assign-assignment-type">
						<option value="0" selected>(Not Selected)</option>
						<option value="-1">(Unassign)</option>
						<?php
							foreach($assignment_type_list as $item) {
								echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['assignment_type_name'].'</option>';
							}
						?>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if($generate) : ?>

<table class="table table-bordered" id="assignment-table">
	<thead>
		<th style="width:5%"></th>
		<th>Employee Name</th>
		<th>Block 1<br>08:00-10:15</th>
		<th>Block 2<br>10:30-12:45</th>
		<th>Block 3<br>13:30-16:00</th>
		<th>Block 4<br>16:15-Finish</th>
	</thead>
	<?php foreach($employee_assignments as $assignment) : ?>
		
		<tr>
			<td><img src="<?php echo file_exists(str_replace('application','assets',APPPATH).'data/kaizen/app/users/thumbnails/'.$assignment['employee_name'].'.jpg') ? base_url('assets/data/kaizen/app/users/thumbnails/'.$assignment['employee_name'].'.jpg') : base_url('assets/data/kaizen/app/users/thumbnails/no-photo.jpg'); ?>" width="40"></td>
			<td class="employee-name" id="employee-<?php echo $assignment['employee_id']; ?>"><?php echo $assignment['employee_name']; ?></td>

			<?php for($shift=1; $shift <= 4; $shift++) : ?>
			
			<td class="asg" id="<?php echo 'asg-'.$assignment['employee_id'].'-'.$shift; ?>" data-employee="<?php echo $assignment['employee_id']; ?>" data-employee_name="<?php echo $assignment['employee_name']; ?>" data-shift="<?php echo $shift; ?>"><?php echo implode(', ', array_column($assignment['assignments'][$shift], 'name')); ?>
			</td>
			
			<?php endfor; ?>
		</tr>
	
	<?php endforeach ?>

</table>

<?php endif; ?>