<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">
	.report-card-value {
		font-weight: bold;
		font-size: 24px;
		padding: 5px 15px;
	}
</style>

<?php if($report_title <> '') : ?>
<div class="page-header">
	<h1><?php echo $report_title; ?></h1>
	<?php if(isset($report_subtitle)) echo '<h4>'.$report_subtitle.'</h4>'; ?>
</div>
<?php endif; ?>

<div class="rows">

<?php
	if(!empty($report_result['tables']) || !empty($report_result['charts'])) :
		echo '<div class="col-md-9">';
		
		if(!empty($report_result['charts'])) {
			foreach($report_result['charts'] as $chart) {
				echo '<div id="report-chart-'.$chart['chart_name'].'"></div>';
			}
		}
		
		if(!empty($report_result['tables'])) :
			
			foreach($report_result['tables'] as $table) :
				if(isset($table['table_title'])) {
					echo '<h2>'.$table['table_title'].'</h2>';
				}
?>
		<div class="report-table-area invisible" id="<?php echo $table['table_name'] ?>-area">
			<table class="table table-striped datatabled-report table-bordered" style="width:90%" id="<?php echo $table['table_name']; ?>" cellspacing="0">
				<thead>
					<tr>
						<?php
							foreach($table['table_headers'] as $table_header) {
								echo '<th>'.$table_header.'</th>';
							}
						?>
					</tr>
				</thead>
				<tbody>
					
				</tbody>
				<tfoot>
					<tr>
						<?php
							foreach($table['table_footers'] as $table_footer) {
								if($table_footer <> '') {
									echo '<th class="searchable">'.$table_footer.'</th>';
								}
								else {
									echo '<th></th>';
								}
							}
						?>
					</tr>
				</tfoot>
			</table>
		</div>
<?php 
	endforeach;
	echo '<hr /></div>';
	endif; endif;
?>

<?php 
	if(isset($report_result['cards'])) {
		echo '<div class="col-md-3">';
		foreach($report_result['cards'] as $card) {
			echo '
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">'.$card['title'].'</h3>
					</div>
					<div class="panel-body report-card-value">
						'.$card['value'].'
					</div>
				</div>
			';
		}
		echo '</div>';
	}
?>

</div>