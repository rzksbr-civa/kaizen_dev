<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
</script>

<?php if($generate) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	var replenishment_release_board_table = $('.replenishment-release-board-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
		data: <?php echo json_encode($replenishment_release_board_2d_data); ?>,
		deferRender: true,
		columnDefs: [
			{ targets: [1,2,3,4,5,6,7,9,10,11,12,13,14,15], className:'dt-right' },
			{ targets: [8,16], className:'dt-center' }
		],
		aaSorting: [],
		rowCallback: function( row, data, index ) {
			if(data[16] == 'Yes') {
				if(data[8] == "T1") {
					$('td', row).css('background-color', '#C52428'); // red
				}
				else if(data[8] == "T2" || data[8] == "T3") {
					$('td', row).css('background-color', 'orange');
					$('td', row).css('color', 'black');
				}
				else if(data[8] == "T4" || data[8] == "T5") {
					$('td', row).css('background-color', 'yellow');
					$('td', row).css('color', 'black');
				}
			}
		}
	} );
</script>

<?php endif; ?>