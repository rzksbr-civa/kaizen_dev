<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.apexcharts-canvas {
		background-image: linear-gradient(to bottom, #000000, #00003F, #000000);
	}
</style>

<?php
	foreach($department_list as $department_code => $department_name) :
		if($department_code == 'unknown') continue;
?>
	<h3><?php echo $department_name; ?></h3>
	<div class="trending-chart" id="<?php echo $department_code; ?>-trending-chart"></div>
<?php
	endforeach;
?>