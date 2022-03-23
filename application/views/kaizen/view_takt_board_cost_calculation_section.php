<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<?php if(!empty($facility)) : ?>

<div class="row">
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">
					Takt Time Calculation
					<span class="pull-right">
						<button type="button" class="btn btn-default" aria-label="Edit Takt Data" style="padding:3px 10px;" id="btn-edit-takt-data">
							<span class="glyphicon glyphicon-edit" aria-hidden="true" style="font-size:12px"></span>
						</button>
					</span>
				</div>
			</div>
			<table class="table takt-board-info-table">
				<tr>
					<td class="info-table-label">Projected Demand</td>
					<td class="info-table-value"><?php echo $projected_demand; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Hours Shift</td>
					<td class="info-table-value"><?php echo isset($hours_shift) ? $hours_shift : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Available Time Per Shift (Mins)</td>
					<td class="info-table-value"><?php echo isset($available_time_per_shift_in_min) ? $available_time_per_shift_in_min : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Break Time Per Shift (Mins)</td>
					<td class="info-table-value"><?php echo isset($break_time_per_shift_in_min) ? $break_time_per_shift_in_min : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Lunch Time Per Shift (Mins)</td>
					<td class="info-table-value"><?php echo isset($lunch_time_per_shift_in_min) ? $lunch_time_per_shift_in_min : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Net Available Time Per Day (Mins)</td>
					<td class="info-table-value"><?php echo isset($net_available_time_per_day_in_min) ? $net_available_time_per_day_in_min : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Net Available Time Per Day (Secs)</td>
					<td class="info-table-value"><?php echo isset($net_available_time_per_day_in_sec) ? $net_available_time_per_day_in_sec : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Takt Time (Mins)</td>
					<td class="info-table-value"><?php echo isset($takt_time_in_min) ? number_format($takt_time_in_min, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Takt Time (Secs)</td>
					<td class="info-table-value"><?php echo isset($takt_time_in_sec) ? number_format($takt_time_in_sec, 2) : 'No Data'; ?></td>
				</tr>
			</table>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Headcount Requirement</div>
			</div>
			<table class="table takt-board-info-table">
				<tr>
					<td class="info-table-label">Takt Time Per Package (Mins)</td>
					<td class="info-table-value"><?php echo isset($takt_time_per_package_in_min) ? number_format($takt_time_per_package_in_min, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Picking Cycle Time (Mins)</td>
					<td class="info-table-value"><?php echo isset($picking_cycle_time_in_min) ? $picking_cycle_time_in_min : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Packing Cycle Time (Mins)</td>
					<td class="info-table-value"><?php echo isset($packing_cycle_time_in_min) ? $packing_cycle_time_in_min : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Loading Cycle Time (Mins)</td>
					<td class="info-table-value"><?php echo isset($loading_cycle_time_in_min) ? $loading_cycle_time_in_min : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Picking Headcount Required</td>
					<td class="info-table-value"><?php echo isset($picking_headcount_required) ? number_format($picking_headcount_required, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Packing Headcount Required</td>
					<td class="info-table-value"><?php echo isset($packing_headcount_required) ? number_format($packing_headcount_required, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Loading Headcount Required</td>
					<td class="info-table-value"><?php echo isset($loading_headcount_required) ? number_format($loading_headcount_required, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td>Total Value Add Employees</td>
					<td class="info-table-value"><?php echo isset($total_value_add_employees) ? number_format($total_value_add_employees, 2) : 'No Data'; ?></td>
				</tr>
			</table>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Cost Calculation</div>
			</div>
			<table class="table takt-board-info-table">
				<tr>
					<td class="info-table-label"># Of Employees Scheduled</td>
					<td class="info-table-value"><?php echo isset($num_employees_scheduled) ? $num_employees_scheduled : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label"># Of Employees Needed</td>
					<td class="info-table-value"><?php echo isset($num_employees_needed) ? $num_employees_needed : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Projected Hours Worked</td>
					<td class="info-table-value"><?php echo isset($projected_hours_worked) ? $projected_hours_worked : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Projected Order Volume</td>
					<td class="info-table-value"><?php echo isset($projected_order_volume) ? $projected_order_volume : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Operational Cost Per Package</td>
					<td class="info-table-value"><?php echo isset($operational_cost_per_package) ? '$'.number_format($operational_cost_per_package, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">FTE Cost Per Hour</td>
					<td class="info-table-value"><?php echo isset($fte_cost_per_hour) ? '$'.number_format($fte_cost_per_hour, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Labor Hours Per Package</td>
					<td class="info-table-value"><?php echo isset($hours_per_package) ? number_format($hours_per_package, 2) : 'No Data'; ?></td>
				</tr>
				<tr>
					<td class="info-table-label">Cost Per Package</td>
					<td class="info-table-value"><?php echo isset($cost_per_package) ? '$'.number_format($cost_per_package, 2) : 'No Data'; ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>

<?php endif; ?>

<?php if(empty($takt_value)) : ?>
<div class="alert alert-warning alert-dismissable" role="alert"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>&nbsp;&nbsp;
Please select a facility and complete all the necessary settings in facility table to show takt.</div>
<?php endif; ?>

<?php endif; ?>