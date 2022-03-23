<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.hidden-filter {
		display: none;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		CARRIER OPTIMIZATION BOARD
	</h3>
</div>

<form id="form-carrier-optimization-board-filter">

<div class="row">
	<div class="col-md-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">Assumptions</h3>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="input-fedex-earned-discount">FedEx Earned Discount</label>
							<select class="form-control selectized" id="input-fedex-earned-discount" name="fedex_earned_discount">
								<?php
									foreach($fedex_earned_discount_list as $item) {
										$selected = ($fedex_earned_discount == $item) ? ' selected' : '';
										echo '<option value="'.$item.'"'.$selected.'>'.$item.'</option>';
									}
								?>
							</select>
						</div>
						
						<div class="form-group">
							<label for="input-rsf-fedex-dim-factor">FedEx RSF Dim Factor</label>
							<input type="text" class="form-control" id="input-rsf-fedex-dim-factor" name="rsf_fedex_dim_factor" value="<?php echo $rsf_fedex_dim_factor; ?>">
						</div>
						
						<div class="form-group">
							<label for="input-fedex-client-dim-factor">FedEx Client Dim Factor</label>
							<input type="text" class="form-control" id="input-fedex-client-dim-factor" name="fedex_client_dim_factor" value="<?php echo $fedex_client_dim_factor; ?>">
						</div>
						
						<div class="form-group">
							<label for="input-fedex-reduction-to-minimum">FedEx Reduction To Minimum</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-reduction-to-minimum" name="fedex_reduction_to_minimum" value="<?php echo $fedex_reduction_to_minimum; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-fedex-client-discount-tier">FedEx Client Discount Tier</label>
							<select class="form-control selectized" id="input-fedex-client-discount-tier" name="fedex_client_discount_tier">
								<?php
									foreach($fedex_client_discount_tier_list as $tier_code => $tier_name) {
										$selected = ($fedex_client_discount_tier == $tier_code) ? ' selected' : '';
										echo '<option value="'.$tier_code.'"'.$selected.'>'.$tier_name.'</option>';
									}
								?>
							</select>
						</div>
						
						<hr>
						<h4>FedEx Residential Delivery Fee</h4>
						
						<div class="form-group">
							<label for="input-fedex-residential-delivery-published-fee">Published Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-residential-delivery-published-fee" name="fedex_residential_delivery_published_fee" value="<?php echo $fedex_residential_delivery_published_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-fedex-residential-delivery-rsf-fee">RSF Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-residential-delivery-rsf-fee" name="fedex_residential_delivery_rsf_fee" value="<?php echo $fedex_residential_delivery_rsf_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-fedex-residential-delivery-client-fee">Client Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-residential-delivery-client-fee" name="fedex_residential_delivery_client_fee" value="<?php echo $fedex_residential_delivery_client_fee; ?>">
							</div>
						</div>
						
						<hr>
						<h4>FedEx AHS Weight Surcharge</h4>
						
						<div class="form-group">
							<label for="input-fedex-ahs-weight-surcharge-published-fee">Published Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-ahs-weight-surcharge-published-fee" name="fedex_ahs_weight_surcharge_published_fee" value="<?php echo $fedex_ahs_weight_surcharge_published_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-fedex-ahs-weight-surcharge-rsf-fee">RSF Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-ahs-weight-surcharge-rsf-fee" name="fedex_ahs_weight_surcharge_rsf_fee" value="<?php echo $fedex_ahs_weight_surcharge_rsf_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-fedex-ahs-weight-surcharge-client-fee">Client Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-ahs-weight-surcharge-client-fee" name="fedex_ahs_weight_surcharge_client_fee" value="<?php echo $fedex_ahs_weight_surcharge_client_fee; ?>">
							</div>
						</div>
						
						<hr>
						<h4>FedEx AHS Dimension Surcharge</h4>
						
						<div class="form-group">
							<label for="input-fedex-ahs-dimension-surcharge-published-fee">Published Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-ahs-dimension-surcharge-published-fee" name="fedex_ahs_dimension_surcharge_published_fee" value="<?php echo $fedex_ahs_dimension_surcharge_published_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-fedex-ahs-dimension-surcharge-rsf-fee">RSF Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-ahs-dimension-surcharge-rsf-fee" name="fedex_ahs_dimension_surcharge_rsf_fee" value="<?php echo $fedex_ahs_dimension_surcharge_rsf_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-fedex-ahs-dimension-surcharge-client-fee">Client Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-fedex-ahs-dimension-surcharge-client-fee" name="fedex_ahs_dimension_surcharge_client_fee" value="<?php echo $fedex_ahs_dimension_surcharge_client_fee; ?>">
							</div>
						</div>
					</div>
					
					<div class="col-md-6">
						<div class="form-group">
							<label for="input-ups-earned-discount">UPS Earned Discount</label>
							<select class="form-control selectized" id="input-ups-earned-discount" name="ups_earned_discount">
								<?php
									foreach($ups_earned_discount_list as $item) {
										$selected = ($ups_earned_discount == $item) ? ' selected' : '';
										echo '<option value="'.$item.'"'.$selected.'>'.$item.'</option>';
									}
								?>
							</select>
						</div>
						
						<div class="form-group">
							<label for="input-rsf-ups-dim-factor">UPS RSF Dim Factor</label>
							<input type="text" class="form-control" id="input-rsf-ups-dim-factor" name="rsf_ups_dim_factor" value="<?php echo $rsf_ups_dim_factor; ?>">
						</div>
						
						<div class="form-group">
							<label for="input-ups-client-dim-factor">UPS Client Dim Factor</label>
							<input type="text" class="form-control" id="input-ups-client-dim-factor" name="ups_client_dim_factor" value="<?php echo $ups_client_dim_factor; ?>">
						</div>
						
						<div class="form-group">
							<label for="input-ups-reduction-to-minimum">UPS Reduction To Minimum</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-reduction-to-minimum" name="ups_reduction_to_minimum" value="<?php echo $ups_reduction_to_minimum; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-ups-client-discount-tier">UPS Client Discount Tier</label>
							<select class="form-control selectized" id="input-ups-client-discount-tier" name="ups_client_discount_tier">
								<?php
									foreach($ups_client_discount_tier_list as $tier_code => $tier_name) {
										$selected = ($ups_client_discount_tier == $tier_code) ? ' selected' : '';
										echo '<option value="'.$tier_code.'"'.$selected.'>'.$tier_name.'</option>';
									}
								?>
							</select>
						</div>
						
						<hr>
						<h4>UPS Residential Delivery Fee</h4>
						
						<div class="form-group">
							<label for="input-ups-residential-delivery-published-fee">Published Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-residential-delivery-published-fee" name="ups_residential_delivery_published_fee" value="<?php echo $ups_residential_delivery_published_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-ups-residential-delivery-rsf-fee">RSF Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-residential-delivery-rsf-fee" name="ups_residential_delivery_rsf_fee" value="<?php echo $ups_residential_delivery_rsf_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-ups-residential-delivery-client-fee">Client Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-residential-delivery-client-fee" name="ups_residential_delivery_client_fee" value="<?php echo $ups_residential_delivery_client_fee; ?>">
							</div>
						</div>
						
						<hr>
						<h4>UPS AHS Weight Surcharge</h4>
						
						<div class="form-group">
							<label for="input-ups-ahs-weight-surcharge-published-fee">Published Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-ahs-weight-surcharge-published-fee" name="ups_ahs_weight_surcharge_published_fee" value="<?php echo $ups_ahs_weight_surcharge_published_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-ups-ahs-weight-surcharge-rsf-fee">RSF Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-ahs-weight-surcharge-rsf-fee" name="ups_ahs_weight_surcharge_rsf_fee" value="<?php echo $ups_ahs_weight_surcharge_rsf_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-ups-ahs-weight-surcharge-client-fee">Client Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-ahs-weight-surcharge-client-fee" name="ups_ahs_weight_surcharge_client_fee" value="<?php echo $ups_ahs_weight_surcharge_client_fee; ?>">
							</div>
						</div>
						
						<hr>
						<h4>UPS AHS Dimension Surcharge</h4>
						
						<div class="form-group">
							<label for="input-ups-ahs-dimension-surcharge-published-fee">Published Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-ahs-dimension-surcharge-published-fee" name="ups_ahs_dimension_surcharge_published_fee" value="<?php echo $ups_ahs_dimension_surcharge_published_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-ups-ahs-dimension-surcharge-rsf-fee">RSF Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-ahs-dimension-surcharge-rsf-fee" name="ups_ahs_dimension_surcharge_rsf_fee" value="<?php echo $ups_ahs_dimension_surcharge_rsf_fee; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="input-ups-ahs-dimension-surcharge-client-fee">Client Fee</label>
							<div class="input-group">
								<div class="input-group-addon">$</div>
								<input type="text" class="form-control" id="input-ups-ahs-dimension-surcharge-client-fee" name="ups_ahs_dimension_surcharge_client_fee" value="<?php echo $ups_ahs_dimension_surcharge_client_fee; ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Filters</h3>
			</div>
			<div class="panel-body">
				<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
				<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label for="input-report-type">Report Type</label>
							<select class="form-control selectized" id="input-report-type" name="report_type">
								<?php
									foreach($report_type_list as $report_type_code => $report_type_name) {
										$selected = ($report_type == $report_type_code) ? ' selected' : '';
										echo '<option value="'.$report_type_code.'"'.$selected.'>'.$report_type_name.'</option>';
									}
								?>
							</select>
						</div>

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
							<label for="input-merchant">Merchant</label>
							<select class="form-control selectized" id="input-merchant" name="merchant">
								<option value="">All Merchants</option>
								<?php
									foreach($merchant_list as $item) {
										$selected = ($merchant == $item['id']) ? ' selected' : '';
										echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['merchant_name'].'</option>';
									}
								?>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="input-period-from">Period From</label>
							<input type="date" class="form-control" id="input-period-from" name="period_from" value="<?php echo $period_from; ?>">
						</div>

						<div class="form-group">
							<label for="input-period-to">Period To</label>
							<input type="date" class="form-control" id="input-period-to" name="period_to" value="<?php echo $period_to; ?>">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>&nbsp;</label>
							<button type="submit" class="form-control btn btn-primary">Show</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

</form>

<?php echo isset($carrier_optimization_board_table_html) ? $carrier_optimization_board_table_html : null; ?>