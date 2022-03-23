<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	#tf-total-table td {
		padding: 8px 30px;
	}
	
	#tf-total-table tr.magnify td {
		font-size: 18px;
	}
	
	#tf-total-table tr.strong td {
		font-weight: bold;
	}
	
	.amount-column {
		text-align: right;
	}
</style>

<div class="row">
	<div class="col-md-12">
		<table class="table table-striped table-responsive" id="tf-total-table">
			<tbody>
				<?php
					foreach($rows as $row) {
						$row_class = null;
						if(isset($row['class'])) {
							if(is_array($row['class'])) {
								$row_class = implode(' ', $row['class']);
							}
							else {
								$row_class = $row['class'];
							}
						}
						if(!empty($row_class)) {
							$row_class = ' class="'.$row_class.'"';
						}
						
						echo '<tr'.$row_class.'>';
						echo '<td id="tf-'.$row['name'].'-label">'.$row['label'].'</td>';
						echo '<td class="amount-column" id="tf-'.$row['name'].'-value">'.$row['value'].'</td>';
						echo '</tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</div>