<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title><?php echo $page_title; ?></title>

	<!-- Bootstrap CSS -->
	<?php if(defined('BOOTSTRAP_THEME')) : ?>
		<link href="<?php echo base_url('assets/bootstrap/themes/'.BOOTSTRAP_THEME.'/css/bootstrap.min.css'); ?>" rel="stylesheet">
	<?php else : ?>
		<link href="<?php echo base_url('assets/bootstrap/3.3.7/css/bootstrap.min.css'); ?>" rel="stylesheet">
		<link href="<?php echo base_url('assets/bootstrap/3.3.7/css/custom-style.css'); ?>" rel="stylesheet">
	<?php endif; ?>

	<!-- Datatables CSS -->
	<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

	<!-- Selectize CSS -->
	<link href="<?php echo base_url('assets/selectize/selectize.css'); ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/selectize/selectize.bootstrap3.css'); ?>" rel="stylesheet">
	
	<!-- Custom CSS -->
	<link href="<?php echo base_url('assets/chchdb/style.css'); ?>" rel="stylesheet">
	
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/data/'.PROJECT_CODE.'/app/img/favicon.png'); ?>" />
		
	<link href="<?php echo base_url('assets/fonts/Mukta/font.css'); ?>" rel="stylesheet">
</head>

<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="<?php echo '#' . PROJECT_CODE . '-menu'; ?>">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo base_url(); ?>">
				<?php echo PROJECT_NAME; ?>
			</a>
		</div>
	</div>
</nav>

<body>
	<div class="container">
		<div class="col-md-4">
			<div class="page-header">
				<img src="<?php echo base_url('assets/data/kcust/app/redstag-logo.png'); ?>" width="200">
				<!-- <h3><?php echo ucwords(lang('word__login')); ?></h3> -->
			</div>
			<div id="invalidLoginAlert"></div>
			<form>
				<div class="form-group" id="form-group-username">
					<label class="control-label" for="inputUserName"><?php echo ucfirst(lang('word__username')); ?></label>
					<input type="text" class="form-control" id="inputUserName" required>
					<span id="help-block-username" class="help-block">
				</div>
				<div class="form-group" id="form-group-password">
					<label class="control-label" for="inputUserPassword"><?php echo ucfirst(lang('word__password')); ?></label>
					<input type="password" class="form-control" id="inputUserPassword" required>
					<span id="help-block-password" class="help-block">
				</div>
				<button type="button" class="btn btn-primary" id="btn-do-login"><?php echo ucfirst(lang('word__login')); ?></button>
			</form>
		</div>
	
		<!-- jQuery -->
		<script src="<?php echo base_url('assets/jquery/3.3.1/jquery.min.js'); ?>" type="text/javascript"></script>

		 <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->

		<!-- Bootstrap Javascript -->
		<script src="<?php echo base_url('assets/bootstrap/3.3.7/js/bootstrap.min.js'); ?>"></script>
		
		<script>	
			$(document).ready(function() {
				$('#btn-do-login').on('click', function() {
					login();
				});
				
				$('#inputUserName, #inputUserPassword').keyup(function(e) {
					var key = e.keyCode;
					if(key == 13) login();
				});
			});
			
			function login() {
				var input_valid = true;
				var username = $('#inputUserName').val();
				var password = $('#inputUserPassword').val();
				
				resetFieldsState();
				
				if(username == '') {
					input_valid = false;
					$('#form-group-username').addClass('has-error');
					$('#help-block-username').html('<?php echo ucfirst(sprintf(lang('error_message__please_fill_in_the_x'), lang('word__username'))); ?>');
				}
				
				if(password == '') {
					input_valid = false;
					$('#form-group-password').addClass('has-error');
					$('#help-block-password').html('<?php echo ucfirst(sprintf(lang('error_message__please_fill_in_the_x'), lang('word__password'))); ?>');
				}
				
				if(input_valid) {
					$.ajax({
						url : '<?php echo base_url('login/process_login'); ?>',
						type : 'POST',
						dataType : 'json',
						data : {
							'username': username,
							'password': password},
						success : function(data) {
							if(data.login_success) {
								window.location = '<?php echo base_url(PROJECT_CODE . '/home'); ?>';
							}
							else {
								$('#inputUserPassword').val('');
								showInvalidLoginAlert(data.error_message);
							}
						},
						error : function(data) {
							alert('<?php echo lang('error_message__check_internet_connection') ?>');
						}
					});
				}
			}
			
			function resetFieldsState() {
				$('.form-group').removeClass('has-error');
				$('.help-block').html('');
				$('#invalidLoginAlert').html('');
			}
			
			function showInvalidLoginAlert(errorMessage) {
				$('#invalidLoginAlert').html('<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> '+ errorMessage +'</div>');
			}
		</script>
	</div> <!-- #container -->
</body>
</html>