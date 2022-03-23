<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<html>
<head>
	<title>Update: <?php echo $update_function_name; ?></title>
</head>

<body>

<div id="result">Running update #1: <?php echo $update_function_name; ?></div>

<!-- jQuery -->
<script src="<?php echo base_url('assets/jquery/3.3.1/jquery.min.js'); ?>" type="text/javascript"></script>

 <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<script>
	var increment = 1;
	update();
	
	function update() {
		$.ajax({
			url: '<?php echo base_url(PROJECT_CODE.'/update_public/'.$update_function_name); ?>',
				success: function(data) {
					increment++;
					$('#result').html('Running update #'+increment+': <?php echo $update_function_name; ?>');
					update();
			}
		});
	}
</script>

</body>
</html>