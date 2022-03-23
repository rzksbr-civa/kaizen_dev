<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.ac-data-col {
		width: 27px;
		text-align: center;
		vertical-align: middle;
	}
	
	.ac-data-col-total {
		width: 40px;
		text-align: center;
	}
</style>

<div class="page-header">
	<h1>Outbound Performance KPI's by User</h1>
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

<div class="table-area invisible" style="width: <?php echo 250 + 100*(count($data['date_label'])+1); ?>px;">
<table class="table table-bordered" id="outbound-performance-kpi-by-user-table">
	<thead>
		<th style="text-align:center;">#</th>
		<th style="width:100px;">User</th>
		<?php
			foreach($data['date_label'] as $date_info) :
		?>
		<th class="ac-data-col"><?php echo $date_info['label']; ?></th>
		<?php 
			endforeach;
		?>
	</thead>
	<tbody>
		<?php if(!empty($data['user_data'])) : $rank=1; foreach($data['user_data'] as $user_name => $user_info) : ?>
			<tr>
				<td style="text-align:center;"><?php echo $rank++; ?></td>
				<td><img src="<?php echo file_exists(str_replace('application','assets',APPPATH).'data/kaizen/app/users/thumbnails/'.$user_name.'.jpg') ? base_url('assets/data/kaizen/app/users/thumbnails/'.$user_name.'.jpg') : base_url('assets/data/kaizen/app/users/thumbnails/no-photo.jpg'); ?>" width="30">&nbsp;&nbsp;<?php echo $user_name; ?></td>
				
				<?php
					$previous_data = null;
					foreach($data['date_label'] as $date => $date_info) {
						$current_data = $data['user_data'][$user_name][$date];
						$additional_class = '';
						if($date <> 'total') {
							if($previous_data !== null) {
								if($current_data < $previous_data) {
									$additional_class = ' text-danger';
								}
								else if($current_data > $previous_data) {
									$additional_class = ' text-success';
								}
							}
						}
					
						echo '<td class="ac-data-col'.$additional_class.'">'.$current_data.'</td>';
						$previous_data = $current_data;
					}	
				?>
			</tr>
		
		<?php endforeach; endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<td>Total</td>
			<?php
				foreach($data['date_label'] as $date_info) {
					echo '<td class="ac-data-col">'.$date_info['total'].'</td>';
				}
			?>
		</tr>
	</tfoot>
</table>
</div>

<?php endif; ?>
