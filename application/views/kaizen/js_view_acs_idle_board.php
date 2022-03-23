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
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_acs_idle_board_data'); ?>', { 
			data: $('#form-acs-idle-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#acs-idle-board-visualization-area').html(result.acs_idle_board_visualization_html);
			}
		}, "json" );
    }
	
	$('body').on('click', '.btn-change-idle-order-state', function() {
		var order_no = $(this).attr('order_no');
		$.post('<?php echo base_url(PROJECT_CODE.'/api/change_idle_order_state'); ?>', { 
			order_no: order_no,
			action: $(this).attr('action')}, function(result) {
			if(result.success) {
				$('.td-action-'+order_no).html(result.td_action_html);
			}
		}, "json" );
	});
</script>

<?php endif; ?>