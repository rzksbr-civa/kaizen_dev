<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

	<?php if($report_type == 'summary'): ?>
		<div class="col-lg-9">
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

		<div class="col-lg-3">
			<h2>Transit Time</h2>
			<table class="table" id="transit-time-table">
				<thead>
					<th>Transit Time</th>
					<th>Actual</th>
				</thead>
				<?php if(!empty($transit_time_data)):
						foreach($transit_time_data as $current_data): ?>
					<tr>
						<td><?php echo $current_data['transit_day']; ?> Day</td>
						<td><?php echo number_format($current_data['actual_percentage'],2) ?>%</td>
					</tr>
				<?php endforeach; endif; ?>
			</table>
		</div>
	<?php else: ?>
		<div class="col-lg-6">
			<h2>By Zone</h2>
			<div id="carrier-zone-summary-table-wrapper">
				<table class="table" id="carrier-zone-summary-table">
					<thead>
						<th>Zone</th>
						<th>#Package</th>
						<th>#Late</th>
						<th>%Ontime</th>
					</thead>
					<tbody>
						<?php foreach($summary['zone'] as $zone => $zone_data): ?>
							<tr>
								<td><?php echo $zone; ?></td>
								<td><?php echo $zone_data['all_packages']; ?></td>
								<td><?php echo $zone_data['late_packages']; ?></td>
								<td><?php echo number_format(($zone_data['all_packages'] - $zone_data['late_packages']) / $zone_data['all_packages'] * 100, 2); ?>%</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		
		<div class="col-lg-6">
			<h2>By State</h2>
			<div id="carrier=state-summary-table-wrapper">
				<table class="table" id="carrier-state-summary-table">
					<thead>
						<th>State</th>
						<th>#Package</th>
						<th>#Late</th>
						<th>%Ontime</th>
					</thead>
					<tbody>
						<?php foreach($summary['state'] as $state => $state_data): ?>
							<tr>
								<td><?php echo $state; ?></td>
								<td><?php echo $state_data['all_packages']; ?></td>
								<td><?php echo $state_data['late_packages']; ?></td>
								<td><?php echo number_format(($state_data['all_packages'] - $state_data['late_packages']) / $state_data['all_packages'] * 100, 2); ?>%</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
			
	<?php endif; ?>
	
<div class="col-lg-12">
	<h2>Details</h2>
	<?php if(count($package_status_board_data) == 10000) : ?>
		<div class="alert alert-warning" role="alert">Your query returns too many data. We only show the latest 10000 data here. Try to use the filter to get the information you want.</div>
	<?php endif; ?>


	<div id="carrier-status-table-wrapper">	
		<table class="table" id="carrier-status-table">
			<thead>
				<th style="width:60px;">Customer Name</th>
				<th style="width:60px;">Package Created At</th>
				<th style="width:60px;">Order Number</th>
				<th style="width:60px;">Carrier</th>
				<th style="width:80px;">Shipping Method</th>
				<th style="width:60px;">Tracking Number</th>
				<th style="width:80px;">Status</th>
				<th style="width:60px;">Expected Delivery Date</th>
				<th style="width:60px;">Actual Delivery Date</th>
				<th style="width:40px;">Is Delivered?</th>
				<th style="width:40px;">Late Days</th>
				<th style="width:60px;">Last Checked At</th>
				<th style="width:30px;">POD</th>
				<th style="width:20px;"></th>
			</thead>
			<tbody>
				<?php if(empty($package_status_board_data)) : ?>
					<tr>
						<td colspan="13" style="font-size:20px; text-align:center;">
							No data found.
						</td>
				<?php else: ?>
					<?php $i=-1; foreach($package_status_board_data as $current_data) : $i++; ?>
						<tr class="row-color-<?php echo $current_data['color']; ?>" id="tr-<?php echo preg_replace("/[^A-Za-z0-9 ]/", '', $current_data['track_number']); ?>" row_index=<?php echo $i; ?>>
							<td><?php echo $current_data['customer_name']; ?></td>
							<td><?php echo $current_data['local_package_created_time']; ?></td>
							<td><?php echo $current_data['order_number']; ?></td>
							<td><?php echo $current_data['carrier_code']; ?></td>
							<td><?php echo $current_data['shipping_method']; ?></td>
							<td><?php echo $current_data['track_number']; ?></td>
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
			<a class="btn btn-default" href="<?php echo base_url(PROJECT_CODE.'/package_status_board/download_pod/'.implode('-',$delivered_packages_track_numbers[$i]).'/POD-'.date('Ymd', strtotime($period_from)).'-'.date('Ymd', strtotime($period_to)).'part-'.($i+1)); ?>">Part <?php echo ($i+1); ?></a> 
		<?php endfor; ?>
	<?php endif; ?>

<?php endif; ?>