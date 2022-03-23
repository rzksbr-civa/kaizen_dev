<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	table.dataTable td.dataTables_empty {
		text-align: left;
		padding-left: 20px;
	}
	
	span.trigger-manipulate-data {
		cursor: pointer;
	}
	
	table.datatabled-entity {
		width: 90%;
	}
</style>

<div class="table-area invisible">
	<table class="table table-striped datatabled-entity table-bordered" id="table-<?php echo $entity_name; ?>" cellspacing="0">
		<thead>
			<tr>
				<?php
					foreach($table_headers as $table_header) {
						echo '<th>'.$table_header.'</th>';
					}
				?>
			</tr>
		</thead>
		<tbody>
			
		</tbody>
		<tfoot>
			<tr>
				<?php
					$i=1;
					foreach($table_footers as $table_footer) {
						if(!empty($table_footer['label'])) {
							echo '<th class="searchable" col_no="'.$i++.'" data_format="'.$table_footer['format'].'">'.$table_footer['label'].'</th>';
						}
						else {
							echo '<th></th>';
						}
					}
				?>
			</tr>
		</tfoot>
	</table>
</div>