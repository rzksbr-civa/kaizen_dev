<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.chart-box-title {
		text-align: center;
		font-size: 20px;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		SHIPMENT REPORT
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-shipment-report-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-report-type">Report Type</label>
						<select class="form-control selectized" id="input-report-type" name="report_type">
							<?php
								foreach($report_type_list as $item) {
									$selected = ($report_type == $item['name']) ? ' selected' : '';
									echo '<option value="'.$item['name'].'"'.$selected.'>'.$item['label'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-facility">Facility</label>
						<select class="form-control selectized" id="input-facility" name="facility">
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
									$selected = ($periodicity == $item['name'])  ? ' selected' : '';
									echo '<option value="'.$item['name'].'"'.$selected.'>'.$item['label'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-excluded-customers">Excluded Customers</label>
						<select multiple class="form-control multiple-selectized" id="input-excluded-customers" name="excluded_customers[]">
							<option value="">No Excluded Customers</option>
							<?php
								foreach($store_list as $item) {
									$selected = !empty($excluded_customers) && in_array($item['store_id'], $excluded_customers) ? ' selected' : '';
									echo '<option value="'.$item['store_id'].'"'.$selected.'>'.$item['name'].'</option>';
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
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
					
					<!-- <div class="form-group">
						<label>&nbsp;</label>
						<button type="button" class="form-control btn btn-primary" id="btn-export">Export</button>
					</div> -->
				</div>
			</div>
		</form>
	</div>
</div>
	
	<?php if($generate) : ?>
	
		<?php if($report_type == 'no_breakdown') : ?>
	
		<div class="row">
			<div class="col-md-12">
				<table class="table" id="shipment-report-table">
					<thead>
						<tr>
							<th>Period</th>
							<th>Demand</th>
							<th>Num Employees Worked</th>
							<th>Labor Hours Worked</th>
							<th>Labor Hours Per Package</th>
							<th>Cost</th>
							<th>Cost Per Package</th>
						</tr>
					</thead>
					<tbody>
					
						<?php foreach($table_data as $period_label => $period_data) : ?>
						
						<tr>
							<td><?php echo $period_label; ?></td>
							<td><?php echo $period_data['demand']; ?></td>
							<td><?php echo $period_data['num_employees']; ?></td>
							<td><?php echo number_format($period_data['labor_hours_worked'], 2); ?></td>
							<td><?php echo number_format($period_data['labor_hours_per_package'], 2); ?></td>
							<td><?php echo '$' . number_format($period_data['cost'], 2); ?></td>
							<td><?php echo '$' . number_format($period_data['cost_per_package'], 2); ?></td>
						</tr>
						
						<?php endforeach; ?>
					
					</tbody>
				</table>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Demand</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="demand-chart"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Num Employees Worked</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="num-employees-worked-chart"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Labor Hours Worked</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="labor-hours-worked-chart"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Labor Hours Per Package</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="labor-hours-per-package-chart"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Cost</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="cost-chart"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Cost Per Package</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="cost-per-package-chart"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<?php elseif($report_type == 'breakdown_by_product_family') : ?>
		
		<h3>Volume</h3>
		
		<div class="row">
			<div class="col-md-12">
				<table class="table datatabled datatabled-entity" style="width:<?php echo count(array_filter(array_column($assignment_types, 'show'))) * 100 + 300; ?>px;">
					<thead>
						<tr>
							<th style="width:200px;">Period</th>
							<?php 
								foreach($assignment_types as $assignment_type) {
									if($assignment_type['show']) {
										echo '<th style="width:100px;">' . $assignment_type['assignment_type_name'] . '</th>';
									}
								}
							?>
							<th style="width:100px;">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($table_data as $period_label => $period_data) : ?>
						
						<tr>
							<td><?php echo $period_label; ?></td>
							
							<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
									if($assignment_type['show']) :
							?>
							
							<td><?php echo number_format($period_data['action'][$assignment_type_id]); ?></td>
							
							<?php endif; endforeach; ?>
							
							<td><?php echo number_format($period_data['total']['action']); ?></td>
						</tr>
						
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<td>Total</td>
						<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
								if($assignment_type['show']) :
						?>
						
						<td><?php echo number_format($assignment_type['total_actions']); ?></td>
						
						<?php endif; endforeach; ?>
						
						<td><?php echo number_format($overall_total['action']); ?></td>
					</tfoot>
				</table>
			</div>
		</div>
		
		<h3>Labor Hours Worked</h3>
		
		<div class="row">
			<div class="col-md-12">
				<table class="table datatabled datatabled-entity" style="width:<?php echo count(array_filter(array_column($assignment_types, 'show'))) * 100 + 300; ?>px;">
					<thead>
						<tr>
							<th style="width:200px;">Period</th>
							<?php 
								foreach($assignment_types as $assignment_type) {
									if($assignment_type['show']) {
										echo '<th style="width:100px;">' . $assignment_type['assignment_type_name'] . '</th>';
									}
								}
							?>
							<th style="width:100px;">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($table_data as $period_label => $period_data) : ?>
						
						<tr>
							<td><?php echo $period_label; ?></td>
							
							<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
									if($assignment_type['show']) :
							?>
							
							<td><?php echo isset($period_data['labor_hours_worked'][$assignment_type_id]) ? number_format($period_data['labor_hours_worked'][$assignment_type_id],2) : 0; ?></td>
							
							<?php endif; endforeach; ?>
							
							<td><?php echo number_format($period_data['total']['labor_hours_worked'], 2); ?></td>
						</tr>
						
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<td>Total</td>
						<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
								if($assignment_type['show']) :
						?>
						
						<td><?php echo number_format($assignment_type['total_labor_hours_worked'], 2); ?></td>
						
						<?php endif; endforeach; ?>
						
						<td><?php echo number_format($overall_total['labor_hours_worked'], 2); ?></td>
					</tfoot>
				</table>
			</div>
		</div>
		
		<h3>Labor Hours Per Family</h3>
		
		<div class="row">
			<div class="col-md-12">
				<table class="table datatabled datatabled-entity" style="width:<?php echo count(array_filter(array_column($assignment_types, 'show'))) * 100 + 300; ?>px;">
					<thead>
						<tr>
							<th style="width:200px;">Period</th>
							<?php 
								foreach($assignment_types as $assignment_type) {
									if($assignment_type['show']) {
										echo '<th style="width:100px;">' . $assignment_type['assignment_type_name'] . '</th>';
									}
								}
							?>
							<th style="width:100px;">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($table_data as $period_label => $period_data) : ?>
						
						<tr>
							<td><?php echo $period_label; ?></td>
							
							<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
									if($assignment_type['show']) :
							?>
							
							<td><?php echo isset($period_data['labor_hours_per_assignment'][$assignment_type_id]) ? number_format($period_data['labor_hours_per_assignment'][$assignment_type_id],2) : 0; ?></td>
							
							<?php endif; endforeach; ?>
							
							<td><?php echo $period_data['total']['action'] > 0 ? number_format($period_data['total']['labor_hours_worked'] / $period_data['total']['action'], 2) : 0; ?></td>
						</tr>
						
						<?php endforeach; ?>
					</tbody>
					<!-- <tfoot>
						<td>Total</td>
						<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
								if($assignment_type['show']) :
						?>
						
						<td><?php echo $assignment_type['total_actions'] > 0 ? number_format($assignment_type['total_labor_hours_worked'] / $assignment_type['total_actions'], 2) : 0; ?></td>
						
						<?php endif; endforeach; ?>
						
						<td><?php echo $overall_total['action'] > 0 ? number_format($overall_total['labor_hours_worked'] / $overall_total['action'], 2) : 0; ?></td>
					</tfoot>-->
				</table>
			</div>
		</div>
		
		<h3>Cost</h3>
		
		<div class="row">
			<div class="col-md-12">
				<table class="table datatabled datatabled-entity" style="width:<?php echo count(array_filter(array_column($assignment_types, 'show'))) * 100 + 300; ?>px;">
					<thead>
						<tr>
							<th style="width:200px;">Period</th>
							<?php 
								foreach($assignment_types as $assignment_type) {
									if($assignment_type['show']) {
										echo '<th style="width:100px;">' . $assignment_type['assignment_type_name'] . '</th>';
									}
								}
							?>
							<th style="width:100px;">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($table_data as $period_label => $period_data) : ?>
						
						<tr>
							<td><?php echo $period_label; ?></td>
							
							<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
									if($assignment_type['show']) :
							?>
							
							<td><?php echo '$' . (isset($period_data['cost'][$assignment_type_id]) ? number_format($period_data['cost'][$assignment_type_id],2) : 0); ?></td>
							
							<?php endif; endforeach; ?>
							
							<td><?php echo '$' . number_format($period_data['total']['cost'], 2); ?></td>
						</tr>
						
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<td>Total</td>
						<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
								if($assignment_type['show']) :
						?>
						
						<td><?php echo '$' . number_format($assignment_type['total_cost'], 2); ?></td>
						
						<?php endif; endforeach; ?>
						
						<td><?php echo '$' . number_format($overall_total['cost'], 2); ?></td>
					</tfoot>
				</table>
			</div>
		</div>
		
		<h3>Cost Per Family</h3>
		
		<div class="row">
			<div class="col-md-12">
				<table class="table datatabled datatabled-entity" style="width:<?php echo count(array_filter(array_column($assignment_types, 'show'))) * 100 + 300; ?>px;">
					<thead>
						<tr>
							<th style="width:200px;">Period</th>
							<?php 
								foreach($assignment_types as $assignment_type) {
									if($assignment_type['show']) {
										echo '<th style="width:100px;">' . $assignment_type['assignment_type_name'] . '</th>';
									}
								}
							?>
							<th style="width:100px;">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($table_data as $period_label => $period_data) : ?>
						
						<tr>
							<td><?php echo $period_label; ?></td>
							
							<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
									if($assignment_type['show']) :
							?>
							
							<td><?php echo '$' . (isset($period_data['cost_per_assignment'][$assignment_type_id]) ? number_format($period_data['cost_per_assignment'][$assignment_type_id],2) : 0); ?></td>
							
							<?php endif; endforeach; ?>
							
							<td><?php echo '$' . ($period_data['total']['action'] > 0 ? number_format($period_data['total']['cost'] / $period_data['total']['action'], 2) : 0); ?></td>
						</tr>
						
						<?php endforeach; ?>
					</tbody>
					<!-- <tfoot>
						<td>Total</td>
						<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
								if($assignment_type['show']) :
						?>
						
						<td><?php echo '$' . ($assignment_type['total_actions'] > 0 ? number_format($assignment_type['total_cost'] / $assignment_type['total_actions'], 2) : 0); ?></td>
						
						<?php endif; endforeach; ?>
						
						<td><?php echo '$' . ($overall_total['action'] > 0 ? number_format($overall_total['cost'] / $overall_total['action'], 2) : 0); ?></td>
					</tfoot>-->
				</table>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Volume</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="product-family-action-chart"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Labor Hours Worked</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="product-family-labor-hours-worked-chart"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Labor Hours Per Family</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="product-family-labor-hours-per-assignment-chart"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Cost</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="product-family-cost-chart"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<div class="panel-title chart-box-title">Cost Per Family</div>
					</div>
					<div class="panel-body">
						<div class="chart-wrapper">
							<div class="shipment-report-chart" id="product-family-cost-per-assignment-chart"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<?php endif; ?>
	
	<?php endif; ?>
