<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<?php 
	$i = 0;
	foreach($manifest_data as $manifest) : ?>
	
	<?php if($i % 6 == 0) echo '<div class="row">'; ?>
	
	<div class="col-lg-2 col-md-3 col-xs-6">
		<div class="panel panel-default">
			<div class="panel-heading manifest-color-<?php echo $manifest['color']; ?>">
				<div class="pull-right">
				<?php
					switch($manifest['status']) {
						case 'open':
							echo '<span class="label label-success">OPEN</span>';
							break;
						case 'sealed':
							echo '<span class="label label-default">SEALED</span>';
							break;
						case 'loaded':
							echo '<span class="label label-primary">LOADED</span>';
							break;
					}
				?>
				</div>
				<div class="manifest-number">#<?php echo $manifest['increment_id']; ?></div>
				<div class="manifest-info"><?php echo date('Y-m-d H:i', strtotime($manifest['local_created_time'])); ?> <br> Loc <?php echo $manifest['load_location']; ?> &middot; <?php echo ucwords($manifest['container_type_name']); ?> <br> <?php echo $manifest['carrier_name']; ?></div>
				<div class="manifest-weight"><?php echo number_format($manifest['weight'], 2); ?> lbs <br><?php echo $manifest['packages']; ?> packages</div>
				<div class="blow-out"><?php echo number_format($manifest['blow_out'], 1); ?>%</div>
			</div>
		</div>
	</div>
	
	<?php if($i % 6 == 5) { echo '</div>'; } $i++; ?>
	
<?php endforeach; ?>


<?php endif; ?>