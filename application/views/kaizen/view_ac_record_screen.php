<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

<style type="text/css">

</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		AC RECORD SCREEN
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-ac-record-screen-filter">
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
	</div>
</div>

<div class="row">
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-body">
				<h3>New AC</h3>
				<form id="form-ac-record">
					<div class="form-group">
						<label for="input-ac-date">Date</label>
						<input type="date" class="form-control" id="input-ac-date" name="date" value="">
					</div>
					<div class="form-group">
						<label for="input-ac-time">Time</label>
						<input type="time" class="form-control" id="input-ac-time" name="time" value="">
					</div>
					<div class="form-group">
						<label for="input-ac-customer-name">Customer Name</label>
						<select class="form-control selectized" id="input-ac-customer-name" name="customer_name">
							<option value=""></option>
							<?php
								foreach($customer_list as $item) {
									echo '<option value="'.$item['id'].'>'.$item['customer_name'].'</option>';
								}
							?>
						</select>
					</div>
					<div class="form-group">
						<label for="input-ac-order-no">Order No.</label>
						<input type="text" class="form-control" id="input-ac-order-no" name="order_no" value="">
					</div>
					<div class="form-group">
						<label for="input-ac-type">AC Type</label>
						<select class="form-control selectized" id="input-ac-type" name="abnormal_type">
							<option value=""></option>
							<?php
								foreach($abnormal_type_list as $item) {
									echo '<option value="'.$item['id'].'>'.$item['abnormal_type_name'].'</option>';
								}
							?>
						</select>
					</div>
					<div class="form-group">
						<label for="input-ac-is-closed">AC Closed?</label>
						<select class="form-control selectized" id="input-ac-is-closed" name="is_rc_closed">
							<option value=""></option>
							<option value="yes">Yes</option>
							<option value="no">No</option>
						</select>
					</div>
					<div class="form-group">
						<label for="input-ac-is-customer-charged">Customer Charged?</label>
						<select class="form-control selectized" id="input-ac-is-customer-charged" name="is_customer_charged">
							<option value=""></option>
							<option value="yes">Yes</option>
							<option value="no">No</option>
						</select>
					</div>
					<div class="form-group">
						<label for="input-ac-discovered-by">Discovered By</label>
						<select class="form-control selectized" id="input-ac-discovered-by" name="discovered_by">
							<option value=""></option>
							<?php
								foreach($employee_list as $item) {
									echo '<option value="'.$item['id'].'">'.$item['employee_name'].'</option>';
								}
							?>
						</select>
					</div>
					<div class="form-group">
						<label for="input-ac-discovered-by-points">Discovered By Points</label>
						<input type="text" class="form-control" id="input-ac-discovered-by-points" name="discovered_by_points" value="">
					</div>
					<div class="form-group">
						<label for="input-ac-caused-by">Caused By</label>
						<select class="form-control selectized" id="input-ac-caused-by" name="caused_by">
							<option value=""></option>
							<?php
								foreach($employee_list as $item) {
									echo '<option value="'.$item['id'].'">'.$item['employee_name'].'</option>';
								}
							?>
						</select>
					</div>
					<div class="form-group">
						<label for="input-ac-caused-by-points">Caused By Points</label>
						<input type="text" class="form-control" id="input-ac-caused-by-points" name="caused_by_points" value="">
					</div>
					<div class="form-group">
						<label for="input-ac-cost">AC Cost</label>
						<input type="text" class="form-control" id="input-ac-cost" name="ac_cost" value="">
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<?php if($generate) : ?>

<?php endif; ?>