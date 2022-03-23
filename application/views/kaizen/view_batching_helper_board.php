<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		BATCHING HELPER BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-batching-helper-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
			<div class="row">
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
						<label for="input-carrier">Carrier</label>
						<select multiple class="form-control multiple-selectized" id="input-carrier" name="carrier[]">
							<option value="">All Carriers</option>
							<?php
								foreach($carrier_list as $carrier_code => $carrier_name) {
									$selected = in_array($carrier_code, $carrier) ? ' selected' : '';
									echo '<option value="'.$carrier_code.'"'.$selected.'>'.$carrier_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-package-created-at-period-from">Target Ship Date From</label>
						<input type="date" class="form-control" id="input-package-created-at-period-from" name="period_from" value="<?php echo $period_from; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-package-created-at-period-to">Target Ship Date To</label>
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

<div id="carrier-status-table-area">
	<?php echo $batching_helper_board_visualization_html; ?>
</div>

<?php endif; ?>