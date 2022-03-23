<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if(isset($ajax_url)) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	var table;
	
	$(document).ready(function() {
		// Column search box
		$('.datatabled-entity tfoot th.searchable').each( function () {
			var title = $(this).text();
			$(this).html( '<input class="form-control column-search-box" id="datatable-search-col-'+$(this).attr('col_no')+'" data_format="'+$(this).attr('data_format')+'" type="text" placeholder="<?php echo ucwords(lang('word__search')); ?> '+title+'" autocomplete="off" />' );
		} );
		
		table = $('.datatabled-entity').DataTable( {
			dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"#after-table-elements"><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
			ajax: '<?php echo $ajax_url; ?>',
			rowId: 'row_id',
			deferRender: true,
			buttons: [
				{
					extend: 'copy',
					text: 'Copy',
					exportOptions: {columns: ':visible :not(:first-child)'}
				},
				{
					extend: 'excel',
					text: 'Excel',
					exportOptions: {columns: ':visible :not(:first-child)'}
				},
				{
					extend: 'excel',
					text: 'Excel (Filtered)',
					exportOptions: {columns: ':visible :not(:first-child)', modifier: {search: 'applied'}}
				},
				{
					extend: 'excel',
					text: 'Excel (All Cols)',
					exportOptions: {columns: ':not(:first-child)'}
				},
				{
					extend: 'print',
					text: 'Print',
					exportOptions: {columns: ':visible :not(:first-child)'}
				},
				'colvis'
			],
			columnDefs: [<?php echo implode(', ', $column_defs); ?>,
				{'targets':'_all',
					'render': function(data, type, row, meta) {
						if(data===null || typeof data != 'string') return data;
						var str = data.replace('{U}', '<?php echo base_url(); ?>');
						return str;
					}
				}],
			order: [<?php echo implode(', ', $data_order); ?>],
			<?php
				// Don't use fixed header on mobile as it's still unstable.
				$this->CI =& get_instance();
				$this->CI->load->library('user_agent');
				if(!$this->CI->agent->is_mobile()) :
			?>
			/*fixedHeader: {
				headerOffset: 50
			},*/
			<?php endif; ?>
			select: true,
			colResize: {
				"tableWidthFixed": false
			},
			autoWidth: false,
			//pagingType: 'input',
			drawCallback: function( settings ) {
				$('.trigger-manipulate-data').attr('parent_entity', '<?php echo isset($parent_entity_name) ? $parent_entity_name : ''; ?>');
			}
		} );
		
		// Show the table after the javascript is loaded
		$('.table-area').removeClass('invisible');
		
		// Apply the search
		table.columns().every( function () {
			var that = this;

			$( 'input', this.footer() ).on( 'keyup change', delay(function () {
				datatableSearch(that, this.value, $(this).attr('data_format'));
			}, 500) );
		} );
		
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
		
		
		// To fix fixedHeader when scrolled
		$('.table-container').scroll(function() {
			var leftOffset = $('.datatabled-entity').offset().left;
			$('.fixedHeader-floating').css('left', leftOffset);
			$('.fixedHeader-locked').css('left', leftOffset);
			$('.column-search-box').blur();
		});
		$(window).scroll(function() {
			var leftOffset = $('.datatabled-entity').offset().left;
			$('.fixedHeader-floating').css('left', leftOffset);
			$('.fixedHeader-locked').css('left', leftOffset);
			$('.column-search-box').blur();
		});
		
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
		
		$('.datatabled-entity tfoot tr').insertAfter($('.datatabled-entity thead tr'));
		
		$('.btn-default').click( function() {
			$('.datatabled-entity').css('width',1000);
		});
		
		$('.datatabled-entity').on( 'column-visibility.dt', function ( e, settings, column, state ) {
			//table.fixedHeader.columns.recalc().adjust();
		} );
		
		function delay(callback, ms) {
			var timer = 0;
				return function() {
					var context = this, args = arguments;
					clearTimeout(timer);
					timer = setTimeout(function () {
					callback.apply(context, args);
				}, ms || 0);
			};
		}
		
		<?php if(isset($after_table_widgets_exists) && $after_table_widgets_exists) : ?>
		$.post('<?php echo base_url('api/get_after_table_widgets'); ?>', {
			parent_entity_name: '<?php echo $parent_entity_name; ?>',
			entity_name: '<?php echo $entity_name; ?>',
			tab_name: '<?php echo $tab_name; ?>',
			data_id: '<?php echo $data_id; ?>'}, function(result) {
				$('#after-table-elements').html(result.after_table_widgets);
		}, "json" );

		<?php endif; ?>
		
		<?php
			if(isset($datatable_filters)) {
				foreach($datatable_filters as $datatable_filter) {
					echo '$("#datatable-search-col-'.$datatable_filter['column_no'].'").val("'.$datatable_filter['filter_value'].'");';
					echo 'datatableSearch(table.columns('.$datatable_filter['column_no'].'), $("#datatable-search-col-'.$datatable_filter['column_no'].'").val(), null);';
				}
			}
		?>
	});
	
	var rangeFiltering = false;
	var rangeFilterCol = 0;
	var rangeFilterMinValue = null;
	var rangeFilterMaxValue = null;
	var rangeFilterIncludeEqual = true;
	
	function datatableSearch(column, value, format) {
		var that = column;
		
		rangeFiltering = false;

		//if ( that.search() !== value ) {
			if(value === '""') {
				that
					.search( '^$', true, false )
					.draw();
			}
			else if(value === '<>""') {
				that
					.search( '[^\s]', true, false )
					.draw();
			}
			else if(value === '<>0') {
				that
					.search( '[^0]', true, false )
					.draw();
			}
			else if(format == 'number') {
				value = value.replace(/\s/g, '');
				rangeFilterCol = that[0][0];
					
				if(value.match(/(\d+)-(\d+)/)) {	
					var filterValues = value.split('-');
					
					rangeFiltering = true;
					rangeFilterMinValue = parseFloat(filterValues[0]);
					rangeFilterMaxValue = parseFloat(filterValues[1]);
					rangeFilterIncludeEqual = true;
					that.search('').draw();
				}
				else if(value.match(/>(\d+)/)) {
					rangeFiltering = true;
					rangeFilterMinValue = parseFloat(value.replace('>',''));
					rangeFilterMaxValue = null;
					rangeFilterIncludeEqual = false;
					that.search('').draw();
				}
				else if(value.match(/>=(\d+)/)) {
					rangeFiltering = true;
					rangeFilterMinValue = parseFloat(value.replace('>=',''));
					rangeFilterMaxValue = null;
					rangeFilterIncludeEqual = true;
					that.search('').draw();
				}
				else if(value.match(/<(\d+)/)) {
					rangeFiltering = true;
					rangeFilterMinValue = null;
					rangeFilterMaxValue = parseFloat(value.replace('<',''));
					rangeFilterIncludeEqual = false;
					that.search('').draw();
				}
				else if(value.match(/<=(\d+)/)) {
					rangeFiltering = true;
					rangeFilterMinValue = null;
					rangeFilterMaxValue = parseFloat(value.replace('<=',''));
					rangeFilterIncludeEqual = true;
					that.search('').draw();
				}
				else if(value.trim() !== '') {
					that
						.search('^' + value.trim() + '$', true, false, true)
						.draw();
				}
				else {
					that
						.search( value, true, true )
						.draw();
				}
			}
			else if(value.charAt(0) === '[' && value.charAt(value.length - 1) === ']') {
				that
					.search('^' + value.substring(1, value.length-1) + '$', true, false, true)
					.draw();
			}
			else {
				that
					.search( value, true, true )
					.draw();
			}
		//}
	}
	
	jQuery.fn.dataTableExt.afnFiltering.push(
		function(oSettings, aData, iDataIndex) {
			if(rangeFiltering) {
				var thisValue = parseFloat(aData[rangeFilterCol]) || 0;
				if(rangeFilterIncludeEqual) {
					if(
						(rangeFilterMinValue === null && rangeFilterMaxValue === null) ||
						(rangeFilterMinValue === null && rangeFilterMaxValue >= thisValue) ||
						(rangeFilterMinValue <= thisValue && rangeFilterMaxValue === null) ||
						(rangeFilterMinValue <= thisValue && rangeFilterMaxValue >= thisValue)
					) {
						return true;
					}
				}
				else {
					if(
						(rangeFilterMinValue === null && rangeFilterMaxValue === null) ||
						(rangeFilterMinValue === null && rangeFilterMaxValue > thisValue) ||
						(rangeFilterMinValue < thisValue && rangeFilterMaxValue === null) ||
						(rangeFilterMinValue < thisValue && rangeFilterMaxValue > thisValue)
					) {
						return true;
					}
				}
				
				return false;
			}
			else {
				return true;
			}
		}
	);
</script>

<?php endif; ?>