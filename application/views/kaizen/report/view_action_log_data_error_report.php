<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.page-title {
		text-align: center;
		font-size: 20px;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		ACTION LOG DATA ERROR REPORT
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-package-board-filter">
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
		</form>
	</div>
</div>

<?php if($generate) : ?>
	<table class="table datatabled-entity" id="action-log-data-error-report-table">
		<thead>
			<th>Log ID</th>
			<th>Facility</th>
			<th>Customer</th>
			<th>Employee</th>
			<th>Action</th>
			<th>Shipment No</th>
			<th>Started At</th>
			<th>Finished At</th>
			<th>Duration</th>
		</thead>
		<tbody>
			<?php foreach($table_data as $current_data) : ?>
			
			<tr>
				<td><?php echo $current_data['log_id']; ?></td>
				<td><?php echo $current_data['facility']; ?></td>
				<td><?php echo $current_data['customer']; ?></td>
				<td><?php echo $current_data['employee']; ?></td>
				<td><?php echo $current_data['action']; ?></td>
				<td><?php echo $current_data['shipment_no']; ?></td>
				<td><?php echo $current_data['started_at']; ?></td>
				<td><?php echo $current_data['finished_at']; ?></td>
				<td><?php echo isset($current_data['duration']) ? sprintf('%02d:%02d:%02d', floor($current_data['duration']/3600), floor($current_data['duration']/60) % 60, $current_data['duration'] % 60) : null; ?></td>
			</tr>
			
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; // Generate ?>