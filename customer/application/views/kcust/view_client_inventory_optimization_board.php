<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.row-color-red {
		background-color: #C52428;
		color: white;
	}
	
	.row-color-yellow {
		background-color: #FFFF00;
		color: black;
	}
	
	.row-color-green {
		background-color: #028A0F;
		color: white;
	}
	
	.color-red {
		color: #C52428;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kcust/app/redstag-logo.png'); ?>" width="200"><br>
		CLIENT INVENTORY OPTIMIZATION BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-replenishment-release-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-service-level">Service Level % <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Service level is the probability of inventory being in stock when an order is placed. Increasing service level requires you to carry more inventory and potentially incur higher storage fees. 97% is a standard value."></span></label>
						<div class="input-group">
							<input type="number" step="1" class="form-control" id="input-service-level-percentage" name="service_level_percentage" value="<?php echo $service_level_percentage; ?>">
							<span class="input-group-addon">%</span>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-service-level">Sort Order <span class="glyphicon glyphicon-question-sign" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="How should the last 90 day sales in Projected Stock Out Dates table sorted?"></span></label>
						<select class="form-control selectized" id="input-sort-order" name="sort_order">
							<?php
								foreach($sort_order_list as $sort_order_id => $sort_order_name) {
									$selected = ($sort_order == $sort_order_id) ? ' selected' : '';
									echo '<option value="'.$sort_order_id.'"'.$selected.'>'.$sort_order_name.'</option>';
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

<?php if($generate) : ?>

<div id="client-inventory-optimization-visualization-area">
	<?php echo $client_inventory_optimization_board_visualization_html; ?>
</div>

<?php endif; ?>