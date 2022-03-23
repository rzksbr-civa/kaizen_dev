<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	var table = $('#carrier-status-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
	} );
	
	function update_status(carrier_code, track_number, mwe_status, mwe_expected_delivery_date, row_index) {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_carrier_tracking_status'); ?>', { 
			carrier_code: carrier_code,
			track_number: track_number,
			mwe_status: mwe_status,
			mwe_expected_delivery_date: mwe_expected_delivery_date}, function(result) {
			if(result.success) {
				//$('#status-'+track_number).html(result.status);
				//$('#eta-'+track_number).html(result.eta);
				//$('#actual-delivery-date-'+track_number).html(result.actual_delivery_date);
				
				table.cell({row: row_index, column: 4}).data(result.status);
				table.cell({row: row_index, column: 6}).data(result.actual_delivery_date);
				table.cell({row: row_index, column: 9}).data(result.last_checked_at);
				table.cell({row: row_index, column: 10}).data(result.pod);
				
				if(result.color.length > 0) {
					$('#tr-'+track_number).removeClass().addClass('row-color-'+result.color);
				}
			}
		}, "json" );
	}
	
	var need_updated_package_ids = <?php echo json_encode($need_updated_package_ids); ?>;
	var row_index_by_package_id = <?php echo json_encode($row_index_by_package_id); ?>;
	
	bulk_update_status(need_updated_package_ids);
	
	function bulk_update_status(need_updated_package_ids) {
		var current_batch_package_ids = [];

		var need_updated_package_count = need_updated_package_ids.length;
		var this_batch_count = 15;
		if(need_updated_package_count < this_batch_count) {
			this_batch_count = need_updated_package_count;
		}
		for(var i=0; i<this_batch_count; i++) {
			current_batch_package_ids.push(need_updated_package_ids.pop());
		}
		
		$.post('<?php echo base_url(PROJECT_CODE.'/api/bulk_update_carrier_tracking_status'); ?>', { 
			package_ids: current_batch_package_ids}, function(result) {
			if(result.success) {			
				for(var i=0; i<result.updated_packages.length; i++) {
					var updated_package = result.updated_packages[i];

					var row_index = row_index_by_package_id[updated_package.package_id];
					table.cell({row: row_index, column: 4}).data(updated_package.status);
					if(updated_package.actual_delivery_date != null) {
						table.cell({row: row_index, column: 6}).data(updated_package.actual_delivery_date.substr(0,10));
					}
					if(updated_package.is_delivered) {
						table.cell({row: row_index, column: 7}).data('Yes');
					}
					table.cell({row: row_index, column: 8}).data(updated_package.late_days);
					table.cell({row: row_index, column: 9}).data(updated_package.last_checked_at);
					
					if(updated_package.color != null) {
						$('#tr-'+updated_package.track_number).removeClass().addClass('row-color-'+updated_package.color);
					}
					
					if(updated_package.actual_delivery_date != null && $('#carrier-code-'+row_index).html() == 'fedex') {
						table.cell({row: row_index, column: 10}).data('<a href="https://www.fedex.com/trackingCal/retrievePDF.jsp?accountNbr=&anon=true&appType=&destCountry=&locale=en_US&shipDate=&trackingCarrier=FDXG&trackingNumber='+$('#track-number-'+row_index).html()+'&type=SPOD" target="_blank"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a>');
					}
				}
				
				if(need_updated_package_count == this_batch_count) {
					// Update done
				}
				else {
					bulk_update_status(need_updated_package_ids);
				}
			}
		}, "json" );
	}
</script>