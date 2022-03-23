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
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_status_board_data'); ?>', { 
			data: $('#form-status-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#status-board-visualization-area').html(result.status_board_visualization_html);
			}
		}, "json" );
    }
</script>

<?php endif; ?>