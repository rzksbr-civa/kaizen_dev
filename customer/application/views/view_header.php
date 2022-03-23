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
		<link href="<?php echo base_url('assets/bootstrap/themes/'.BOOTSTRAP_THEME.'/css/custom-style.css'); ?>" rel="stylesheet">
	<?php else : ?>
		<link href="<?php echo base_url('assets/bootstrap/3.3.7/css/bootstrap.min.css'); ?>" rel="stylesheet">
	<?php endif; ?>

	<!-- Selectize CSS -->
	<link href="<?php echo base_url('assets/selectize/selectize.css'); ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/selectize/selectize.bootstrap3.css'); ?>" rel="stylesheet">
	
	<!-- Custom CSS -->
	<link href="<?php echo base_url('assets/chchdb/style.css'); ?>" rel="stylesheet">
	
	<?php 
		if(file_exists(str_replace('application','assets',APPPATH).'data/'.PROJECT_CODE.'/css/style.css')) {
			?>
			<link href="<?php echo base_url('assets/data/'.PROJECT_CODE.'/css/style.css'); ?>" rel="stylesheet">
			<?php
		}
	?>
	
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
			<a class="navbar-brand" href="<?php echo base_url(PROJECT_CODE . '/home'); ?>">
				<?php echo PROJECT_NAME; ?>
			</a>
		</div>
		
		<div class="collapse navbar-collapse" id="<?php echo PROJECT_CODE . '-menu'; ?>">
			<?php
				// Generate header menu
				
				$header_menu = config_item('header_menu');
				
				// Generate left side menu
				if(!empty($header_menu)) {
					echo '<ul class="nav navbar-nav navbar-left">';
					
					// Generate header menu items using helpers/chchdb_header_helper.php
					generate_header_menu_item($header_menu, $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role'));
					
					echo '</ul>';
				}
			?>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_name'); ?> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo base_url('user/settings'); ?>"><?php echo ucwords(lang('title__change_password')); ?></a></li>
					</ul>
				</li>
				<li><a href="<?php echo base_url('logout'); ?>"><?php echo ucwords(lang('word__logout')); ?></a></li>
			</ul>
		</div>
	</div>
</nav>

<body>
	<div class="container">