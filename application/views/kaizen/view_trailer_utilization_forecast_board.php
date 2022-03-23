<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.row-red {
		background-color: #C21807;
		color: white;
		transition: 1200ms ease;
	}
	
	.row-yellow {
		background-color: yellow;
		color: black;
	}
	
	.row-green {
		background-color: green;
		color: white;
	}
	
	#auto-refresh-button-area {
		position: fixed;
		top: 50px;
		right: 20px;
		width: 200px;
		padding: 10px 30px 30px 30px;
		z-index: 100;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		TRAILER UTILIZATION FORECAST BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-trailer-utilization-forecast-board-filter">
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

<?php if($generate) : ?>

<div id="trailer-utilization-forecast-visualization-area">
	<?php echo $trailer_utilization_forecast_board_visualization_html; ?>
</div>

<?php endif; ?>