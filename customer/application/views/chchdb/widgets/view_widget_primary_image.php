<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="col-md-6">
	<table class="table table-bordered">
	<?php
		for($i=1; $i<=5; $i++) {
			if(file_exists(str_replace('application', 'assets', APPPATH).'/data/erms/img/tmp/'.$barcode.'-0'.$i.'.jpg')) :
	?>
		<tr>
			<td>
				<a href="<?php echo base_url('assets/data/erms/img/tmp/'.$barcode.'-0'.$i.'.jpg'); ?>" target="_blank">
					<img src="<?php echo base_url('assets/data/erms/img/tmp/'.$barcode.'-0'.$i.'.jpg'); ?>" width="100%">
				</a>
			</td>
		</tr>
	<?php
			endif;
		}
	?>
	</table>
</div>

