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
		REPLENISHMENT RELEASE BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-replenishment-release-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
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
				<!-- <div class="col-md-2">
					<div class="form-group">
						<label for="input-customer">Customer</label>
						<select class="form-control selectized" id="input-customer" name="customer">
							<option value="">All Customers</option>
							<?php
								foreach($store_list as $item) {
									$selected = ($customer == $item['store_id']) ? ' selected' : '';
									echo '<option value="'.$item['store_id'].'"'.$selected.'>'.$item['name'].'</option>';
								}
							?>
						</select>
					</div>
				</div> -->
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-sku-tier">SKU Tier</label>
						<select multiple class="form-control multiple-selectized" id="input-sku-tier" name="sku_tier[]">
							<option value="">All SKU Tiers</option>
							<?php
								foreach($sku_tier_list as $sku_tier_code => $sku_tier_name) {
									$selected = in_array($sku_tier_code, $sku_tier) ? ' selected' : '';
									echo '<option value="'.$sku_tier_code.'"'.$selected.'>'.$sku_tier_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period-from">Service Level %</label>
						<div class="input-group">
							<input type="number" step="0.01" class="form-control" id="input-service-level-percentage" name="service_level_percentage" value="<?php echo $service_level_percentage; ?>">
							<span class="input-group-addon">%</span>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period-from">Period From</label>
						<input type="date" class="form-control" id="input-period-from" name="period_from" value="<?php echo $period_from; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period-to">Period To</label>
						<input type="date" class="form-control" id="input-period-to" name="period_to" value="<?php echo $period_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
			</div>
			<h4>Replenish Freq. (Days)</h4>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-replenish-freq-tier-1">Tier 1</label>
						<select class="form-control selectized" id="input-replenish-freq-tier-1" name="replenish_freq_tier_1">
							<?php
								for($i=1; $i<=$default_replenish_freq_tier[1]; $i++) {
									$selected = ($i == $replenish_freq_tier_1) ? ' selected' : '';
									echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-replenish-freq-tier-2">Tier 2</label>
						<select class="form-control selectized" id="input-replenish-freq-tier-2" name="replenish_freq_tier_2">
							<?php
								for($i=1; $i<=$default_replenish_freq_tier[2]; $i++) {
									$selected = ($i == $replenish_freq_tier_2) ? ' selected' : '';
									echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-replenish-freq-tier-3">Tier 3</label>
						<select class="form-control selectized" id="input-replenish-freq-tier-3" name="replenish_freq_tier_3">
							<?php
								for($i=1; $i<=$default_replenish_freq_tier[3]; $i++) {
									$selected = ($i == $replenish_freq_tier_3) ? ' selected' : '';
									echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-replenish-freq-tier-4">Tier 4</label>
						<select class="form-control selectized" id="input-replenish-freq-tier-4" name="replenish_freq_tier_4">
							<?php
								for($i=1; $i<=$default_replenish_freq_tier[4]; $i++) {
									$selected = ($i == $replenish_freq_tier_4) ? ' selected' : '';
									echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-replenish-freq-tier-5">Tier 5</label>
						<select class="form-control selectized" id="input-replenish-freq-tier-5" name="replenish_freq_tier_5">
							<?php
								for($i=1; $i<=$default_replenish_freq_tier[5]; $i++) {
									$selected = ($i == $replenish_freq_tier_5) ? ' selected' : '';
									echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
								}
							?>
						</select>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div id="replenishment-release-board-visualization-area">
	<?php echo $replenishment_release_board_visualization_html; ?>
</div>

<?php endif; ?>