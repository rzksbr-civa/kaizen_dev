<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
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
	
	#waiting-asns-table td {
		font-size:16px;
		vertical-align:middle;
	}
	
	@media only screen and (max-width: 768px) {
		#waiting-asns-table-wrapper {
			width: 100%;
			overflow-x: scroll;
		}
		
		#waiting-asns-table {
			width: 1200px;
			overflow-x: scroll;
		}
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		INBOUND IDLE TIME BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-inbound-idle-time-board-filter">
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
						<label for="input-status">Status</label>
						<select multiple class="form-control multiple-selectized" id="input-status" name="status[]">
							<option value="">All Status except Complete</option>
							<?php
								foreach($delivery_status_list as $status_code => $status_name) {
									$selected = in_array($status_code, $status) ? ' selected' : '';
									echo '<option value="'.$status_code.'"'.$selected.'>'.$status_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-created-at-period-from">Created Date From</label>
						<input type="date" class="form-control" id="input-created-at-period-from" name="created_at_period_from" value="<?php echo $created_at_period_from; ?>">
					</div>
					<div class="form-group">
						<label for="input-created-at-period-to">Created Date To</label>
						<input type="date" class="form-control" id="input-created-at-period-to" name="created_at_period_to" value="<?php echo $created_at_period_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-accepted-at-period-from">Accepted Date From</label>
						<input type="date" class="form-control" id="input-accepted-at-period-from" name="accepted_at_period_from" value="<?php echo $accepted_at_period_from; ?>">
					</div>
					<div class="form-group">
						<label for="input-accepted-at-period-to">Accepted Date To</label>
						<input type="date" class="form-control" id="input-accepted-at-period-to" name="accepted_at_period_to" value="<?php echo $accepted_at_period_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-completed-date-period-from">Completed Date From</label>
						<input type="date" class="form-control" id="input-completed-date-period-from" name="completed_date_period_from" value="<?php echo $completed_date_period_from; ?>">
					</div>
					<div class="form-group">
						<label for="input-completed-date-period-to">Completed Date To</label>
						<input type="date" class="form-control" id="input-completed-date-period-to" name="completed_date_period_to" value="<?php echo $completed_date_period_to; ?>">
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

<div id="inbound-idle-time-waiting-asns-area">
	<?php echo $inbound_idle_time_waiting_asns_html; ?>
</div>

<?php endif; ?>