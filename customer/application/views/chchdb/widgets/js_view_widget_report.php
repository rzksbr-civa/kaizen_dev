<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<script>
	$(document).ready(function() {
		$('.btn-show-report').click(function() {
			var report_options = $('#form_report_options').serializeArray();
			var parameters = [];
			for(var i=0; i<report_options.length; i++) {
				parameters[i] = report_options[i].name + '=' + report_options[i].value;
			}
			location.replace(window.location.href.split('?')[0]+'?'+parameters.join('&'));
		});
		
		<?php 
			if(isset($tables)) :
			foreach($tables as $table) : 
				$table_name = $table['table_name'];
		?>
		
		$('#<?php echo $table_name; ?> tfoot th.searchable').each( function () {
			var title = $(this).text();
			$(this).html( '<input class="form-control column-search-box" type="text" placeholder="<?php echo ucwords(lang('word__search')); ?> '+title+'" autocomplete="off" />' );
		} );
		
		var <?php echo $table_name ?> = $('.datatabled-report').DataTable( {
			dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
			ajax: '<?php echo $table['ajax_url']; ?>',
			deferRender: true,
			buttons: [
				{
					extend: 'copy',
					text: 'Copy',
					exportOptions: {columns: ':visible'}
				},
				{
					extend: 'excel',
					text: 'Excel',
					exportOptions: {columns: ':visible'}
				},
				{
					extend: 'excel',
					text: 'Excel (Filtered)',
					exportOptions: {columns: ':visible :not(:first-child)', modifier: {search: 'applied'}}
				},
				{
					extend: 'excel',
					text: 'Excel (All Cols)'
				},
				{
					extend: 'print',
					text: 'Print',
					exportOptions: {columns: ':visible'}
				},
				'colvis'
			],
			columnDefs: [<?php echo implode(', ', $table['column_defs']); ?>,
				{'targets':'_all',
					'render': function(data, type, row, meta) {
						if(data===null || typeof data != 'string') return data;
						var str = data.replace('{U}', '<?php echo base_url(); ?>');
						return str;
					}
				}],
			/*fixedHeader: {
				headerOffset: 50
			},*/
			select: true,
			colResize: {
				"tableWidthFixed": false
			},
			aaSorting: []
		} );
		
		// Show the table after the javascript is loaded
		$('#<?php echo $table_name; ?>-area').removeClass('invisible');
		
		// Apply the search
		<?php echo $table_name; ?>.columns().every( function () {
			var that = this;

			$( 'input', this.footer() ).on( 'keyup change', function () {
				if ( that.search() !== this.value ) {
					if(this.value === '""') {
						that
							.search( '^$', true, false )
							.draw();
					}
					else if(this.value === '<>""') {
						that
							.search( '[^\s]', true, false )
							.draw();
					}
					else if(this.value.charAt(0) === '[' && this.value.charAt(this.value.length - 1) === ']') {
						that
							.search('^' + this.value.substring(1, this.value.length-1) + '$', true, false, true)
							.draw();
					}
					else {
						that
							.search( this.value, true, true )
							.draw();
					}
				}
			} );
		} );
		
		$('#<?php echo $table_name; ?> tfoot tr').insertAfter($('#<?php echo $table_name; ?> thead tr'));
		<?php endforeach; endif; ?>
		
		// Other format (e.g. currency) column sorting
		jQuery.extend(jQuery.fn.dataTableExt.oSort, {
			"currency-pre": function (a) {
				a = (a == null) ? '0' : a;
				a = (a === "") ? 0 : ((a === "-") ? 0 : a.replace(/[^\d\-\<?php echo NUMBER_DECIMAL_POINT; ?>]/g, ""));
				return parseFloat(a);
			},
			"currency-asc": function (a, b) {
				return a - b;
			},
			"currency-desc": function (a, b) {
				return b - a;
			}
		});
		
		/*
		To fix fixedHeader when scrolled
		$('.table-container').scroll(function() {
			var leftOffset = $('.datatabled-report').offset().left;
			$('.fixedHeader-floating').css('left', leftOffset);
		});*/
		
		$('body').on('click', 'li.paginate_button', function() {
			if($(this).attr('id').endsWith('ellipsis')) {
				var page = prompt('What page do you want to go?');
				
				if(page != null) {
					page = parseInt(page);
					
					if(!Number.isInteger(page)) {
						alert('Page number must be an integer.');
					}
					else {
						if(page < 1) {
							page = 1;
						}
						else if(page > table.page.info().pages) {
							page = table.page.info().pages;
						}
						
						table.page(page-1).draw('page');
					}
				}
			}
		});
		
		$('body').on('change', '#input_report_options_report_type', function() {
			var reportType = $('#input_report_options_report_type').val();
			$('#report-options-parameters-area').slideUp('fast');
			
			$.post('<?php echo base_url('api/get_report_options_parameter'); ?>', { 
				report_type: reportType}, function(result) {
					$('#report-options-parameters-area').html(result.render).slideDown('fast');
					for(var i=0; i<result.selectized_elements.length; i++) {
						$('#'+result.selectized_elements[i]).selectize();
					}
			},'json');
		});
		
		// Chart
		<?php
			if(!empty($charts)) :
				foreach($charts as $chart) :
					echo 'var '.$chart['chart_name'].'_options = ' . json_encode($chart['chart_options']) .';';
					
					if(!empty($chart['formatter'])) {
						foreach($chart['formatter'] as $formatter) {
							$format_formula = 'val';
							if($formatter == 'currency_idr') {
								$format_formula = '"Rp " + formatThousandSeparator(val)';
							}
							echo $chart['chart_name'].'_options.dataLabels.formatter = function(val, opts) { return '.$format_formula.' };';
						}
					}
					
					?>
					var <?php echo $chart['chart_name']; ?> = new ApexCharts(
						document.querySelector("#report-chart-<?php echo $chart['chart_name']; ?>"),
						<?php echo $chart['chart_name']; ?>_options
					);

					<?php echo $chart['chart_name']; ?>.render();
					<?php
				endforeach;
			endif;
		?>	
	});
	
	function formatThousandSeparator(value) {
		value += '';
		var decimals = value.split('<?php echo NUMBER_DECIMAL_POINT; ?>');
		var decimal = '';
		if(decimals.length > 1) {
			decimal = '<?php echo NUMBER_DECIMAL_POINT; ?>' + decimals[1];
		}
		value = decimals[0];
		return value
			.replace(/\D/g, "")
			.replace(/\B(?=(\d{3})+(?!\d))/g, "<?php echo NUMBER_THOUSAND_SEPARATOR; ?>") + decimal
		;
	}
</script>

