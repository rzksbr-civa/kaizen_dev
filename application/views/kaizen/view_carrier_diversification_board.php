<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		CARRIER DIVERSIFICATION BOARD
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
						<label for="input-stock-ids">Facility</label>
						<select class="form-control selectized" id="input-stock-ids" name="stock_ids">
							<option value="">All Facilities</option>
							<?php
								foreach($facility_list as $current_facility) {
									$selected = ($current_facility['stock_id'] == $stock_ids) ? ' selected' : '';
									echo '<option value="'.$current_facility['stock_id'].'"'.$selected.'>'.$current_facility['facility_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-customer">Customer</label>
						<select multiple class="form-control multiple-selectized" id="input-customer" name="customer[]">
							<option value="">All Customers</option>
							<?php
								foreach($customer_list as $current_customer) {
									$selected = in_array($current_customer['customer_id'], $customer) ? ' selected' : '';
									echo '<option value="'.$current_customer['customer_id'].'"'.$selected.'>'.$current_customer['customer_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-account">Account</label>
						<select class="form-control selectized" id="input-account" name="account">
							<option value="">All Accounts</option>
							<option value="all">All Accounts</option>
							<option value="rsf">RSF Main Accounts</option>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-periodicity">Periodicity</label>
						<select class="form-control selectized" id="input-periodicity" name="periodicity">
							<option value="">No Breakdown</option>
							<?php
								foreach($periodicity_list as $current_data) {
									$selected = $periodicity == $current_data ? ' selected' : '';
									echo '<option value="'.$current_data.'"'.$selected.'>'.ucwords($current_data).'</option>';
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
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div id="carrier-diversification-board-visualization">
	<?php echo $carrier_diversification_board_visualization_html; ?>
</div>

<?php endif; ?>