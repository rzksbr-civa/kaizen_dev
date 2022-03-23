<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<script>
	window.setInterval('refresh()', 60000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_live_drops_board_data'); ?>', { 
			data: $('#form-live-drops-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#live-drops-board-visualization-area').html(result.live_drops_board_visualization_html);
				$('#js-live-drops-board-visualization').html(result.js_live_drops_board_visualization_html);
			}
		}, "json" );
    }
</script>

<div id="js-live-drops-board-visualization">
<?php echo $js_live_drops_board_visualization_html; ?>
</div>

<?php endif; ?>