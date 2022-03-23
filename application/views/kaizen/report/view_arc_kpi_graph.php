<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">

</style>

<div class="page-header">
	<h1>AC KPI's</h1>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form>
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-abnormal-type">Abnormal Type</label>
						<select class="form-control selectized" id="input-abnormal-type" name="abnormal_type">
							<option value=""></option>
							<?php
								foreach($abnormal_type_list as $item) {
									$selected = ($abnormal_type == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['abnormal_type_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-carrier">Carrier</label>
						<select class="form-control selectized" id="input-carrier" name="carrier">
							<option value=""></option>
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
							<option value=""></option>
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
							<option value=""></option>
							<?php
								foreach($department_list as $item) {
									$selected = ($department == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['department_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-facility">Facility</label>
						<select class="form-control selectized" id="input-facility" name="facility">
							<option value=""></option>
							<?php
								foreach($facility_list as $item) {
									$selected = ($facility == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['facility_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-period-from">Period From</label>
						<input type="date" class="form-control" id="input-period-from" name="period_from" value="<?php echo $period_from; ?>">
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="input-period-to">Period To</label>
						<input type="date" class="form-control" id="input-period-to" name="period_to" value="<?php echo $period_to; ?>">
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

<?php if($generate) : ?>

<div class="kpi-graph-area col-md-12" id="kpi-graph-area-customer">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Customer</h3>
		</div>
		<div class="panel-body">
			<div class="kpi-graph" id="kpi-graph-customer">
			<?php
				if(empty($graph['customer'])) {
					echo '<h3>No data found</h3>';
				}
			?>
			</div>
		</div>
	</div>
</div>

<div class="kpi-graph-area col-md-12" id="kpi-graph-area-reason">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Reason</h3>
		</div>
		<div class="panel-body">
			<div class="kpi-graph" id="kpi-graph-reason">
			<?php
				if(empty($graph['reason'])) {
					echo '<h3>No data found</h3>';
				}
			?>
			</div>
		</div>
	</div>
</div>

<div class="kpi-graph-area col-md-12" id="kpi-graph-area-department">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Department</h3>
		</div>
		<div class="panel-body">
			<div class="kpi-graph" id="kpi-graph-department">
			<?php
				if(empty($graph['department'])) {
					echo '<h3>No data found</h3>';
				}
			?>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>
