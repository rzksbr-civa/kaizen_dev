<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- jQuery -->
<script src="<?php echo base_url('assets/jquery/3.3.1/jquery.min.js'); ?>" type="text/javascript"></script>

 <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<!-- Bootstrap Javascript -->
<script src="<?php echo base_url('assets/bootstrap/3.3.7/js/bootstrap.min.js'); ?>"></script>

<!-- Selectize JS -->
<script src="<?php echo base_url('assets/selectize/selectize.min.js'); ?>"></script>

<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	});

	$('.selectized').selectize();
</script>

<?php
	// Load other JS if any
	if(isset($js)) {
		echo $js;
	}
?>

</div> <!-- #container -->
</body>
</html>