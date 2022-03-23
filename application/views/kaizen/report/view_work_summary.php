<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		WORK SUMMARY
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-work-summary-filter">
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
						<label for="input-periodicity">Periodicity</label>
						<select class="form-control selectized" id="input-periodicity" name="periodicity">
							<?php
								foreach($periodicity_list as $item) {
									$selected = ($periodicity == $item) ? ' selected' : '';
									echo '<option value="'.$item.'"'.$selected.'>'.ucwords($item).'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period_from">Period From</label>
						<input type="date" class="form-control" id="input-period_from" name="period_from" value="<?php echo $period_from; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period_from">Period To</label>
						<input type="date" class="form-control" id="input-period_to" name="period_to" value="<?php echo $period_to; ?>">
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

<?php if($generate && !empty($periodicity)) : ?>

<div id="work-summary-table-area">
	<table class="table">
		<thead>
			<th><?php echo $periodicity_label; ?></th>
			<th># of Operators</th>
			<th>Hours Worked</th>
			<th>Packages Shipped (Load Action Qty)</th>
			<th>Hours/Pkg Shipped</th>
			
			<?php if(!empty($facility)) : ?>
				<th>Cost/Pkg Shipped</th>
			<?php endif; ?>
		</thead>
		<tbody>
			<?php foreach($work_summary_data as $current_data) : ?>
				<tr>
					<td><?php echo $current_data['label']; ?></td>
					<td><?php echo $current_data['num_operators']; ?></td>
					<td><?php echo number_format($current_data['sum_of_time'] / 3600, 2); ?></td>
					<td><?php echo number_format($current_data['total_load_qty'], 0); ?></td>
					<td><?php echo number_format($current_data['hours_per_package'], 2); ?></td>
					
					<?php if(isset($current_data['cost_per_package'])) : ?>
						<td><?php echo number_format($current_data['cost_per_package'], 2); ?></td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot style="background-color:green;">
			<td>Total</td>
			<td></td>
			<td><?php echo number_format($total_sum_of_time / 3600, 2); ?></td>
			<td><?php echo number_format($total_load_qty, 0); ?></td>
			<td><?php echo number_format($hours_per_package, 2); ?></td>
			
			<?php if(isset($cost_per_package)) : ?>
				<td><?php echo number_format($cost_per_package, 2); ?></td>
			<?php endif; ?>
		</tfoot>
	</table>
</div>

<?php endif; ?>