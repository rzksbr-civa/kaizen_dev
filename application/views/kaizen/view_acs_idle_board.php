<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.row-color-red {
		background-color: #C21807;
		color: white;
	}
	
	.row-color-yellow {
		background-color: yellow;
		color: black;
	}
	
	.row-color-green {
		background-color: green;
		color: white;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		ACs IDLE BOARD
	</h3>
	
	<div style="text-align:center;">Last Updated: <span id="page-last-updated-text"><?php echo $page_generated_time; ?></span></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-acs-idle-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-facility">Facility</label>
						<select class="form-control selectized" id="input-facility" name="facility">
							<option value="">All Facilities</option>
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
						<label for="input-data-visibility">Data Visibility</label>
						<select class="form-control selectized" id="input-data-visibility" name="data_visibility">
							<?php
								foreach($data_visibility_list as $visibility_code => $item) {
									$selected = ($data_visibility == $visibility_code) ? ' selected' : '';
									echo '<option value="'.$visibility_code.'"'.$selected.'>'.$item.'</option>';
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

<div id="acs-idle-board-visualization-area">
	<?php echo $acs_idle_board_visualization_html; ?>
</div>

<?php endif; ?>