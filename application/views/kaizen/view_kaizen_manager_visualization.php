<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title kaizen-panel-title">Pace</div>
			</div>
			<div class="panel-body">
				<div class="kaizen-manager-chart-wrapper">
					<div class="kaizen-manager-chart" id="kaizen-manager-pace-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title kaizen-panel-title">Packing</div>
			</div>
			<div class="panel-body">
				<div class="kaizen-manager-chart-wrapper">
					<div class="kaizen-manager-chart" id="kaizen-manager-packing-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title kaizen-panel-title">Picking</div>
			</div>
			<div class="panel-body">
				<div class="kaizen-manager-chart-wrapper">
					<div class="kaizen-manager-chart" id="kaizen-manager-picking-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title kaizen-panel-title">Loading</div>
			</div>
			<div class="panel-body">
				<div class="kaizen-manager-chart-wrapper">
					<div class="kaizen-manager-chart" id="kaizen-manager-loading-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>