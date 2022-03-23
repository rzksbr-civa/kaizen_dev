<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-12">
	<h2>Summary</h2>
	<div id="carrier-status-summary-table-wrapper">
		<table class="table" id="carrier-status-summary-table">
			<thead>
				<th>Carrier</th>
				<th>#Package</th>
				<th>#Delivered</th>
				<th>#Delivered - Late</th>
				<th>% Delivered On Time</th>
				<th>#Not Delivered</th>
				<th>#Not Delivered - Late</th>
			</thead>
			<tbody>
				<?php if(empty($carrier_status_summary_data)): ?>
					<tr>
						<td colspan="6" style="font-size:20px; text-align:center;">
							No data found.
						</td>
					</tr>
				<?php else: ?>
					<?php foreach($carrier_status_summary_data as $carrier_code => $current_data): 
							$color_code = 'green';
							if($current_data['delivered_ontime_percentage'] >= 70 && $current_data['delivered_ontime_percentage'] < 80) {
								$color_code = 'yellow';
							}
							else if($current_data['delivered_ontime_percentage'] < 70) {
								$color_code = 'red';
							}
						?>
						<tr class="row-color-<?php echo $color_code; ?>">
							<td><?php echo $carrier_code; ?></td>
							<td><?php echo $current_data['num_packages']; ?></td>
							<td><?php echo $current_data['num_delivered']; ?></td>
							<td><?php echo $current_data['num_delivered_late']; ?></td>
							<td><?php echo $current_data['delivered_ontime_percentage']; ?>%</td>
							<td><?php echo $current_data['num_not_delivered']; ?></td>
							<td><?php echo $current_data['num_not_delivered_late']; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="col-lg-12">
	<h2>Details</h2>
	<div id="carrier-status-table-wrapper">	
		<table class="table" id="carrier-status-table">
			<thead>
				<th>Package Created At</th>
				<th>Order Number</th>
				<th>Carrier</th>
				<th>Tracking Number</th>
				<th>Status</th>
				<th>Expected Delivery Date</th>
				<th>Actual Delivery Date</th>
				<th>Is Delivered?</th>
				<th>Late Days</th>
				<th>Last Checked At</th>
				<th>POD</th>
				<th></th>
			</thead>
			<tbody>
				<?php if(empty($carrier_status_dashboard_data)) : ?>
					<tr>
						<td colspan="11" style="font-size:20px; text-align:center;">
							No data found.
						</td>
					</tr>
				<?php else: ?>
					<?php $i=-1; foreach($carrier_status_dashboard_data as $current_data) : $i++; ?>
						<tr class="row-color-<?php echo $current_data['color']; ?>" id="tr-<?php echo $current_data['track_number']; ?>" row_index=<?php echo $i; ?>>
							<td><?php echo $current_data['local_package_created_time']; ?></td>
							<td><?php echo $current_data['order_number']; ?></td>
							<td id="carrier-code-<?php echo $i; ?>"><?php echo $current_data['carrier_code']; ?></td>
							<td id="track-number-<?php echo $i; ?>"><?php echo $current_data['track_number']; ?></td>
							<td id="status-<?php echo $current_data['track_number']; ?>"><?php echo $current_data['status']; ?></td>
							<td><?php echo $current_data['expected_delivery_date']; ?></td>
							<td id="actual-delivery-date-<?php echo $current_data['track_number']; ?>"><?php echo !empty($current_data['actual_delivery_date']) ? date('Y-m-d', strtotime($current_data['actual_delivery_date'])) : null; ?></td>
							<td><?php echo $current_data['is_delivered'] === true ? 'Yes' : 'No'; ?></td>
							<td><?php echo $current_data['late_days']; ?></td>
							<td><?php echo $current_data['last_checked_at']; ?></td>
							<td><?php echo $current_data['carrier_code'] == 'fedex' && $current_data['is_delivered'] === true ? '<a href="https://www.fedex.com/trackingCal/retrievePDF.jsp?accountNbr=&anon=true&appType=&destCountry=&locale=en_US&shipDate=&trackingCarrier=FDXG&trackingNumber='.$current_data['track_number'].'&type=SPOD" target="_blank"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a>' : null; ?></td>
							<td><a href="javascript:update_status('<?php echo $current_data['carrier_code']; ?>', '<?php echo $current_data['track_number']; ?>', '<?php echo $current_data['status']; ?>', '<?php echo $current_data['expected_delivery_date']; ?>', <?php echo $i; ?>);"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></a></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<br>

	<?php if(!empty($delivered_packages_track_numbers)): ?>
		<h4>POD Bulk Download</h4>

		<?php for($i=0; $i<count($delivered_packages_track_numbers); $i++): ?>
			<a class="btn btn-default" href="<?php echo base_url(PROJECT_CODE.'/carrier_status_dashboard_for_packages/download_pod/'.implode('-',$delivered_packages_track_numbers[$i]).'/POD-'.date('Ymd', strtotime($period_from)).'-'.date('Ymd', strtotime($period_to)).'part-'.($i+1)); ?>">Part <?php echo ($i+1); ?></a> 
		<?php endfor; ?>
	<?php endif; ?>

<?php endif; ?>