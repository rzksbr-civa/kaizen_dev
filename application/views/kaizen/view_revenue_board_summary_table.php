<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.revenue-summary-title {
		text-align: center;
	}
	
	.revenue-summary-content {
		text-align: center;
		font-size: 32px;
		font-weight: bold;
	}
</style>

<?php if($generate) : ?>

<div class="row">
	<div class="col-md-3">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title revenue-summary-title">Package Revenue</h3>
			</div>
			<div class="panel-body revenue-summary-content">
				$ <?php echo number_format($revenue_summary['total_package_revenue'],2); ?>
			</div>
		</div>
	</div>
	
	<div class="col-md-3">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<h3 class="panel-title revenue-summary-title">Total Wages with Overhead</h3>
			</div>
			<div class="panel-body revenue-summary-content">
				$ <?php echo number_format($revenue_summary['total_wages_with_overhead'],2); ?>
			</div>
			<table class="table">
				<?php foreach($department_list as $department_code => $department_name) : ?>
					<tr>
						<td><?php echo $department_name; ?></td>
						<td><?php echo '$' . number_format($revenue_summary['wages_with_overhead'][$department_code],2); ?></td>
					</tr>
				<?php endforeach ?>
			</table>
		</div>
	</div>
	
	<div class="col-md-3">
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title revenue-summary-title">Outbound Profit</h3>
			</div>
			<div class="panel-body revenue-summary-content">
				$ <?php echo number_format($revenue_summary['outbound_profit'],2); ?>
			</div>
		</div>
	</div>
	
	<div class="col-md-3">
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title revenue-summary-title">Inbound Revenue</h3>
			</div>
			<div class="panel-body revenue-summary-content">
				$ <?php echo number_format($revenue_summary['inbound_revenue'],2); ?>
			</div>
		</div>
	</div>
</div>

<?php
	foreach($department_list as $department_code => $department_name) :
?>
	<h3><?php echo $department_name; ?></h3>
	<div class="revenue-summary-chart" id="<?php echo $department_code; ?>-revenue-chart"></div>
<?php
	endforeach;
?>

<?php endif; ?>