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

</script>

<?php echo $js_carrier_status_dashboard_for_packages_table_html; ?>

<?php endif; ?>