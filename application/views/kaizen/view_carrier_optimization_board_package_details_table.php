<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<h3>Package Details</h3>

<div class="table-area">
	<table class="table datatabled-entity carrier-optimization-table" id="package-details-table" style="width:4400px;">
		<thead>
			<th>Facility</th>
			<th>Shipment #</th>
			<th>Merchant Name</th>
			<th>Carrier</th>
			<th>Shipping Method</th>
			<th>Zip</th>
			<th>Package Created At</th>
			<th>Client Billable FedEx Weight</th>
			<th>Client Billable UPS Weight</th>
			<th>RSF Billable FedEx Weight</th>
			<th>RSF Billable UPS Weight</th>
			<th>FedEx Zone</th>
			<th>UPS Zone</th>
			<th>FedEx Base Shipping Cost</th>
			<th>FedEx Client Tier 1 Cost</th>
			<th>FedEx Client Tier 2 Cost</th>
			<th>FedEx Client Tier 3 Cost</th>
			<th>FedEx RSF Cost</th>
			<th>UPS Base Shipping Cost</th>
			<th>UPS Client Tier 1 Cost</th>
			<th>UPS Client Tier 2 Cost</th>
			<th>UPS Client Tier 3 Cost</th>
			<th>UPS RSF Cost</th>
			<th>FedEx Total Published Surcharge</th>
			<th>FedEx Total RSF Surcharge</th>
			<th>FedEx Total Client Surcharge</th>
			<th>UPS Total Published Surcharge</th>
			<th>UPS Total RSF Surcharge</th>
			<th>UPS Total Client Surcharge</th>
			<th>Profit via FedEx (Base)</th>
			<th>Profit via UPS (Base)</th>
			<th>UPS Profit - FedEx Profit (Base)</th>
			<th>Preferred Carrier (Base)</th>
		</thead>
		<tbody>
			<?php foreach($package_details_data as $current_data) : ?>
				<tr>
					<td><?php echo $current_data['facility_name']; ?></td>
					<td><?php echo $current_data['shipment_no']; ?></td>
					<td><?php echo $current_data['merchant_name']; ?></td>
					<td><?php echo $current_data['carrier']; ?></td>
					<td><?php echo $current_data['shipping_method']; ?></td>
					<td><?php echo $current_data['fedex_zip_code']; ?></td>
					<td><?php echo $current_data['package_created_at']; ?></td>
					<td><?php echo $current_data['client_billable_fedex_weight']; ?></td>
					<td><?php echo $current_data['client_billable_ups_weight']; ?></td>
					<td><?php echo $current_data['rsf_billable_fedex_weight']; ?></td>
					<td><?php echo $current_data['rsf_billable_ups_weight']; ?></td>
					<td><?php echo $current_data['fedex_zone']; ?></td>
					<td><?php echo $current_data['ups_zone']; ?></td>
					<td><?php echo isset($current_data['fedex_base_shipping_cost']) ? number_format($current_data['fedex_base_shipping_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['fedex_client_tier_1_cost']) ? number_format($current_data['fedex_client_tier_1_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['fedex_client_tier_2_cost']) ? number_format($current_data['fedex_client_tier_2_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['fedex_client_tier_3_cost']) ? number_format($current_data['fedex_client_tier_3_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['fedex_rsf_cost']) ? number_format($current_data['fedex_rsf_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_base_shipping_cost']) ? number_format($current_data['ups_base_shipping_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_client_tier_1_cost']) ? number_format($current_data['ups_client_tier_1_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_client_tier_2_cost']) ? number_format($current_data['ups_client_tier_2_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_client_tier_3_cost']) ? number_format($current_data['ups_client_tier_3_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_rsf_cost']) ? number_format($current_data['ups_rsf_cost'],2) : ''; ?></td>
					<td><?php echo isset($current_data['fedex_published_total_surcharge']) ? number_format($current_data['fedex_published_total_surcharge'],2) : ''; ?></td>
					<td><?php echo isset($current_data['fedex_rsf_total_surcharge']) ? number_format($current_data['fedex_rsf_total_surcharge'],2) : ''; ?></td>
					<td><?php echo isset($current_data['fedex_client_total_surcharge']) ? number_format($current_data['fedex_client_total_surcharge'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_published_total_surcharge']) ? number_format($current_data['ups_published_total_surcharge'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_rsf_total_surcharge']) ? number_format($current_data['ups_rsf_total_surcharge'],2) : ''; ?></td>
					<td><?php echo isset($current_data['ups_client_total_surcharge']) ? number_format($current_data['ups_client_total_surcharge'],2) : ''; ?></td>
					<td><?php echo isset($current_data['profit_fedex']) ? number_format($current_data['profit_fedex'],2) : ''; ?></td>
					<td><?php echo isset($current_data['profit_ups']) ? number_format($current_data['profit_ups'],2) : ''; ?></td>
					<td><?php echo isset($current_data['profit_diff']) ? number_format($current_data['profit_diff'],2) : ''; ?></td>
					<td><?php echo isset($current_data['preferred_carrier']) ? $current_data['preferred_carrier'] : null; ?></td>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>