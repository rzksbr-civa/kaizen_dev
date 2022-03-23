<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">

</style>

<div class="page-header">
	<h1>Outbound Performance KPI's by Status</h1>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form>
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-data-to-show">Data to Show</label>
						<select class="form-control selectized" id="input-data-to-show" name="data_to_show">
							<?php
								foreach($data_to_show_list as $item_id => $item_name) {
									$selected = ($data_to_show == $item_id) ? ' selected' : '';
									echo '<option value="'.$item_id.'"'.$selected.'>'.$item_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-status">Status</label>
						<select multiple class="form-control selectized" id="input-status" name="status[]">
							<option value="">All Status</option>
							<?php
								foreach($status_list as $item) {
									$selected = in_array($item, $status) ? ' selected' : '';
									echo '<option value="'.$item.'"'.$selected.'>'.ucwords($item).'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-periodicity">Periodicity</label>
						<select class="form-control selectized" id="input-periodicity" name="periodicity">
							<option value=""></option>
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

<div class="kpi-graph" id="kpi-outbound-performance-by-status">
<?php
	if(empty($graph_data)) {
		echo '<h3>No data found</h3>';
	}
?>
</div>

<?php endif; ?>
