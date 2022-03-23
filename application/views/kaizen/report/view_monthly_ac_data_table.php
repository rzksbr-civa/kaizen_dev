<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.ac-data-col {
		width: 27px;
		text-align: center;
	}
	
	.ac-data-col-total {
		width: 40px;
		text-align: center;
	}
</style>

<div class="page-header">
	<h1>Monthly AC Data Table</h1>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-carrier">Carrier</label>
						<select class="form-control selectized" id="input-carrier" name="carrier">
							<option value="">All Carriers</option>
							<?php
								foreach($carrier_list as $item) {
									$selected = ($carrier == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['carrier_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-customer">Customer</label>
						<select class="form-control selectized" id="input-customer" name="customer">
							<option value="">All Customers</option>
							<?php
								foreach($customer_list as $item) {
									$selected = ($customer == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['customer_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
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
				<div class="col-md-3">
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
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-year">Year</label>
						<input type="number" class="form-control" id="input-year" name="year" value="<?php echo $year; ?>">
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Generate</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="table-area invisible">
<table class="table table-bordered" id="monthly-ac-data-table">
	<thead>
		<th>AC Type</th>
		<th class="ac-data-col">Jan</th>
		<th class="ac-data-col">Feb</th>
		<th class="ac-data-col">Mar</th>
		<th class="ac-data-col">Apr</th>
		<th class="ac-data-col">May</th>
		<th class="ac-data-col">Jun</th>
		<th class="ac-data-col">Jul</th>
		<th class="ac-data-col">Aug</th>
		<th class="ac-data-col">Sep</th>
		<th class="ac-data-col">Oct</th>
		<th class="ac-data-col">Nov</th>
		<th class="ac-data-col">Dec</th>
		<th class="ac-data-col-total">Total</th>
	</thead>
	<tbody>
		<?php foreach($ac_table_data as $data) : ?>
		
		<tr>
			<td><?php echo $data['abnormal_type_name']; ?></td>
			<?php
				for($i=1; $i<=12; $i++) {
					if($data['monthly_count'][$i] === null) {
						echo '<td></td>';
					}
					else {
						$additional_class = ' ';
						if($i > 1) {
							if($data['monthly_count'][$i] > $data['monthly_count'][$i-1]) {
								$additional_class .= ' text-danger';
							}
							else if($data['monthly_count'][$i] < $data['monthly_count'][$i-1]) {
								$additional_class .= ' text-success';
							}
						}
						echo '<td class="ac-data-col'.$additional_class.'">'.$data['monthly_count'][$i].'</td>';
					}
				}
				echo '<td class="ac-data-col-total">'.$data['monthly_count']['total'].'</td>';
			?>
		</tr>
		
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td>Total</td>
			<?php
				for($i=1; $i<=12; $i++) {
					echo '<td class="ac-data-col">'.$monthly_total[$i].'</td>';
				}
			?>
			<td class="ac-data-col-total"><?php echo $monthly_total['total']; ?></td>
		</tr>
	</tfoot>
</table>
</div>
