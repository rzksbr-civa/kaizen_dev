<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<script>
$('body').on('click', '#btn-set-hide-break-times', function() {
	var break_times_state = $(this).attr('break_times_state');
	if(break_times_state == 'hidden') {
		$(this).attr('break_times_state','shown');
		$(this).html('Hide Break Times');
		$('#break-times-row').slideDown('fast');
	}
	else {
		$(this).attr('break_times_state','hidden');
		$(this).html('Set Break Times');
		$('#break-times-row').slideUp('fast');
	}
});
</script>

<?php if($generate): ?>
<script>
const FULL_DASH_ARRAY = 283;
const WARNING_THRESHOLD = 300;
const ALERT_THRESHOLD = 60;

const COLOR_CODES = {
	info: {
		color: "green"
	},
	warning: {
		color: "orange",
		threshold: WARNING_THRESHOLD
	},
	alert: {
		color: "red",
		threshold: ALERT_THRESHOLD
	}
};

let startTime = new Date(<?php echo strtotime($start_time) * 1000; ?>);
let endTime = null;

<?php if(isset($estimated_finish_time_js)) : ?>
	endTime = new Date(<?php echo $estimated_finish_time_js; ?>);
<?php endif; ?>

const TIME_LIMIT = Math.round((endTime - startTime) / 1000);
let timePassed = 0;
let timeLeft = TIME_LIMIT;
let timerInterval = null;
let remainingPathColor = COLOR_CODES.info.color;

startTimer();

function onTimesUp() {
	clearInterval(timerInterval);
}

function startTimer() {
	timerInterval = setInterval(() => {
		if(endTime !== null) {
			let now = new Date();
			
			timePassed = Math.round((now - startTime) / 1000);
			timeLeft = Math.round((endTime - now) / 1000);
			//$('#time-countdown-label').html( formatTime(timeLeft) );
			setCircleDasharray();
			setRemainingPathColor(timeLeft);
			
			if (timeLeft === 0) {
				onTimesUp();
			}
		}
	}, 1000);
}

function formatTime(time) {
	let hours = Math.floor(time / 3600);
	let minutes = Math.floor((time % 3600) / 60);
	let seconds = time % 60;

	if (hours < 10) {
		hours = `0${hours}`;
	}
	
	if (minutes < 10) {
		minutes = `0${minutes}`;
	}
	
	if (seconds < 10) {
		seconds = `0${seconds}`;
	}
	
	return `${hours}:${minutes}:${seconds}`;
}

function setRemainingPathColor(timeLeft) {
	/*const { alert, warning, info } = COLOR_CODES;
	if (timeLeft <= alert.threshold) {
		$('.base-timer__path-remaining').removeClass(info.color);
		$('.base-timer__path-remaining').removeClass(warning.color);
		$('.base-timer__path-remaining').addClass(alert.color);
	}
	else if (timeLeft <= warning.threshold) {
		$('.base-timer__path-remaining').removeClass(info.color);
		$('.base-timer__path-remaining').addClass(warning.color);
		$('.base-timer__path-remaining').removeClass(alert.color);
	}
	else {
		$('.base-timer__path-remaining').addClass(info.color);
		$('.base-timer__path-remaining').removeClass(warning.color);
		$('.base-timer__path-remaining').removeClass(alert.color);
	}*/
}

function calculateTimeFraction() {
	const rawTimeFraction = timeLeft / TIME_LIMIT;
	return rawTimeFraction - (1 / TIME_LIMIT) * (1 - rawTimeFraction);
}

function setCircleDasharray() {
	const circleDasharray = `${(
		calculateTimeFraction() * FULL_DASH_ARRAY
	).toFixed(0)} 283`;
	$('#time-countdown-base-timer-path-remaining').attr('stroke-dasharray', circleDasharray);
}

function setRemainingShipmentsCircleDasharray(remainingShipments, completedShipments) {
	const circleDasharray = `${(
		(remainingShipments / (remainingShipments + completedShipments)) * FULL_DASH_ARRAY
	).toFixed(0)} 283`;
	$('#remaining-shipments-base-timer-path-remaining').attr('stroke-dasharray', circleDasharray);
}

setRemainingShipmentsCircleDasharray(<?php echo $total_shipments_count; ?>, <?php echo $completed_shipments_count; ?>);
</script>
<?php endif; ?>

<?php if($generate) : ?>

<script>
	window.setInterval('refresh()', 60000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_countdown_board_data'); ?>', { 
			data: $('#form-countdown-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#countdown-board-shipments-graph').html(result.countdown_board_shipments_graph_html);
				$('#js-countdown-board-shipments-graph').html(result.js_countdown_board_shipments_graph_html);
				
				$('#time-countdown-label').html(result.estimated_remaining_secs_text);
				$('#estimated-finish-time-label').html(result.estimated_finish_time_text);
				if(result.estimated_finish_time_js === null) {
					endTime = null;
				}
				else {
					endTime = new Date(result.estimated_finish_time_js);
				}
				
				$('.base-timer__path-remaining').removeClass('green');
				$('.base-timer__path-remaining').removeClass('orange');
				$('.base-timer__path-remaining').removeClass('red');
				$('.base-timer__path-remaining').addClass(result.color_code);
				
				$('#remaining-shipments-label').html(result.remaining_shipments_count);
				//$('#completed-shipments-label').html(result.completed_shipments_count);
				$('#current-pace-label').html(result.current_num_shipment_per_minute);
				$('#required-pace-label').html(result.required_num_shipment_per_minute_text);
				$('#projected-demand-label').html(result.adjusted_projected_demand);
				setRemainingShipmentsCircleDasharray(result.total_shipments_count,result.completed_shipments_count);
			}
		}, "json" );
    }	
</script>

<div id="js-countdown-board-shipments-graph">
	<?php echo $js_countdown_board_shipments_graph_html; ?>
</div>

<?php endif; ?>