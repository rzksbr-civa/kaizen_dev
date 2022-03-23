<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$(document).ready(function(){
		$('.nav-tabs a').click(function (e) {
			var tabName = $(this).attr('aria-controls');
			changeTab(tabName);
		});
	});

	function changeTab(tabName) {
		var pageURL = "<?php echo $page_url; ?>";
		window.location.replace(pageURL + '/' + tabName);
	}
</script>