<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
	
	$('#panel-settings-collapse-btn').click( function() {
		var state = $(this).data('state');
		
		if(state == 'up') {
			$(this).data('state','down');
			$(this).removeClass('glyphicon-chevron-up');
			$(this).addClass('glyphicon-chevron-down');
		}
		else {
			$(this).data('state','up');
			$(this).removeClass('glyphicon-chevron-down');
			$(this).addClass('glyphicon-chevron-up');
		}
	});
</script>

<?php if($generate): ?>
	<script>
		window.setInterval('refresh()', 60000);
		
		function refresh() {
			var scroll = $(window).scrollTop();
			$.post('<?php echo base_url(PROJECT_CODE.'/api/get_kaizen_manager_data'); ?>', { 
				data: $('#form-kaizen-manager-filter').serializeArray()}, function(result) {
				if(result.success && result.page_version > $('#input-page-version').val()) {
					$('#page-last-updated-text').html(result.page_last_updated);
					$('#kaizen-manager-visualization').html(result.kaizen_manager_visualization_html);
					$('#js-kaizen-manager-visualization').html(result.js_kaizen_manager_visualization_html);
					$(window).scrollTop(scroll);
				}
			}, "json" );
		}
	</script>
	
	<div id="js-kaizen-manager-visualization">
		<?php echo isset($js_kaizen_manager_visualization_html) ? $js_kaizen_manager_visualization_html : null; ?>
	</div>
<?php endif; ?>