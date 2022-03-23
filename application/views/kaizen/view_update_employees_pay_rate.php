<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
	<div class="col-md-6">
		<?php if(!empty($employees_with_no_employee_number)) : ?>
		<h3>Employees with empty employee number in TSheets</h3>
			<table class="table table-bordered">
				<thead>
					<th>Employee Number on TSheets</th>
					<th>Employee Name on TSheets</th>
					<th>Employee Payroll ID on TSheets</th>
					<th>Employee Pay Rate on TSheets</th>
				</thead>
				<tbody>
					<?php foreach($employees_with_no_employee_number as $employee) : ?>
						<tr>
							<td><?php echo $employee['tsheets_employee_number']; ?></td>
							<td><?php echo $employee['tsheets_employee_name']; ?></td>
							<td><?php echo $employee['tsheets_employee_payroll_id']; ?></td>
							<td><?php echo $employee['tsheets_employee_pay_rate']; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		
		<?php if(!empty($employees_with_wrong_employee_number)) : ?>
		<h3>Employees with different name between Kaizen & TSheets</h3>
			<table class="table table-bordered">
				<thead>
					<th>Employee Number on TSheets</th>
					<th>Employee Name on TSheets</th>
					<th>Employee Payroll ID on TSheets</th>
					<th>Employee Pay Rate on TSheets</th>
					<th>Employee Number on Kaizen</th>
					<th>Employee Name on Kaizen</th>
					<th>Employee Pay Rate on Kaizen</th>
				</thead>
				<tbody>
					<?php foreach($employees_with_wrong_employee_number as $employee) : ?>
						<tr>
							<td><?php echo $employee['tsheets_employee_number']; ?></td>
							<td><?php echo $employee['tsheets_employee_name']; ?></td>
							<td><?php echo $employee['tsheets_employee_payroll_id']; ?></td>
							<td><?php echo $employee['tsheets_employee_pay_rate']; ?></td>
							<td><?php echo $employee['kaizen_employee_number']; ?></td>
							<td><?php echo $employee['kaizen_employee_name']; ?></td>
							<td><?php echo $employee['kaizen_employee_pay_rate']; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		
		<?php if(!empty($employees_with_updated_pay_rate)) : ?>
		<h3>Updated employees pay rate</h3>
			<table class="table table-bordered">
				<thead>
					<th>Employee Numbers</th>
					<th>Employee Name on TSheets</th>
					<th>Employee Name on Kaizen</th>
					<th>Previous Pay Rate</th>
					<th>Updated Pay Rate</th>
				</thead>
				<tbody>
					<?php foreach($employees_with_updated_pay_rate as $employee) : ?>
						<tr>
							<td><?php echo $employee['kaizen_employee_number']; ?></td>
							<td><?php echo $employee['tsheets_employee_name']; ?></td>
							<td><?php echo $employee['kaizen_employee_name']; ?></td>
							<td><?php echo $employee['kaizen_employee_pay_rate']; ?></td>
							<td><?php echo $employee['tsheets_employee_pay_rate']; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		
		<?php if($success) : ?>
		<div class="alert alert-success" role="alert">
			<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
			&nbsp;&nbsp;Employees pay rate data have been updated.
		</div>
		<?php endif; ?>
	</div>
</div>