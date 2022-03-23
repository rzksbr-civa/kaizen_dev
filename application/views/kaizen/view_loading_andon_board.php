<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.panel .manifest-color-red {
		background-color: #C21807;
		color: white;
		transition: 1200ms ease;
	}
	
	.panel .manifest-color-yellow {
		background-color: yellow;
		color: black;
	}
	
	.panel .manifest-color-green {
		background-color: green;
		color: white;
	}
	
	.manifest-number {
		font-size: 18px;
		font-weight: bold;
	}
	
	.manifest-info, .manifest-weight {
		font-size: 14px;
	}
	
	.blow-out {
		font-size: 40px;
		font-weight: bold;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		LOADING ANDON BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-loading-andon-board-filter">
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
					
					<div class="form-group">
						<label for="input-carrier">Carrier</label>
						<select class="form-control selectized" id="input-carrier" name="carrier">
							<option value="">All Carriers</option>
							<?php
								foreach($carrier_list as $carrier_code => $carrier_name) {
									$selected = ($carrier == $carrier_code) ? ' selected' : '';
									echo '<option value="'.$carrier_code.'"'.$selected.'>'.$carrier_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-status">Status</label>
						<select class="form-control selectized" id="input-status" name="status">
							<option value="">All Status</option>
							<?php
								foreach($manifest_status_list as $status_code => $status_name) {
									$selected = ($status == $status_code) ? ' selected' : '';
									echo '<option value="'.$status_code.'"'.$selected.'>'.$status_name.'</option>';
								}
							?>
						</select>
					</div>
					
					<div class="form-group">
						<label for="input-container-type">Container Type</label>
						<select class="form-control selectized" id="input-container-type" name="container_type">
							<option value="">All Container Types</option>
							<?php
								foreach($container_type_list as $container_type_code => $container_type_name) {
									$selected = ($container_type == $container_type_code) ? ' selected' : '';
									echo '<option value="'.$container_type_code.'"'.$selected.'>'.$container_type_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-load-location">Load Location</label>
						<input type="text" class="form-control" id="input-load-location" name="load_location" value="<?php echo $load_location; ?>">
					</div>
					
					<div class="form-group">
						<label for="input-sort">Sort By</label>
						<select class="form-control selectized" id="input-sort" name="sort">
							<?php
								foreach($sort_list as $sort_code => $sort_name) {
									$selected = ($sort == $sort_code) ? ' selected' : '';
									echo '<option value="'.$sort_code.'"'.$selected.'>'.$sort_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period-from">Period From</label>
						<input type="date" class="form-control" id="input-period-from" name="period_from" value="<?php echo $period_from; ?>">
					</div>
					
					<div class="form-group">
						<label for="input-period-to">Period To</label>
						<input type="date" class="form-control" id="input-period-to" name="period_to" value="<?php echo $period_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-utilization">Utilization</label>
						<select class="form-control selectized" id="input-utilization" name="utilization">
							<option value="">All Utilizations</option>
							<?php
								foreach($utilization_list as $utilization_code => $utilization_name) {
									$selected = ($utilization == $utilization_code) ? ' selected' : '';
									echo '<option value="'.$utilization_code.'"'.$selected.'>'.$utilization_name.'</option>';
								}
							?>
						</select>
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

<div id="loading-andon-board-visualization-area">
	<?php echo $loading_andon_board_visualization_html; ?>
</div>

<?php endif; ?>