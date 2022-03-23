<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		PRESHIFT CHECK
	</h3>	
</div>

<h3>Choose facility</h3>

<br>

<div class="col-md-4">
	<?php foreach($facility_list as $facility) : ?>
	<a type="button" class="btn btn-info btn-lg btn-group-justified" href="<?php echo base_url('kaizen/preshift_check/facility/'.$facility['id']); ?>">
		<?php echo $facility['facility_name']; ?>
	</a>
	<br>
	<?php endforeach; ?>
</div>