<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	#carrier-status-table td,
	#carrier-status-table th	{
		font-size:15px;
		vertical-align:middle;
		white-space: normal;
	}
	
	@media only screen and (max-width: 768px) {
		#carrier-status-table-wrapper {
			width: 100%;
			overflow-x: scroll;
		}
		
		#carrier-status-table {
			width: 1200px;
			overflow-x: scroll;
		}
	}
	
	.row-color-yellow {
		background-color: yellow;
		color: black;
	}
	
	.row-color-red {
		background-color: #C52428;
		color: white;
	}
	
	.row-color-green {
		background-color: green;
		color: white;
	}
	
	.row-color-blue {
		background-color: #0078D7;
		color: white;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		PACKAGE STATUS BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-carrier-status-dashboard-for-packages-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-customer">Customer</label>
						<select class="form-control selectized" id="input-customer" name="customer">
							<option value="">All Customers</option>
							<?php
								foreach($customer_list as $current_customer) {
									$selected = $customer == $current_customer['customer_id'] ? ' selected' : '';
									echo '<option value="'.$current_customer['customer_id'].'"'.$selected.'>'.$current_customer['customer_name'].'</option>';
								}
							?>
						</select>
					</div>
					
					<div class="form-group">
						<label for="input-shipping-method">Shipping Method</label>
						<select class="form-control multiple-selectized" id="input-shipping-method" name="shipping_method[]">
							<option value="">All Shipping Methods</option>
							<?php
								foreach($shipping_method_list as $shipping_method_name) {
									$selected = in_array($shipping_method_name, $shipping_method) ? ' selected' : '';
									echo '<option value="'.$shipping_method_name.'"'.$selected.'>'.$shipping_method_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-package-created-at-period-from">Package Created Date From</label>
						<input type="date" class="form-control" id="input-package-created-at-period-from" name="period_from" value="<?php echo $period_from; ?>">
					</div>

					<div class="form-group">
						<label for="input-package-created-at-period-to">Package Created Date To</label>
						<input type="date" class="form-control" id="input-package-created-at-period-to" name="period_to" value="<?php echo $period_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-carrier">Carrier</label>
						<select class="form-control multiple-selectized" id="input-carrier" name="carrier[]">
							<option value="">All Carriers</option>
							<?php
								foreach($carrier_list as $carrier_code => $carrier_name) {
									$selected = in_array($carrier_code, $carrier) ? ' selected' : '';
									echo '<option value="'.$carrier_code.'"'.$selected.'>'.$carrier_name.'</option>';
								}
							?>
						</select>
					</div>

					<div class="form-group">
						<label for="input-track-number">Track Number</label>
						<input type="text" class="form-control" id="input-track-number" name="track_number" value="<?php echo $track_number; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-is-delivered">Is Delivered?</label>
						<select class="form-control selectized" id="input-is-delivered" name="is_delivered">
							<option value="">All Status</option>
							<option value="yes" <?php echo $is_delivered == 'yes' ? 'selected' : null; ?>>Yes</option>
							<option value="no" <?php echo $is_delivered == 'no' ? 'selected' : null; ?>>No</option>
						</select>
					</div>
					
					<div class="form-group">
						<label for="input-is-late">Is Late?</label>
						<select class="form-control selectized" id="input-is-late" name="is_late">
							<option value="">All Status</option>
							<option value="yes" <?php echo $is_late == 'yes' ? 'selected' : null; ?>>Yes</option>
							<option value="no" <?php echo $is_late == 'no' ? 'selected' : null; ?>>No</option>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-max-transit-day">Max Transit Time (Day)</label>
						<input type="number" class="form-control" id="input-max-transit-day" name="max_transit_day" value="<?php echo $max_transit_day; ?>">
					</div>
					
					<div class="form-group">
						<label for="input-facility">Facility</label>
						<select class="form-control multiple-selectized" id="input-facility" name="stock_ids[]">
							<option value="">All Facilities</option>
							<?php
								foreach($facility_list as $current_facility) {
									$selected = in_array($current_facility['stock_id'], $stock_ids) ? ' selected' : '';
									echo '<option value="'.$current_facility['stock_id'].'"'.$selected.'>'.$current_facility['facility_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-report-type">Report Type</label>
						<select class="form-control selectized" id="input-report-type" name="report_type">
							<?php
								foreach($report_type_list as $report_type_code => $report_type_name) {
									$selected = ($report_type_code == $report_type) ? ' selected' : '';
									echo '<option value="'.$report_type_code.'"'.$selected.'>'.$report_type_name.'</option>';
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

<?php if($generate) : ?>

<div id="carrier-status-table-area">
	<?php echo $package_status_board_table_html; ?>
</div>

<?php endif; ?>