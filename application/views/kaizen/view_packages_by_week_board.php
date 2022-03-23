<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		PACKAGES BY WEEK BOARD
	</h3>	
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-packages-by-week-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-week-grouping">Week Grouping</label>
						<select class="form-control selectized" id="input-week-grouping" name="week_grouping">
							<?php
								foreach($week_grouping_list as $item_id => $item_name) {
									$selected = ($week_grouping == $item_id) ? ' selected' : '';
									echo '<option value="'.$item_id.'"'.$selected.'>'.$item_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-first-day-of-week">First Day of Week</label>
						<select class="form-control selectized" id="input-first-day-of-week" name="first_day_of_week">
							<?php
								foreach($first_day_of_week_list as $item) {
									$selected = ($first_day_of_week == $item) ? ' selected' : '';
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

<div id="packages-by-week-board-visualization">
	<?php echo $packages_by_week_board_visualization_html; ?>
</div>

<?php endif; ?>