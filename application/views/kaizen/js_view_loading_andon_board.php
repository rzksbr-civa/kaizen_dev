<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	
	window.setInterval('refresh()', 60000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_loading_andon_board_data'); ?>', { 
			data: $('#form-loading-andon-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#loading-andon-board-visualization-area').html(result.loading_andon_board_visualization_html);
			}
		}, "json" );
    }
	
	var currentIndex = 0;
	setInterval( function() {
		if(currentIndex == 0) {
			$('.manifest-color-red').css('background-color', '#C21807');
			currentIndex = 1;
		}
		else {
			$('.manifest-color-red').css('background-color', '#3F0000');
			currentIndex = 0;
		}
	}, 600);
</script>

<?php endif; ?>