<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('#input-report-type').change(function() {
		var reportType = $(this).val();
		var currentPeriodicityValue = $('#input-periodicity').val();
		$('#input-periodicity')[0].selectize.setValue(null, true);
		$('#input-periodicity')[0].selectize.clearOptions();

		if(reportType == 'revenue_summary' || reportType == 'wages') {
			$('#periodicity-filter-area').hide();
		}
		else {
			$('#periodicity-filter-area').fadeIn('slow');
		}
		
		if(reportType == 'wages') {
			$('#department-filter-area').fadeIn('slow');
		}
		else {
			$('#department-filter-area').hide();
		}
		
		if(reportType != 'trending_graph') {
			$('#input-periodicity')[0].selectize.addOption({value:'hourly',text:'Hourly'});
		}
		else {
			if(currentPeriodicityValue == 'hourly') currentPeriodicityValue = 'daily';
		}
		
		$('#input-periodicity')[0].selectize.addOption({value:'daily',text:'Daily'});
		$('#input-periodicity')[0].selectize.addOption({value:'weekly',text:'Weekly'});
		$('#input-periodicity')[0].selectize.addOption({value:'monthly',text:'Monthly'});
		$('#input-periodicity')[0].selectize.addOption({value:'yearly',text:'Yearly'});
		$('#input-periodicity')[0].selectize.setValue(currentPeriodicityValue, true);
	});
</script>

<?php if($generate) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	var revenue_table = $('.revenue-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
	} );
</script>

<?php endif; ?>