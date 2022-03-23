<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">
	.green-text {
		color: green;
		font-size: 18px;
		font-weight: bold;
	}
	
	.red-text {
		color: red;
		font-size: 18px;
		font-weight: bold;
	}
</style>

<h3>Summary</h3>

<table class="table table-bordered">
	<thead>
		<tr>
			<th rowspan="2"></th>
			<th colspan="2">FedEx</th>
			<th colspan="2">UPS</th>
			<th colspan="4">Total</th>
		</tr>
		<tr>
			<th>Current</th>
			<th>Optimized</th>
			<th>Current</th>
			<th>Optimized</th>
			<th>Current</th>
			<th>Optimized</th>
			<th>All FedEx</th>
			<th>All UPS</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td># Packages</td>
			<td><?php echo $results_by_carrier['fedex']['current_packages']; ?></td>
			<td><?php echo $results_by_carrier['fedex']['optimized_packages']; ?></td>
			<td><?php echo $results_by_carrier['ups']['current_packages']; ?></td>
			<td><?php echo $results_by_carrier['ups']['optimized_packages']; ?></td>
			<td colspan="4"><?php echo $results_by_carrier['fedex']['total_packages']; ?></td>
		</tr>
		<tr>
			<td>% Packages</td>
			<td><?php echo number_format($results_by_carrier['fedex']['current_packages'] / $results_by_carrier['fedex']['total_packages'] * 100, 2); ?>%</td>
			<td><?php echo number_format($results_by_carrier['fedex']['optimized_packages'] / $results_by_carrier['fedex']['total_packages'] * 100, 2); ?>%</td>
			<td><?php echo number_format($results_by_carrier['ups']['current_packages'] / $results_by_carrier['fedex']['total_packages'] * 100, 2); ?>%</td>
			<td><?php echo number_format($results_by_carrier['ups']['optimized_packages'] / $results_by_carrier['fedex']['total_packages'] * 100, 2); ?>%</td>
			<td colspan="4"></td>
		</tr>
		<tr>
			<td>Cost</td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['current']['cost'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['optimized']['cost'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['current']['cost'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['optimized']['cost'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['current']['cost'] + $results_by_carrier['ups']['current']['cost'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['optimized']['cost'] + $results_by_carrier['ups']['optimized']['cost'], 2); ?></td>
			<td>$ 
				<?php
					switch($fedex_tier) {
						case '1':
							echo number_format($results_by_carrier['fedex']['tier_1']['cost'], 2);
							break;
						case '2':
							echo number_format($results_by_carrier['fedex']['tier_2']['cost'], 2);
							break;
						case '3':
							echo number_format($results_by_carrier['fedex']['tier_3']['cost'], 2);
							break;
					}
				?>
			</td>
			<td>$ 
				<?php
					switch($ups_tier) {
						case '1':
							echo number_format($results_by_carrier['ups']['tier_1']['cost'], 2);
							break;
						case '2':
							echo number_format($results_by_carrier['ups']['tier_2']['cost'], 2);
							break;
						case '3':
							echo number_format($results_by_carrier['ups']['tier_3']['cost'], 2);
							break;
					}
				?>
			</td>
		</tr>
		<tr>
			<td>Profit</td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['current']['profit'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['optimized']['profit'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['current']['profit'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['optimized']['profit'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['current']['profit'] + $results_by_carrier['ups']['current']['profit'], 2); ?></td>
			<td>$ 
				<?php 
					echo number_format($results_by_carrier['fedex']['optimized']['profit'] + $results_by_carrier['ups']['optimized']['profit'], 2);
					$diff = ($results_by_carrier['fedex']['optimized']['profit'] + $results_by_carrier['ups']['optimized']['profit']) - ($results_by_carrier['fedex']['current']['profit'] + $results_by_carrier['ups']['current']['profit']);
					echo '<br><span class="'.($diff>=0 ? 'green-text' : 'red-text').'">('.($diff>=0 ? '+' : '-').'$ '.number_format(abs($diff),2).')</span>';
				?>
			</td>
			<td>$ 
				<?php
					$profit = null;
					switch($fedex_tier) {
						case '1':
							$profit = $results_by_carrier['fedex']['tier_1']['profit'];
							break;
						case '2':
							$profit = $results_by_carrier['fedex']['tier_2']['profit'];
							break;
						case '3':
							$profit = $results_by_carrier['fedex']['tier_3']['profit'];
							break;
					}
					
					echo number_format($profit,2);
					$diff = $profit - ($results_by_carrier['fedex']['current']['profit'] + $results_by_carrier['ups']['current']['profit']);
					echo '<br><span class="'.($diff>=0 ? 'green-text' : 'red-text').'">('.($diff>=0 ? '+' : '-').'$ '.number_format(abs($diff),2).')</span>';
				?>
			</td>
			<td>$ 
				<?php
					$profit = null;
					switch($ups_tier) {
						case '1':
							$profit = $results_by_carrier['ups']['tier_1']['profit'];
							break;
						case '2':
							$profit = $results_by_carrier['ups']['tier_2']['profit'];
							break;
						case '3':
							$profit = $results_by_carrier['ups']['tier_3']['profit'];
							break;
					}
					
					echo number_format($profit,2);
					$diff = $profit - ($results_by_carrier['fedex']['current']['profit'] + $results_by_carrier['ups']['current']['profit']);
					echo '<br><span class="'.($diff>=0 ? 'green-text' : 'red-text').'">('.($diff>=0 ? '+' : '-').'$ '.number_format(abs($diff),2).')</span>';
				?>
			</td>
		</tr>
		<tr>
			<td>Cost per Package</td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['current']['cost_per_package'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['optimized']['cost_per_package'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['current']['cost_per_package'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['optimized']['cost_per_package'], 2); ?></td>
			<td>$ <?php echo number_format(($results_by_carrier['fedex']['current']['cost'] + $results_by_carrier['ups']['current']['cost']) / $results_by_carrier['fedex']['total_packages'], 2); ?></td>
			<td>$ <?php echo number_format(($results_by_carrier['fedex']['optimized']['cost'] + $results_by_carrier['ups']['optimized']['cost']) / $results_by_carrier['fedex']['total_packages'], 2); ?></td>
			<td>$ 
				<?php
					switch($fedex_tier) {
						case '1':
							echo number_format($results_by_carrier['fedex']['tier_1']['cost_per_package'], 2);
							break;
						case '2':
							echo number_format($results_by_carrier['fedex']['tier_2']['cost_per_package'], 2);
							break;
						case '3':
							echo number_format($results_by_carrier['fedex']['tier_3']['cost_per_package'], 2);
							break;
					}
				?>
			</td>
			<td>$ 
				<?php
					switch($ups_tier) {
						case '1':
							echo number_format($results_by_carrier['ups']['tier_1']['cost_per_package'], 2);
							break;
						case '2':
							echo number_format($results_by_carrier['ups']['tier_2']['cost_per_package'], 2);
							break;
						case '3':
							echo number_format($results_by_carrier['ups']['tier_3']['cost_per_package'], 2);
							break;
					}
				?>
			</td>
		</tr>
		<tr>
			<td>Profit per Package</td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['current']['profit_per_package'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['fedex']['optimized']['profit_per_package'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['current']['profit_per_package'], 2); ?></td>
			<td>$ <?php echo number_format($results_by_carrier['ups']['optimized']['profit_per_package'], 2); ?></td>
			<td>$ <?php echo number_format(($results_by_carrier['fedex']['current']['profit'] + $results_by_carrier['ups']['current']['profit']) / $results_by_carrier['fedex']['total_packages'], 2); ?></td>
			<td>$ <?php echo number_format(($results_by_carrier['fedex']['optimized']['profit'] + $results_by_carrier['ups']['optimized']['profit']) / $results_by_carrier['fedex']['total_packages'], 2); ?></td>
			<td>$ 
				<?php
					switch($fedex_tier) {
						case '1':
							echo number_format($results_by_carrier['fedex']['tier_1']['profit_per_package'], 2);
							break;
						case '2':
							echo number_format($results_by_carrier['fedex']['tier_2']['profit_per_package'], 2);
							break;
						case '3':
							echo number_format($results_by_carrier['fedex']['tier_3']['profit_per_package'], 2);
							break;
					}
				?>
			</td>
			<td>$ 
				<?php
					switch($ups_tier) {
						case '1':
							echo number_format($results_by_carrier['ups']['tier_1']['profit_per_package'], 2);
							break;
						case '2':
							echo number_format($results_by_carrier['ups']['tier_2']['profit_per_package'], 2);
							break;
						case '3':
							echo number_format($results_by_carrier['ups']['tier_3']['profit_per_package'], 2);
							break;
					}
				?>
			</td>
		</tr>
	</tbody>
</table>

<h3>Results by Carrier</h3>

<div class="row">
	<div class="col-md-6">
		<div class="panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title">FedEx</h3>
			</div>
			<table class="table">
				<thead>
					<th></th>
					<th></th>
					<th>Profit</th>
					<th>Cost per Package</th>
					<th>Profit per Package</th>
				</thead>
				<tbody>
					<tr>
						<td>Packages</td>
						<td><?php echo $results_by_carrier['fedex']['total_packages']; ?></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>Tier 1</td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_1']['cost'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_1']['profit'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_1']['cost_per_package'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_1']['profit_per_package'], 2); ?></td>
					</tr>
					<tr>
						<td>Tier 2</td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_2']['cost'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_2']['profit'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_2']['cost_per_package'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_2']['profit_per_package'], 2); ?></td>
					</tr>
					<tr>
						<td>Tier 3</td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_3']['cost'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_3']['profit'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_3']['cost_per_package'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['tier_3']['profit_per_package'], 2); ?></td>
					</tr>
					<tr>
						<td>RSF</td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['rsf']['cost'],2); ?></td>
						<td></td>
						<td>$ <?php echo number_format($results_by_carrier['fedex']['rsf']['cost_per_package'],2); ?></td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<h3 class="panel-title">UPS</h3>
			</div>
			<table class="table">
				<thead>
					<th></th>
					<th></th>
					<th>Profit</th>
					<th>Cost per Package</th>
					<th>Profit per Package</th>
				</thead>
				<tbody>
					<tr>
						<td>Packages</td>
						<td><?php echo $results_by_carrier['ups']['total_packages']; ?></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>Tier 1</td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_1']['cost'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_1']['profit'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_1']['cost_per_package'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_1']['profit_per_package'], 2); ?></td>
					</tr>
					<tr>
						<td>Tier 2</td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_2']['cost'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_2']['profit'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_2']['cost_per_package'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_2']['profit_per_package'], 2); ?></td>
					</tr>
					<tr>
						<td>Tier 3</td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_3']['cost'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_3']['profit'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_3']['cost_per_package'], 2); ?></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['tier_3']['profit_per_package'], 2); ?></td>
					</tr>
					<tr>
						<td>RSF</td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['rsf']['cost'],2); ?></td>
						<td></td>
						<td>$ <?php echo number_format($results_by_carrier['ups']['rsf']['cost_per_package'],2); ?></td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php endif; ?>