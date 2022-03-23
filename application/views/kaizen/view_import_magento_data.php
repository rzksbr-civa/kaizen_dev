<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		IMPORT MAGENTO DATA
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form>
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="input-period-from">Period From</label>
						<input type="date" class="form-control" id="input-period-from" name="period_from" value="<?php echo $period_from; ?>">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="input-period-to">Period To</label>
						<input type="date" class="form-control" id="input-period-to" name="period_to" value="<?php echo $period_to; ?>">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Import</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>
	<?php echo $message; ?>
<?php endif; ?>