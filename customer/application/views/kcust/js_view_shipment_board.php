<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<?php if($generate) : ?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
	
	window.setInterval('refresh()', 60000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_shipment_board_data'); ?>', { 
			data: $('#form-shipment-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#shipment-board-visualization-area').html(result.shipment_board_visualization_html);
				$('#js-shipment-board-visualization-area').html(result.js_shipment_board_visualization_html);
			}
		}, "json" );
    }
</script>

<div id="js-shipment-board-visualization-area">
	<?php echo $js_shipment_board_visualization_html; ?>
</div>

<?php endif; ?>