<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
</script>

<?php if($generate) : ?>

<script>
	window.setInterval('refresh()', 20000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_idle_picking_batch_board_data'); ?>', { 
			data: $('#form-idle-picking-batch-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#idle-picking-batch-board-visualization-area').html(result.idle_picking_batch_board_visualization_html);
			}
		}, "json" );
    }
</script>

<?php endif; ?>