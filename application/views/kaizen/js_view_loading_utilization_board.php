<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('body').on('click', '#btn-auto-refresh', function() {
		toggleAutoRefreshButton();
	});
	
	function toggleAutoRefreshButton() {
		var current_state = $('#btn-auto-refresh').data('state');
		
		if(current_state == 'on') {
			turnOffAutoRefresh();
		}
		else {
			turnOnAutoRefresh();
		}
	}
	
	function turnOffAutoRefresh() {
		$('#btn-auto-refresh').data('state', 'off');
		$('#btn-auto-refresh').removeClass('btn-success').addClass('btn-default');
		$('#btn-auto-refresh').text('Auto Refresh Off');
	}
	
	function turnOnAutoRefresh() {
		$('#btn-auto-refresh').data('state', 'on');
		$('#btn-auto-refresh').removeClass('btn-default').addClass('btn-success');
		$('#btn-auto-refresh').text('Auto Refresh On');
	}
</script>

<?php if($generate) : ?>

<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	
	window.setInterval('refresh()', 10000);

    function refresh() {
		if($('#btn-auto-refresh').data('state') == 'on') {
			$.post('<?php echo base_url(PROJECT_CODE.'/api/get_loading_utilization_board_data'); ?>', { 
				data: $('#form-loading-utilization-board-filter').serializeArray()}, function(result) {
				if(result.success && result.page_version > $('#input-page-version').val()) {
					$('#page-last-updated-text').html(result.page_last_updated);
					$('#loading-utilization-board-visualization-area').html(result.loading_utilization_board_visualization_html);
					$('#js-loading-utilization-board-visualization-area').html(result.js_loading_utilization_board_visualization_html);
				}
			}, "json" );
		}
    }
</script>

<div id="js-loading-utilization-board-visualization-area">
	<?php echo $js_loading_utilization_board_visualization_html; ?>
</div>

<?php endif; ?>