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
		CARTON UTILIZATION BOARD
	</h3>
</div>

<form id="form-carton-utilization-board-filter">

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-body">
				<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
				<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label for="input-facility">Facility</label>
							<select multiple class="form-control multiple-selectized" id="input-facility" name="stock_id[]">
								<option value="">All Facilities</option>
								<?php
									foreach($facility_list as $item) {
										$selected = in_array($item['stock_id'], $stock_id) ? ' selected' : '';
										echo '<option value="'.$item['stock_id'].'"'.$selected.'>'.$item['facility_name'].'</option>';
									}
								?>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label for="input-client">Client</label>
							<select multiple class="form-control multiple-selectized" id="input-client" name="client[]">
								<option value="">All Clients</option>
								<?php
									foreach($client_list as $item) {
										$selected = in_array($item['id'], $client) ? ' selected' : '';
										echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['client_name'].'</option>';
									}
								?>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label for="input-package-created-from">Package Created From</label>
							<input type="date" class="form-control" id="input-package-created-from" name="package_created_from" value="<?php echo $package_created_from; ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label for="input-package-created-to">Package Created To</label>
							<input type="date" class="form-control" id="input-package-created-to" name="package_created_to" value="<?php echo $package_created_to; ?>">
						</div>
					</div>
					<div class="col-md-2">
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

<?php echo isset($carton_utilization_board_visualization_html) ? $carton_utilization_board_visualization_html : null; ?>