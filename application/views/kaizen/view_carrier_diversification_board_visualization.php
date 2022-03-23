<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>
	<div class="table-area">
	<table class="table table-bordered datatabled-entity" id="table-carrier-diversification-board">
		<thead>
			<?php if(!empty($periodicity)): ?>
				<th>Period</th>
			<?php endif; ?>
			
			<?php foreach($carriers as $current_data): ?>
				<th><?php echo $current_data; ?></th>
				<th>% <?php echo $current_data; ?></th>
			<?php endforeach; ?>
		
			<th>Total</th>
		</thead>
		
		<?php if(!empty($periodicity)):
				foreach($table_data as $current_period => $current_data): ?>
					<tr><td><?php echo $current_period; ?></td>
					
					<?php foreach($carriers as $current_carrier): ?>
						<td><?php echo $current_data[$current_carrier]; ?></td>
						<td><?php echo number_format($current_data[$current_carrier]/$current_data['total']*100,2).'%'; ?></td>
					<?php endforeach; ?>
					
					<td><?php echo $current_data['total']; ?></td></tr>
		<?php endforeach; ?>
		<?php else: ?>
			<tr>
			<?php foreach($carriers as $current_carrier): ?>
				<td><?php echo $table_data[$current_carrier]; ?></td>
				<td><?php echo number_format($table_data[$current_carrier]/$table_data['total']*100,2).'%'; ?></td>
			<?php endforeach; ?>
			
			<td><?php echo $table_data['total']; ?></td></tr>
		<?php endif; ?>
	</table>
	</div>
<?php endif; ?>