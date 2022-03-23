<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.package-board-title {
		text-align: center;
		font-size: 20px;
	}
	
	.order-info-label {
		width: 200px;
		text-align: right;
		font-weight: bold;
	}
	
	#package-board-table th {
		text-align: center;
	}
	
	<?php if($breakdown_type == 'family' && $display_type == 'summary'): ?>
	
	#package-board-table th, #package-board-table td {
		font-size: 24px;
	}
	
	<?php endif; ?>
	
	.total-header-cell {
		background-color: #FCF7B6;
		color: black;
	}
	
	.packing-header-cell {
		background-color: #FF7C80;
		color: black;
	}
	
	.picking-header-cell {
		background-color: #A9D08E;
		color: black;
	}
	
	.loading-header-cell {
		background-color: #8EA9DB;
		color: black;
	}
	
	#package-board-table td {
		text-align: right;
	}
	
	#package-board-table td.row-label {
		text-align: left;
	}
	
	.row-color-red {
		background-color: #C21807;
		color: white;
	}
	
	.row-color-green {
		background-color: green;
		color: white;
	}
	
	.row-color-yellow {
		background-color: yellow;
		color: black;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		PACKAGE BOARD
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-package-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-breakdown-type">Breakdown Type</label>
						<select class="form-control selectized" id="input-breakdown-type" name="breakdown_type">
							<?php
								foreach($breakdown_type_list as $key => $breakdown_type_name) {
									$selected = ($breakdown_type == $key) ? ' selected' : '';
									echo '<option value="'.$key.'"'.$selected.'>'.$breakdown_type_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-calculation-method">Calculation Method</label>
						<select class="form-control selectized" id="input-calculation-method" name="calculation_method">
							<?php
								foreach($calculation_method_list as $key => $calculation_method_name) {
									$selected = ($calculation_method == $key) ? ' selected' : '';
									echo '<option value="'.$key.'"'.$selected.'>'.$calculation_method_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-facility">Facility</label>
						<select multiple class="form-control multiple-selectized" id="input-facility" name="facility[]">
							<option value="">All Facilities</option>
							<?php
								foreach($facility_list as $item) {
									$selected = in_array($item['id'], $facility) ? ' selected' : '';
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
					<div class="form-group">
						<label for="input-period-to">Period To</label>
						<input type="date" class="form-control" id="input-period-to" name="period_to" value="<?php echo $period_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-display-type">Display Type</label>
						<select class="form-control selectized" id="input-display-type" name="display_type">
							<?php
								foreach($display_type_list as $item) {
									$selected = ($display_type == $item) ? ' selected' : '';
									echo '<option value="'.$item.'"'.$selected.'>'.ucwords($item).'</option>';
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

<?php if($generate) :
		if($breakdown_type == 'family'  && $display_type == 'summary') $table_width = 800; ?>
	<table class="table datatabled-entity" id="package-board-table" style="width:<?php echo $table_width; ?>px;">
		<thead>
			<?php if($breakdown_type == 'family' && $display_type == 'summary') : ?>
			<th style="width:200px;">Row Labels</th>
			<th class="total-header-cell">Total Shipments</th>
			<th class="total-header-cell">Total Labor Hours</th>
			<th class="total-header-cell">Total Labor Hours Per Shipment</th>
			<?php else: ?>
			
				<?php if($breakdown_type == 'customer') : ?>
				
				<th style="width:200px;">Merchant Name</th>
				
				<?php endif; ?>
			
			
				<?php if($breakdown_type == 'customer') : ?>
				
				<th style="width:200px;">Store Name</th>
				
				<?php else: ?>
				
				<th style="width:200px;">Row Labels</th>
				
				<?php endif; ?>
				
				<?php if($breakdown_type == 'shipment') : ?>
				
				<th>Customer Name</th>
				<th>Family Name</th>
				
				<?php endif; ?>

				<?php if($breakdown_type <> 'shipment') : ?>
				<th class="total-header-cell">Total Shipments</th>
				<?php endif; ?>
				
				<th class="total-header-cell">Total VA Time</th>
				<th class="total-header-cell">Total NVA Time</th>
				<th class="total-header-cell">Total Labor Hours</th>
				<th class="total-header-cell">Total Labor Cost</th>
				
				<?php if($breakdown_type <> 'shipment') : ?>
				<th class="total-header-cell">Total Labor Hours Per Shipment</th>
				<th class="total-header-cell">Total Labor Cost Per Shipment</th>
				<th class="packing-header-cell"># Shipments<br>(Packing)</th>
				<?php endif; ?>
				
				<th class="packing-header-cell">Packing VA Time (Hrs)</th>
				<th class="packing-header-cell">Packing NVA Time (Hrs)</th>
				<th class="packing-header-cell">Packing Labor Time (Hrs)</th>
				<th class="packing-header-cell">Packing Cost</th>
				
				<?php if($breakdown_type <> 'shipment') : ?>
				<th class="packing-header-cell">Packing Average Time Per Shipment</th>
				<th class="packing-header-cell">Packing Average Cost Per Shipment</th>
				<th class="picking-header-cell"># Shipments<br>(Picking)</th>
				<?php endif; ?>
				
				<th class="picking-header-cell">Picking VA Time (Hrs)</th>
				<th class="picking-header-cell">Picking NVA Time (Hrs)</th>
				<th class="picking-header-cell">Picking Labor Time (Hrs)</th>
				<th class="picking-header-cell">Picking Cost</th>
				
				<?php if($breakdown_type <> 'shipment') : ?>
				<th class="picking-header-cell">Picking Average Time Per Shipment</th>
				<th class="picking-header-cell">Picking Average Cost Per Shipment</th>
				<th class="loading-header-cell"># Shipments<br>(Loading)</th>
				<?php endif; ?>
				
				<th class="loading-header-cell">Loading VA Time (Hrs)</th>
				<th class="loading-header-cell">Loading NVA Time (Hrs)</th>
				<th class="loading-header-cell">Loading Labor Time (Hrs)</th>
				<th class="loading-header-cell">Loading Cost</th>
				
				<?php if($breakdown_type <> 'shipment') : ?>
				<th class="loading-header-cell">Loading Average Time Per Shipment</th>
				<th class="loading-header-cell">Loading Average Cost Per Shipment</th>
				<?php endif; ?>
			<?php endif; ?>
		</thead>
		<tbody>
			<?php foreach($table_data as $row) : ?>
				<tr>
					<?php if($breakdown_type == 'family' && $display_type == 'summary') : ?>
					<td class="row-label"><?php echo $row['row_name']; ?></td>
					<td><?php echo $row['total_shipments']; ?></td>
					<td><?php echo number_format($row['total_labor_hours'], 2); ?></td>
					
					<?php
						$row_color = 'row-color-red';
						$total_labor_hours_per_shipment = round($row['total_labor_hours_per_shipment'], 2);
						
						switch($row['row_name']) {
							case 'Bulk':
								if($total_labor_hours_per_shipment <= 0.04) {
									$row_color = 'row-color-green';
								}
								else if($total_labor_hours_per_shipment <= 0.06) {
									$row_color = 'row-color-yellow';
								}
								break;
							case 'Main':
								if($total_labor_hours_per_shipment <= 0.08) {
									$row_color = 'row-color-green';
								}
								else if($total_labor_hours_per_shipment <= 0.10) {
									$row_color = 'row-color-yellow';
								}
								break;
							case 'Mobile Pick/Pack/Load':
								if($total_labor_hours_per_shipment <= 0.04) {
									$row_color = 'row-color-green';
								}
								else if($total_labor_hours_per_shipment <= 0.06) {
									$row_color = 'row-color-yellow';
								}
								break;
							case 'Special Handling':
								if($total_labor_hours_per_shipment <= 0.11) {
									$row_color = 'row-color-green';
								}
								else if($total_labor_hours_per_shipment <= 0.13) {
									$row_color = 'row-color-yellow';
								}
								break;
							case 'USPS':
								if($total_labor_hours_per_shipment <= 0.06) {
									$row_color = 'row-color-green';
								}
								else if($total_labor_hours_per_shipment <= 0.08) {
									$row_color = 'row-color-yellow';
								}
								break;
							case 'Total':
								if($total_labor_hours_per_shipment <= 0.08) {
									$row_color = 'row-color-green';
								}
								else if($total_labor_hours_per_shipment <= 0.10) {
									$row_color = 'row-color-yellow';
								}
								break;
							default:
						}
					?>
					
					<td class="<?php echo $row_color; ?>"><?php echo number_format($row['total_labor_hours_per_shipment'], 2); ?></td>
					<?php else: ?>
						<?php if($breakdown_type == 'customer') : ?>
						
						<td class="row-label"><?php echo $row['merchant_name']; ?></td>
						
						<?php endif; ?>
					
						<td class="row-label"><?php echo $row['row_name']; ?></td>
						
						<?php if($breakdown_type == 'shipment') : ?>
						
						<td class="row-label"><?php echo $row['customer_name']; ?></td>
						<td class="row-label"><?php echo $row['family_name']; ?></td>
						
						<?php endif; ?>
						
						<?php if($breakdown_type <> 'shipment') : ?>
						<td><?php echo $row['total_shipments']; ?></td>
						<?php endif; ?>
						
						<td><?php echo number_format($row['total_va_time'], 2); ?></td>
						<td><?php echo number_format($row['total_nva_time'], 2); ?></td>
						<td><?php echo number_format($row['total_labor_hours'], 2); ?></td>
						<td><?php echo '$' . number_format($row['total_labor_cost'], 2); ?></td>
						
						<?php if($breakdown_type <> 'shipment') : ?>
						<td><?php echo number_format($row['total_labor_hours_per_shipment'], 2); ?></td>
						<td><?php echo '$' . number_format($row['total_labor_cost_per_shipment'], 2); ?></td>
						<td><?php echo $row['action_summary']['pack']['num_shipments']; ?></td>
						<?php endif; ?>
						
						<td><?php echo number_format($row['action_summary']['pack']['va_time'], 2); ?></td>
						<td><?php echo number_format($row['action_summary']['pack']['nva_time'], 2); ?></td>
						<td><?php echo number_format($row['action_summary']['pack']['time'], 2); ?></td>
						<td><?php echo '$' . number_format($row['action_summary']['pack']['cost'], 2); ?></td>
						
						<?php if($breakdown_type <> 'shipment') : ?>
						<td><?php echo number_format($row['action_summary']['pack']['avg_time'], 2); ?></td>
						<td><?php echo '$' . number_format($row['action_summary']['pack']['avg_cost'], 2); ?></td>
						<td><?php echo $row['action_summary']['pick']['num_shipments']; ?></td>
						<?php endif; ?>
						
						<td><?php echo number_format($row['action_summary']['pick']['va_time'], 2); ?></td>
						<td><?php echo number_format($row['action_summary']['pick']['nva_time'], 2); ?></td>
						<td><?php echo number_format($row['action_summary']['pick']['time'], 2); ?></td>
						<td><?php echo '$' . number_format($row['action_summary']['pick']['cost'], 2); ?></td>
						
						<?php if($breakdown_type <> 'shipment') : ?>
						<td><?php echo number_format($row['action_summary']['pick']['avg_time'], 2); ?></td>
						<td><?php echo '$' . number_format($row['action_summary']['pick']['avg_cost'], 2); ?></td>
						<td><?php echo $row['action_summary']['load']['num_shipments']; ?></td>
						<?php endif; ?>
						
						<td><?php echo number_format($row['action_summary']['load']['va_time'], 2); ?></td>
						<td><?php echo number_format($row['action_summary']['load']['nva_time'], 2); ?></td>
						<td><?php echo number_format($row['action_summary']['load']['time'], 2); ?></td>
						<td><?php echo '$' . number_format($row['action_summary']['load']['cost'], 2); ?></td>
						
						<?php if($breakdown_type <> 'shipment') : ?>
						<td><?php echo number_format($row['action_summary']['load']['avg_time'], 2); ?></td>
						<td><?php echo '$' . number_format($row['action_summary']['load']['avg_cost'], 2); ?></td>
						<?php endif; ?>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<?php $row = $table_total_data; ?>
				<?php if($breakdown_type == 'family' && $display_type == 'summary') : ?>
				<td class="row-label"><?php echo $row['row_name']; ?></td>
				<td><?php echo $row['total_shipments']; ?></td>
				<td><?php echo number_format($row['total_labor_hours'], 2); ?></td>
				<td class="<?php echo $row['total_labor_hours_per_shipment'] >= 0.07 ? 'row-color-red' : 'row-color-green'; ?>"><?php echo number_format($row['total_labor_hours_per_shipment'], 2); ?></td>
				<?php else: ?>
					<td class="row-label"><?php echo $row['row_name']; ?></td>
					
					<?php if($breakdown_type == 'shipment') : ?>
					<td></td>
					<td></td>
					<?php elseif($breakdown_type == 'customer') : ?>
					<td></td>
					<?php endif; ?>
					
					<?php if($breakdown_type <> 'shipment') : ?>
					<td><?php echo $row['total_shipments']; ?></td>
					<?php endif; ?>
					
					<td><?php echo number_format($row['total_va_time'], 2); ?></td>
					<td><?php echo number_format($row['total_nva_time'], 2); ?></td>
					<td><?php echo number_format($row['total_labor_hours'], 2); ?></td>
					<td><?php echo '$' . number_format($row['total_labor_cost'], 2); ?></td>
					
					<?php if($breakdown_type <> 'shipment') : ?>
					<td><?php echo number_format($row['total_labor_hours_per_shipment'], 2); ?></td>
					<td><?php echo '$' . number_format($row['total_labor_cost_per_shipment'], 2); ?></td>
					<td><?php echo $row['action_summary']['pack']['num_shipments']; ?></td>
					<?php endif; ?>
					
					<td><?php echo number_format($row['action_summary']['pack']['va_time'], 2); ?></td>
					<td><?php echo number_format($row['action_summary']['pack']['nva_time'], 2); ?></td>
					<td><?php echo number_format($row['action_summary']['pack']['time'], 2); ?></td>
					<td><?php echo '$' . number_format($row['action_summary']['pack']['cost'], 2); ?></td>
					
					<?php if($breakdown_type <> 'shipment') : ?>
					<td><?php echo number_format($row['action_summary']['pack']['avg_time'], 2); ?></td>
					<td><?php echo '$' . number_format($row['action_summary']['pack']['avg_cost'], 2); ?></td>
					<td><?php echo $row['action_summary']['pick']['num_shipments']; ?></td>
					<?php endif; ?>
					
					<td><?php echo number_format($row['action_summary']['pick']['va_time'], 2); ?></td>
					<td><?php echo number_format($row['action_summary']['pick']['nva_time'], 2); ?></td>
					<td><?php echo number_format($row['action_summary']['pick']['time'], 2); ?></td>
					<td><?php echo '$' . number_format($row['action_summary']['pick']['cost'], 2); ?></td>
					
					<?php if($breakdown_type <> 'shipment') : ?>
					<td><?php echo number_format($row['action_summary']['pick']['avg_time'], 2); ?></td>
					<td><?php echo '$' . number_format($row['action_summary']['pick']['avg_cost'], 2); ?></td>
					<td><?php echo $row['action_summary']['load']['num_shipments']; ?></td>
					<?php endif; ?>
					
					<td><?php echo number_format($row['action_summary']['load']['va_time'], 2); ?></td>
					<td><?php echo number_format($row['action_summary']['load']['nva_time'], 2); ?></td>
					<td><?php echo number_format($row['action_summary']['load']['time'], 2); ?></td>
					<td><?php echo '$' . number_format($row['action_summary']['load']['cost'], 2); ?></td>
					
					<?php if($breakdown_type <> 'shipment') : ?>
					<td><?php echo number_format($row['action_summary']['load']['avg_time'], 2); ?></td>
					<td><?php echo '$' . number_format($row['action_summary']['load']['avg_cost'], 2); ?></td>
					<?php endif; ?>
				<?php endif; ?>
			</tr>
		</tfoot>
	</table>
<?php endif; // Generate ?>